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
use Illuminate\Support\Facades\Storage;
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
                'coursework_weight_percent' => 20,
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
            'coursework_weight_percent' => 40,
            'attachment_file_path' => 'lms/'.$offering->id.'/assignments/sample.pdf',
            'is_published' => true,
            'due_at' => now()->addWeek(),
        ]);

        Storage::fake('local');
        Storage::disk('local')->put($assignment->attachment_file_path, 'sample pdf content');

        LmsNotification::create([
            'user_id' => $studentUser->id,
            'title' => 'Welcome to LMS',
            'body' => 'Course workspace is ready.',
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.lms.show', $offering))
            ->assertOk()
            ->assertSee('Overview');

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

        $this->assertDatabaseHas('course_results', [
            'student_course_enrolment_id' => $offering->studentEnrolments()->where('student_id', $student->id)->value('id'),
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.lms.assignments.show', [$offering, $assignment]))
            ->assertOk()
            ->assertSee('Download assignment');

        $this->actingAs($studentUser)
            ->get(route('student.lms.assignments.attachment', [$offering, $assignment]))
            ->assertOk();
    }

    public function test_published_assignment_notifies_enrolled_students(): void
    {
        ['lecturerUser' => $lecturer, 'studentUser' => $studentUser, 'offering' => $offering] = $this->seedOffering();

        $this->actingAs($lecturer)
            ->post(route('lecturer.lms.assignments.store', $offering), [
                'title' => 'Essay 1',
                'instructions' => 'Write 1000 words.',
                'max_score' => 100,
                'coursework_weight_percent' => 20,
                'is_published' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lms_notifications', [
            'user_id' => $studentUser->id,
            'title' => 'New assignment: Essay 1',
        ]);
    }

    public function test_assignment_submission_notifies_lecturer(): void
    {
        ['lecturerUser' => $lecturer, 'studentUser' => $studentUser, 'student' => $student, 'offering' => $offering] = $this->seedOffering();

        $assignment = LmsAssignment::create([
            'course_offering_id' => $offering->id,
            'title' => 'Essay 1',
            'instructions' => 'Submit your essay.',
            'max_score' => 100,
            'coursework_weight_percent' => 40,
            'is_published' => true,
            'due_at' => now()->addWeek(),
        ]);

        $this->actingAs($studentUser)
            ->post(route('student.lms.assignments.submit', [$offering, $assignment]), [
                'body' => 'My essay response.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lms_notifications', [
            'user_id' => $lecturer->id,
            'title' => 'Assignment submitted: Essay 1',
        ]);
    }

    public function test_published_module_material_notifies_students(): void
    {
        ['lecturerUser' => $lecturer, 'studentUser' => $studentUser, 'offering' => $offering] = $this->seedOffering();

        $this->actingAs($lecturer)
            ->post(route('lecturer.lms.modules.store', $offering), [
                'title' => 'Week 1',
                'description' => 'Introduction',
                'is_published' => 1,
                'material_title' => 'Lecture slides',
                'material_type' => 'document',
                'allow_download' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lms_notifications', [
            'user_id' => $studentUser->id,
            'title' => 'New learning material: Lecture slides',
        ]);
    }

    public function test_lecturer_and_student_can_view_notification_inboxes(): void
    {
        ['lecturerUser' => $lecturer, 'studentUser' => $studentUser] = $this->seedOffering();

        LmsNotification::create([
            'user_id' => $lecturer->id,
            'title' => 'Submission received',
            'body' => 'A student submitted work.',
        ]);

        LmsNotification::create([
            'user_id' => $studentUser->id,
            'title' => 'New assignment',
            'body' => 'Check your course workspace.',
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.notifications'))
            ->assertOk()
            ->assertSee('Submission received');

        $this->actingAs($studentUser)
            ->get(route('student.notifications'))
            ->assertOk()
            ->assertSee('New assignment');
    }

    public function test_unread_notification_count_shows_on_student_bell(): void
    {
        ['studentUser' => $studentUser] = $this->seedOffering();

        LmsNotification::create([
            'user_id' => $studentUser->id,
            'title' => 'New assignment: Essay 1',
            'body' => 'Due tomorrow.',
        ]);

        LmsNotification::create([
            'user_id' => $studentUser->id,
            'title' => 'New learning material: Week 1 slides',
            'body' => 'Added to module: Week 1',
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Notifications (2 unread)', false);
    }

    public function test_discussion_chat_supports_messages_files_and_close(): void
    {
        Storage::fake('local');
        ['lecturerUser' => $lecturer, 'studentUser' => $studentUser, 'offering' => $offering] = $this->seedOffering();

        $this->actingAs($lecturer)
            ->post(route('lecturer.lms.discussions.store', $offering), [
                'title' => 'Week 1 Q&A',
                'body' => 'Ask questions about the first lecture.',
            ])
            ->assertRedirect();

        $discussion = \App\Models\LmsDiscussion::first();

        $this->actingAs($studentUser)
            ->post(route('student.lms.discussions.posts.store', [$offering, $discussion]), [
                'body' => 'Thanks, I have a question about the assignment.',
            ])
            ->assertRedirect();

        $this->actingAs($lecturer)
            ->get(route('lecturer.lms.discussions.show', [$offering, $discussion]))
            ->assertOk()
            ->assertSee('Week 1 Q&A')
            ->assertSee('Ask questions about the first lecture.')
            ->assertSee('Thanks, I have a question');

        $this->actingAs($lecturer)
            ->post(route('lecturer.lms.discussions.close', [$offering, $discussion]))
            ->assertRedirect();

        $this->assertTrue($discussion->fresh()->is_closed);

        $this->actingAs($studentUser)
            ->post(route('student.lms.discussions.posts.store', [$offering, $discussion]), [
                'body' => 'Another message',
            ])
            ->assertForbidden();
    }
}
