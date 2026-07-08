<?php

namespace Tests\Feature;

use App\Enums\ProgrammeApplicationStatus;
use App\Models\Institution;
use App\Models\InstitutionWebsiteSetting;
use App\Models\Programme;
use App\Models\ProgrammeApplication;
use App\Models\Student;
use App\Models\User;
use App\Services\ApplicationEnrollmentService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProgrammeApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function publishedWebsite(): array
    {
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create([
            'name' => 'Test University',
            'acronym' => 'TU',
            'status' => 'active',
        ]);

        $website = InstitutionWebsiteSetting::forInstitution($institution);
        $website->update([
            'slug' => 'test-university',
            'school_name' => 'Test University',
            'is_published' => true,
        ]);

        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Computer Science',
            'level' => 'undergraduate',
            'total_credit_hours' => 480,
            'tuition_fee' => 450000,
            'application_fee' => 25000,
            'applications_open' => true,
        ]);

        return [$institution, $website, $programme];
    }

    protected function createApplicant(Institution $institution): User
    {
        return app(ApplicationEnrollmentService::class)->createApplicantUser([
            'institution_id' => $institution->id,
            'first_name' => 'Jane',
            'last_name' => 'Phiri',
            'email' => 'jane.phiri@example.com',
            'password' => 'password',
        ]);
    }

    public function test_applicant_can_register_via_school_portal(): void
    {
        [, $website] = $this->publishedWebsite();

        $this->post(route('school.apply.register', $website->slug), [
            'first_name' => 'John',
            'last_name' => 'Banda',
            'email' => 'john.banda@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('applicant.apply.create', $website->slug));

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isApplicant());
    }

    protected function sampleGrades(): array
    {
        return [
            'English' => 6,
            'Mathematics' => 5,
            'Chichewa' => 7,
            'Biology' => 6,
            'Agriculture' => 5,
            'Chemistry' => 4,
        ];
    }

    public function test_applicant_can_submit_application(): void
    {
        Storage::fake('local');

        [, $website, $programme] = $this->publishedWebsite();
        $applicant = $this->createApplicant($programme->institution);
        $this->actingAs($applicant);

        $this->post(route('applicant.apply.store', $website->slug), array_merge([
            'programme_id' => $programme->id,
            'first_name' => 'Jane',
            'last_name' => 'Phiri',
            'email' => 'jane.phiri@example.com',
            'phone' => '0999123456',
            'certificates' => UploadedFile::fake()->create('cert.pdf', 100, 'application/pdf'),
            'results' => UploadedFile::fake()->create('results.pdf', 100, 'application/pdf'),
            'payment_proof' => UploadedFile::fake()->create('payment.pdf', 100, 'application/pdf'),
        ], ['grades' => $this->sampleGrades()]))->assertRedirect();

        $application = ProgrammeApplication::query()->first();
        $this->assertNotNull($application);
        $this->assertSame(ProgrammeApplicationStatus::Submitted, $application->status);
        $this->assertSame('Jane Phiri', $application->fullName());
        $this->assertNotNull($application->certificates_path);
        $this->assertCount(6, $application->certificate_grades);
    }

    public function test_applicant_can_edit_application_while_window_open(): void
    {
        Storage::fake('local');

        [, $website, $programme] = $this->publishedWebsite();
        $applicant = $this->createApplicant($programme->institution);

        $application = ProgrammeApplication::create([
            'institution_id' => $programme->institution_id,
            'programme_id' => $programme->id,
            'user_id' => $applicant->id,
            'application_number' => 'TU-2026-0002',
            'first_name' => 'Jane',
            'last_name' => 'Phiri',
            'email' => 'jane.phiri@example.com',
            'certificate_grades' => [['subject' => 'English', 'points' => 5]],
            'status' => ProgrammeApplicationStatus::Submitted,
            'submitted_at' => now(),
            'certificates_path' => 'test/cert.pdf',
            'results_path' => 'test/results.pdf',
            'payment_proof_path' => 'test/payment.pdf',
        ]);

        Storage::disk('local')->put('test/cert.pdf', 'x');
        Storage::disk('local')->put('test/results.pdf', 'x');
        Storage::disk('local')->put('test/payment.pdf', 'x');

        $this->actingAs($applicant)
            ->put(route('applicant.applications.update', $application), array_merge([
                'programme_id' => $programme->id,
                'first_name' => 'Jane',
                'last_name' => 'Mwale',
                'email' => 'jane.phiri@example.com',
            ], ['grades' => $this->sampleGrades()]))
            ->assertRedirect(route('applicant.applications.show', $application));

        $this->assertSame('Jane Mwale', $application->fresh()->fullName());
    }

    public function test_applicant_cannot_start_second_application(): void
    {
        [, $website, $programme] = $this->publishedWebsite();
        $applicant = $this->createApplicant($programme->institution);

        ProgrammeApplication::create([
            'institution_id' => $programme->institution_id,
            'programme_id' => $programme->id,
            'user_id' => $applicant->id,
            'application_number' => 'TU-2026-0003',
            'first_name' => 'Jane',
            'last_name' => 'Phiri',
            'email' => 'jane.phiri@example.com',
            'status' => ProgrammeApplicationStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $this->actingAs($applicant)
            ->get(route('applicant.apply.create', $website->slug))
            ->assertRedirect();
    }

    public function test_admin_enrolling_via_status_creates_student(): void
    {
        [$institution, , $programme] = $this->publishedWebsite();
        $applicant = $this->createApplicant($institution);

        $application = ProgrammeApplication::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'user_id' => $applicant->id,
            'application_number' => 'TU-2026-0099',
            'first_name' => 'Jane',
            'last_name' => 'Phiri',
            'email' => 'jane.phiri@example.com',
            'status' => ProgrammeApplicationStatus::Approved,
            'submitted_at' => now(),
        ]);

        $admin = User::create([
            'institution_id' => $institution->id,
            'name' => 'Institution Admin',
            'email' => 'admin2@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('institution_admin');

        $this->actingAs($admin)
            ->put(route('applications.status', $application), [
                'status' => ProgrammeApplicationStatus::Enrolled->value,
                'year_of_study' => 1,
            ])
            ->assertRedirect();

        $application->refresh();
        $this->assertSame(ProgrammeApplicationStatus::Enrolled, $application->status);
        $this->assertNotNull($application->enrolled_student_id);

        $this->assertDatabaseHas('students', [
            'id' => $application->enrolled_student_id,
            'email' => 'jane.phiri@example.com',
            'programme_id' => $programme->id,
        ]);

        $this->assertTrue($applicant->fresh()->hasRole('student'));
        $this->assertFalse($applicant->fresh()->hasRole('applicant'));
    }

    public function test_admin_can_repair_enrolled_status_without_student_record(): void
    {
        [$institution, , $programme] = $this->publishedWebsite();
        $applicant = $this->createApplicant($institution);

        $application = ProgrammeApplication::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'user_id' => $applicant->id,
            'application_number' => 'TU-2026-0100',
            'first_name' => 'Jane',
            'last_name' => 'Phiri',
            'email' => 'jane.phiri@example.com',
            'status' => ProgrammeApplicationStatus::Enrolled,
            'submitted_at' => now(),
            'enrolled_at' => now(),
        ]);

        $admin = User::create([
            'institution_id' => $institution->id,
            'name' => 'Institution Admin',
            'email' => 'admin3@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('institution_admin');

        $this->actingAs($admin)
            ->put(route('applications.status', $application), [
                'status' => ProgrammeApplicationStatus::Enrolled->value,
            ])
            ->assertRedirect();

        $application->refresh();
        $this->assertNotNull($application->enrolled_student_id);
        $this->assertTrue($applicant->fresh()->hasRole('student'));
    }

    public function test_admin_can_review_and_enroll_applicant(): void
    {
        Storage::fake('local');

        [$institution, $website, $programme] = $this->publishedWebsite();
        $applicant = $this->createApplicant($institution);

        $application = ProgrammeApplication::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'user_id' => $applicant->id,
            'application_number' => 'TU-2026-0001',
            'first_name' => 'Jane',
            'last_name' => 'Phiri',
            'email' => 'jane.phiri@example.com',
            'status' => ProgrammeApplicationStatus::Submitted,
            'submitted_at' => now(),
            'payment_proof_path' => 'institutions/'.$institution->id.'/applications/1/payment_proof/test.pdf',
        ]);

        Storage::disk('local')->put($application->payment_proof_path, 'proof');

        $admin = User::create([
            'institution_id' => $institution->id,
            'name' => 'Institution Admin',
            'email' => 'admin@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('institution_admin');

        $this->actingAs($admin)
            ->get(route('applications.index'))
            ->assertOk()
            ->assertSee('TU-2026-0001');

        $this->actingAs($admin)
            ->post(route('applications.verify-payment', $application))
            ->assertRedirect();

        $application->refresh();
        $this->assertNotNull($application->payment_verified_at);

        $this->actingAs($admin)
            ->put(route('applications.status', $application), [
                'status' => ProgrammeApplicationStatus::Approved->value,
                'admin_notes' => 'Meets entry requirements.',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('applications.enroll', $application), ['year_of_study' => 1])
            ->assertRedirect();

        $application->refresh();
        $this->assertSame(ProgrammeApplicationStatus::Enrolled, $application->status);
        $this->assertNotNull($application->enrolled_student_id);

        $student = Student::find($application->enrolled_student_id);
        $this->assertSame('Jane', $student->first_name);
        $this->assertTrue($applicant->fresh()->hasRole('student'));
        $this->assertFalse($applicant->fresh()->hasRole('applicant'));
    }

    public function test_school_apply_page_links_to_applicant_auth(): void
    {
        [, $website, $programme] = $this->publishedWebsite();

        $this->get(route('school.applications', $website->slug))
            ->assertOk()
            ->assertSee(route('school.apply.register', $website->slug, false))
            ->assertSee('MK 25,000')
            ->assertSee($programme->name);
    }

    public function test_logged_in_admin_can_view_applicant_register_page(): void
    {
        [$institution, $website] = array_slice($this->publishedWebsite(), 0, 2);

        $admin = User::create([
            'institution_id' => $institution->id,
            'name' => 'Institution Admin',
            'email' => 'admin@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('institution_admin');

        $this->actingAs($admin)
            ->get(route('school.apply.register', $website->slug))
            ->assertOk()
            ->assertSee('Create applicant account');
    }
}
