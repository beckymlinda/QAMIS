<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Institution;
use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsProgrammeManager(Institution $institution): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::create([
            'institution_id' => $institution->id,
            'name' => 'Programme Manager',
            'email' => 'manager@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $user->givePermissionTo(Permission::findByName('programme.manage'));
        $this->actingAs($user);

        return $user;
    }

    public function test_admin_can_create_student_with_auto_generated_fields(): void
    {
        $institution = Institution::create([
            'name' => 'Mzuzu University',
            'acronym' => 'MZUNI',
            'status' => 'active',
        ]);

        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Business Administration',
            'level' => 'undergraduate',
        ]);

        $this->actingAsProgrammeManager($institution);

        $this->post(route('students.store'), [
            'programme_id' => $programme->id,
            'first_name' => 'John',
            'last_name' => 'Banda',
            'year_of_study' => 1,
        ])->assertRedirect(route('students.index', ['programme_id' => $programme->id]));

        $student = Student::query()->where('institution_id', $institution->id)->first();

        $this->assertNotNull($student);
        $this->assertSame('john@mzuni.com', $student->email);
        $this->assertStringStartsWith('MZUNI-', $student->student_number);
        $this->assertTrue($student->user->hasRole('student'));
    }

    public function test_student_can_register_for_prescribed_programme_course(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Test HEI', 'acronym' => 'TEST', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Test Programme',
            'level' => 'undergraduate',
        ]);

        $user = User::create([
            'institution_id' => $institution->id,
            'name' => 'Test Student',
            'email' => 'student@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole('student');

        $student = Student::create([
            'institution_id' => $institution->id,
            'user_id' => $user->id,
            'programme_id' => $programme->id,
            'student_number' => 'STU001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@test.mw',
            'year_of_study' => 1,
        ]);

        $prescribedCourse = Course::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'code' => 'BUSI-1101',
            'title' => 'Intro to Business',
            'credit_hours' => 3,
            'year_level' => 1,
            'semester_number' => 1,
        ]);

        $otherProgramme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Other Programme',
            'level' => 'undergraduate',
        ]);

        $otherCourse = Course::create([
            'institution_id' => $institution->id,
            'programme_id' => $otherProgramme->id,
            'code' => 'LAWS-1101',
            'title' => 'Intro to Law',
            'credit_hours' => 3,
            'year_level' => 1,
            'semester_number' => 1,
        ]);

        $prescribedOffering = CourseOffering::create([
            'institution_id' => $institution->id,
            'course_id' => $prescribedCourse->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'delivery_mode' => 'face_to_face',
        ]);

        $otherOffering = CourseOffering::create([
            'institution_id' => $institution->id,
            'course_id' => $otherCourse->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'delivery_mode' => 'face_to_face',
        ]);

        $this->actingAs($user)
            ->post(route('student.courses.register'), [
                'course_offering_id' => $prescribedOffering->id,
                'academic_year' => '2026/2027',
                'semester' => 1,
            ])
            ->assertRedirect(route('student.courses', ['academic_year' => '2026/2027', 'semester' => 1]));

        $this->assertTrue(
            $student->courseEnrolments()->where('course_offering_id', $prescribedOffering->id)->exists()
        );

        $this->actingAs($user)
            ->post(route('student.courses.register'), [
                'course_offering_id' => $otherOffering->id,
                'academic_year' => '2026/2027',
                'semester' => 1,
            ])
            ->assertForbidden();
    }
}
