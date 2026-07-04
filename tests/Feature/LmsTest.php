<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Institution;
use App\Models\LmsAssignment;
use App\Models\LmsNotification;
use App\Models\Programme;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LmsTest extends TestCase
{
    use RefreshDatabase;

    protected function seedOffering(): array
    {
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Test Programme',
            'level' => 'undergraduate',
        ]);

        $lecturerUser = User::create([
            'institution_id' => $institution->id,
            'name' => 'Dr Lecturer',
            'email' => 'lecturer@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $lecturerUser->assignRole('lecturer');

        $staff = StaffMember::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'user_id' => $lecturerUser->id,
            'type' => 'academic',
            'name' => 'Dr Lecturer',
        ]);

        $studentUser = User::create([
            'institution_id' => $institution->id,
            'name' => 'Test Student',
            'email' => 'student@test.mw',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('student');

        $student = Student::create([
            'institution_id' => $institution->id,
            'user_id' => $studentUser->id,
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
            'code' => 'LMS-101',
            'title' => 'Learning Systems',
            'credit_hours' => 3,
        ]);

        $offering = CourseOffering::create([
            'institution_id' => $institution->id,
            'course_id' => $course->id,
            'staff_member_id' => $staff->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'delivery_mode' => 'blended',
        ]);

        StudentCourseEnrolment::create([
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
        ]);

        return compact('lecturerUser', 'studentUser', 'student', 'offering');
    }

    public function test_lecturer_can_manage_lms_outline_and_assignment(): void
    {
        ['lecturerUser' => $lecturer, 'offering' => $offering] = $this->seedOffering();

        $this->actingAs($lecturer)
            ->get(route('lecturer.lms.show', $offering))
            ->assertOk()
            ->assertSee('Learning Management System');

        $this->actingAs($lecturer)
            ->post(route('lecturer.lms.outline.items.store', $offering), [
                'type' => 'learning_outcome',
                'title' => 'Core outcomes',
                'body' => 'Understand LMS fundamentals.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lms_outline_items', [
            'course_offering_id' => $offering->id,
            'type' => 'learning_outcome',
            'title' => 'Core outcomes',
        ]);

        $this->actingAs($lecturer)
            ->post(route('lecturer.lms.assignments.store', $offering), [
                'title' => 'Essay 1',
                'instructions' => 'Write 1000 words.',
                'max_score' => 100,
                'is_published' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lms_assignments', [
            'course_offering_id' => $offering->id,
            'title' => 'Essay 1',
            'is_published' => true,
        ]);

        $assignment = LmsAssignment::where('course_offering_id', $offering->id)->first();

        $this->actingAs($lecturer)
            ->get(route('lecturer.lms.assignments.submissions', [$offering, $assignment]))
            ->assertOk()
            ->assertSee('No submissions yet');
    }

    public function test_student_can_view_lms_and_submit_assignment(): void
    {
        ['lecturerUser' => $lecturer, 'studentUser' => $studentUser, 'student' => $student, 'offering' => $offering] = $this->seedOffering();

        $assignment = LmsAssignment::create([
            'course_offering_id' => $offering->id,
            'title' => 'Essay 1',
            'instructions' => 'Submit your essay.',
            'max_score' => 100,
            'is_published' => true,
            'due_at' => now()->addWeek(),
        ]);

        LmsNotification::create([
            'user_id' => $studentUser->id,
            'title' => 'Welcome to LMS',
            'body' => 'Course workspace is ready.',
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.lms.show', $offering))
            ->assertOk()
            ->assertSee('Course workspace');

        $this->actingAs($studentUser)
            ->post(route('student.lms.assignments.submit', [$offering, $assignment]), [
                'body' => 'My essay response.',
            ])
            ->assertRedirect(route('student.lms.assignments.show', [$offering, $assignment]));

        $this->assertDatabaseHas('lms_assignment_submissions', [
            'lms_assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);

        $submission = $assignment->submissions()->first();

        $this->actingAs($lecturer)
            ->post(route('lecturer.lms.submissions.grade', [
                $offering,
                $submission,
            ]), [
                'score' => 85,
                'feedback' => 'Well done.',
            ])
            ->assertRedirect(route('lecturer.lms.submissions.show', [$offering, $submission]));

        $this->assertDatabaseHas('lms_assignment_submissions', [
            'lms_assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'score' => 85,
        ]);
    }
}
