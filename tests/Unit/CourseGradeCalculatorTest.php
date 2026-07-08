<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Institution;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\Programme;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use App\Services\CourseGradeCalculator;
use App\Support\GpaGrading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseGradeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_accumulates_coursework_from_weighted_assignments(): void
    {
        [$offering, $enrolment, $assignment] = $this->seedScenario();

        $assignment->update(['coursework_weight_percent' => 20]);

        LmsAssignmentSubmission::create([
            'lms_assignment_id' => $assignment->id,
            'student_id' => $enrolment->student_id,
            'score' => 80,
            'submitted_at' => now(),
            'graded_at' => now(),
        ]);

        $calculator = app(CourseGradeCalculator::class);
        $breakdown = $calculator->breakdown($enrolment, $offering);

        $this->assertSame(16.0, $breakdown['earned_coursework_points']);
        $this->assertSame(80.0, $breakdown['coursework_percentage']);
    }

    public function test_it_computes_final_grade_with_exam(): void
    {
        [$offering, $enrolment, $assignment] = $this->seedScenario();

        $assignment->update(['coursework_weight_percent' => 40]);

        LmsAssignmentSubmission::create([
            'lms_assignment_id' => $assignment->id,
            'student_id' => $enrolment->student_id,
            'score' => 100,
            'submitted_at' => now(),
            'graded_at' => now(),
        ]);

        $calculator = app(CourseGradeCalculator::class);
        $result = $calculator->syncResult($enrolment, $offering);
        $result->exam_percentage = 70;
        $result->save();

        $result = $calculator->syncResult($enrolment->fresh(), $offering);

        $this->assertSame(40.0, (float) $result->coursework_percentage);
        $this->assertSame(82.0, (float) $result->final_percentage);
        $this->assertSame('A', $result->letter_grade);
    }

    public function test_it_computes_final_from_weighted_contributions(): void
    {
        $this->assertSame(54.0, GpaGrading::computeFinalFromContributions(12.0, 70.0));
    }

    /**
     * @return array{0: CourseOffering, 1: StudentCourseEnrolment, 2: LmsAssignment}
     */
    private function seedScenario(): array
    {
        $institution = Institution::create(['name' => 'Test HEI', 'status' => 'active']);
        $programme = Programme::create([
            'institution_id' => $institution->id,
            'name' => 'Test Programme',
            'level' => 'undergraduate',
        ]);

        $course = Course::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'code' => 'GPA-101',
            'title' => 'GPA Course',
            'credit_hours' => 3,
        ]);

        $staff = StaffMember::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'type' => 'academic',
            'name' => 'Dr Test',
        ]);

        $offering = CourseOffering::create([
            'institution_id' => $institution->id,
            'course_id' => $course->id,
            'staff_member_id' => $staff->id,
            'academic_year' => '2026/2027',
            'semester' => 1,
            'delivery_mode' => 'blended',
        ]);

        $student = Student::create([
            'institution_id' => $institution->id,
            'programme_id' => $programme->id,
            'student_number' => 'GPA001',
            'first_name' => 'Grade',
            'last_name' => 'Student',
            'email' => 'gpa@test.mw',
            'year_of_study' => 1,
        ]);

        $enrolment = StudentCourseEnrolment::create([
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
        ]);

        $assignment = LmsAssignment::create([
            'course_offering_id' => $offering->id,
            'title' => 'Essay',
            'max_score' => 100,
            'coursework_weight_percent' => 0,
            'is_published' => true,
        ]);

        return [$offering, $enrolment, $assignment];
    }
}
