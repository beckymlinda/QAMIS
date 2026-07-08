<?php

namespace Tests\Feature;

use App\Enums\FeePaymentStatus;
use App\Enums\ProgrammeApplicationStatus;
use App\Models\Institution;
use App\Models\Programme;
use App\Models\ProgrammeApplication;
use App\Models\Student;
use App\Models\StudentFeePayment;
use App\Models\User;
use App\Services\StudentFeesService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentFeesTest extends TestCase
{
    use RefreshDatabase;

    protected function studentWithProgramme(): array
    {
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Fee Uni', 'acronym' => 'FU', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Business',
            'level' => 'undergraduate',
            'tuition_fee' => 500000,
            'registration_fee' => 50000,
            'application_fee' => 25000,
        ]);

        $user = User::create([
            'institution_id' => $institution->id,
            'name' => 'Test Student',
            'email' => 'student@fee.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole('student');

        $student = Student::create([
            'institution_id' => $institution->id,
            'user_id' => $user->id,
            'programme_id' => $programme->id,
            'student_number' => 'FU-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@fee.mw',
            'year_of_study' => 1,
            'status' => 'active',
        ]);

        return [$institution, $programme, $student, $user];
    }

    public function test_verified_application_fee_reduces_outstanding_balance(): void
    {
        [$institution, $programme, $student, $user] = $this->studentWithProgramme();

        ProgrammeApplication::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'user_id' => $user->id,
            'application_number' => 'APP-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@fee.mw',
            'status' => ProgrammeApplicationStatus::Enrolled,
            'payment_verified_at' => now(),
            'enrolled_student_id' => $student->id,
        ]);

        $summary = app(StudentFeesService::class)->summary($student);

        $this->assertSame(575000.0, $summary['total_due']);
        $this->assertSame(25000.0, $summary['application_fee_credit']);
        $this->assertSame(25000.0, $summary['total_paid']);
        $this->assertSame(550000.0, $summary['balance']);
        $this->assertSame('partial', $summary['payment_status']);
    }

    public function test_student_can_submit_fee_receipt(): void
    {
        Storage::fake('local');

        [, , $student, $user] = $this->studentWithProgramme();

        $this->actingAs($user)
            ->post(route('student.fees.store'), [
                'amount' => 100000,
                'payment_reference' => 'BANK123',
                'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('student.fees'));

        $payment = StudentFeePayment::first();
        $this->assertSame(FeePaymentStatus::Pending, $payment->status);
        $this->assertSame('100000.00', $payment->amount);
        $this->assertNotNull($payment->balance_after);
    }

    public function test_admin_can_approve_student_payment(): void
    {
        Storage::fake('local');

        [$institution, , $student] = $this->studentWithProgramme();

        $payment = StudentFeePayment::create([
            'institution_id' => $institution->id,
            'student_id' => $student->id,
            'amount' => 100000,
            'balance_after' => 475000,
            'status' => FeePaymentStatus::Pending,
            'submitted_at' => now(),
            'receipt_path' => 'test/receipt.pdf',
        ]);

        $admin = User::create([
            'institution_id' => $institution->id,
            'name' => 'Admin',
            'email' => 'admin@fee.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('institution_admin');

        $this->actingAs($admin)
            ->post(route('students.fee-payments.approve', [$student, $payment]))
            ->assertRedirect();

        $this->assertSame(FeePaymentStatus::Approved, $payment->fresh()->status);
    }

    public function test_student_show_has_fees_tab(): void
    {
        [$institution, , $student] = $this->studentWithProgramme();

        $admin = User::create([
            'institution_id' => $institution->id,
            'name' => 'Admin',
            'email' => 'admin@fee.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('institution_admin');

        $this->actingAs($admin)
            ->get(route('students.show', [$student, 'tab' => 'fees']))
            ->assertOk()
            ->assertSee('Fees & payments')
            ->assertSee('MK 500,000');
    }

    public function test_admin_fee_payments_dashboard(): void
    {
        [$institution, $programme, $student, $user] = $this->studentWithProgramme();

        ProgrammeApplication::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'user_id' => $user->id,
            'application_number' => 'APP-002',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@fee.mw',
            'status' => ProgrammeApplicationStatus::Enrolled,
            'payment_verified_at' => now(),
            'enrolled_student_id' => $student->id,
        ]);

        $admin = User::create([
            'institution_id' => $institution->id,
            'name' => 'Admin',
            'email' => 'admin@fee.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('institution_admin');

        $this->actingAs($admin)
            ->get(route('fee-payments.index'))
            ->assertOk()
            ->assertSee('Fee payments')
            ->assertSee('Total expected fees')
            ->assertSee('MK 575,000')
            ->assertSee('MK 550,000')
            ->assertSee('Test Student');
    }
}
