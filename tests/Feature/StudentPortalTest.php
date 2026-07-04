<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\EvaluationPeriod;
use App\Models\Institution;
use App\Models\Programme;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use App\Models\User;
use Database\Seeders\EvaluationQuestionnaireSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function createStudentUser(): array
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(EvaluationQuestionnaireSeeder::class);

        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
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

        $course = Course::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'code' => 'BUSI-1101',
            'title' => 'Intro to Business',
            'credit_hours' => 3,
        ]);

        $offering = CourseOffering::create([
            'institution_id' => $institution->id,
            'course_id' => $course->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'delivery_mode' => 'face_to_face',
        ]);

        StudentCourseEnrolment::create([
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
        ]);

        EvaluationPeriod::create([
            'institution_id' => $institution->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'title' => 'Test Evaluation',
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        return compact('user', 'student', 'offering');
    }

    public function test_student_can_view_portal_dashboard_and_timetable(): void
    {
        ['user' => $user] = $this->createStudentUser();

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Student Portal');

        $this->actingAs($user)
            ->get(route('student.courses'))
            ->assertOk()
            ->assertSee('BUSI-1101');
    }

    public function test_student_login_redirects_to_portal(): void
    {
        ['user' => $user] = $this->createStudentUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('student.dashboard'));
    }
}
