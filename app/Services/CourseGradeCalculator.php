<?php

namespace App\Services;

use App\Models\CourseOffering;
use App\Models\CourseResult;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\StaffMember;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use App\Support\GpaGrading;
use Illuminate\Support\Collection;

class CourseGradeCalculator
{
    public const COURSEWORK_PORTION_PERCENT = 40.0;

    public const EXAM_PORTION_PERCENT = 60.0;

    public function publishedAssignments(CourseOffering $offering): Collection
    {
        return $offering->lmsAssignments()
            ->where('is_published', true)
            ->orderBy('due_at')
            ->orderBy('id')
            ->get();
    }

    public function usedCourseworkWeight(CourseOffering $offering, ?int $excludeAssignmentId = null): float
    {
        $query = $offering->lmsAssignments();

        if ($excludeAssignmentId) {
            $query->where('id', '!=', $excludeAssignmentId);
        }

        return (float) $query->sum('coursework_weight_percent');
    }

    public function remainingCourseworkWeight(CourseOffering $offering, ?int $excludeAssignmentId = null): float
    {
        return max(0, self::COURSEWORK_PORTION_PERCENT - $this->usedCourseworkWeight($offering, $excludeAssignmentId));
    }

    /**
     * @return array{
     *     lines: list<array<string, mixed>>,
     *     earned_coursework_points: float,
     *     total_assignment_weight: float,
     *     coursework_percentage: ?float,
     *     coursework_portion_filled: float
     * }
     */
    public function breakdown(StudentCourseEnrolment $enrolment, CourseOffering $offering): array
    {
        $assignments = $this->publishedAssignments($offering);

        $submissions = LmsAssignmentSubmission::query()
            ->where('student_id', $enrolment->student_id)
            ->whereIn('lms_assignment_id', $assignments->pluck('id'))
            ->whereNotNull('score')
            ->get()
            ->keyBy('lms_assignment_id');

        $lines = [];
        $earned = 0.0;
        $totalWeight = 0.0;

        foreach ($assignments as $assignment) {
            $weight = (float) $assignment->coursework_weight_percent;
            $totalWeight += $weight;

            $submission = $submissions->get($assignment->id);
            $assignmentPercent = null;
            $contribution = 0.0;

            if ($submission && $assignment->max_score > 0) {
                $assignmentPercent = round(((float) $submission->score / (float) $assignment->max_score) * 100, 2);
                $contribution = round(($assignmentPercent / 100) * $weight, 2);
                $earned += $contribution;
            }

            $lines[] = [
                'assignment' => $assignment,
                'submission' => $submission,
                'score' => $submission?->score,
                'assignment_percentage' => $assignmentPercent,
                'weight_percent' => $weight,
                'contribution_to_course' => $contribution,
                'contribution_to_gpa' => $assignmentPercent !== null
                    ? GpaGrading::fromPercentage($assignmentPercent)
                    : null,
            ];
        }

        $courseworkPercentage = $totalWeight > 0
            ? round(($earned / $totalWeight) * 100, 2)
            : null;

        return [
            'lines' => $lines,
            'earned_coursework_points' => round($earned, 2),
            'total_assignment_weight' => round($totalWeight, 2),
            'coursework_percentage' => $courseworkPercentage,
            'coursework_portion_filled' => round(min($earned, self::COURSEWORK_PORTION_PERCENT), 2),
        ];
    }

    public function syncResult(
        StudentCourseEnrolment $enrolment,
        CourseOffering $offering,
        ?StaffMember $staff = null,
    ): ?CourseResult {
        $breakdown = $this->breakdown($enrolment, $offering);
        $result = $enrolment->result ?? new CourseResult([
            'student_course_enrolment_id' => $enrolment->id,
        ]);

        $courseworkContribution = $breakdown['earned_coursework_points'];
        $exam = $result->exam_percentage !== null ? (float) $result->exam_percentage : null;

        $result->coursework_percentage = $courseworkContribution;

        $final = $this->resolveFinalPercentage($result, $breakdown, $exam);

        if ($final === null && $courseworkContribution <= 0 && $exam === null) {
            if ($result->exists) {
                $result->delete();
            }

            return null;
        }

        if ($final !== null) {
            $band = GpaGrading::fromPercentage($final);
            $result->final_percentage = $final;
            $result->letter_grade = $band['letter'];
            $result->grade_points = $band['points'];
            $result->quality_label = $band['quality'];
            $result->academic_decision = $band['decision'];
        } else {
            $result->final_percentage = null;
            $result->letter_grade = null;
            $result->grade_points = null;
            $result->quality_label = null;
            $result->academic_decision = null;
        }

        if ($staff) {
            $result->graded_by_staff_member_id = $staff->id;
            $result->graded_at = now();
        }

        $result->save();

        return $result->fresh();
    }

    public function resolveFinalPercentage(CourseResult $result, array $breakdown, ?float $exam): ?float
    {
        if ($result->use_final_override && $result->final_percentage_override !== null) {
            return round((float) $result->final_percentage_override, 2);
        }

        return GpaGrading::computeFinalFromContributions(
            (float) $breakdown['earned_coursework_points'],
            $exam,
        );
    }

    /**
     * @return array{
     *     weighted_coursework_contribution: float,
     *     coursework_performance_average: ?float,
     *     exam_percentage: ?float,
     *     exam_contribution: ?float,
     *     combined_total_percentage: ?float,
     *     course_gpa: ?array
     * }
     */
    public function gradeSummary(array $breakdown, ?CourseResult $result): array
    {
        $exam = $result?->exam_percentage !== null ? (float) $result->exam_percentage : null;
        $combined = $result
            ? $this->resolveFinalPercentage($result, $breakdown, $exam)
            : null;

        return [
            'weighted_coursework_contribution' => (float) $breakdown['earned_coursework_points'],
            'coursework_performance_average' => $breakdown['coursework_percentage'],
            'exam_percentage' => $exam,
            'exam_contribution' => $exam !== null
                ? round($exam * GpaGrading::EXAM_WEIGHT, 2)
                : null,
            'combined_total_percentage' => $combined,
            'course_gpa' => $combined !== null ? GpaGrading::fromPercentage($combined) : null,
        ];
    }

    /**
     * @return array<int, array{enrolment: StudentCourseEnrolment, breakdown: array, result: ?CourseResult, semester_gpa: ?float}>
     */
    public function gradebook(CourseOffering $offering): array
    {
        $offering->load(['course', 'studentEnrolments.student', 'studentEnrolments.result']);

        $rows = [];

        foreach ($offering->studentEnrolments as $enrolment) {
            $breakdown = $this->breakdown($enrolment, $offering);
            $result = $enrolment->result;
            $exam = $result?->exam_percentage !== null ? (float) $result->exam_percentage : null;
            $final = $result
                ? $this->resolveFinalPercentage($result, $breakdown, $exam)
                : null;
            $band = $final !== null ? GpaGrading::fromPercentage($final) : null;

            $rows[] = [
                'enrolment' => $enrolment,
                'breakdown' => $breakdown,
                'result' => $result,
                'final_percentage' => $final,
                'letter_grade' => $band['letter'] ?? $result?->letter_grade,
                'grade_points' => $band['points'] ?? $result?->grade_points,
                'semester_gpa' => $this->semesterGpaForStudent($enrolment->student, $offering),
            ];
        }

        return $rows;
    }

    public function semesterGpaForStudent(Student $student, CourseOffering $contextOffering): ?float
    {
        $enrolments = $student->courseEnrolments()
            ->whereHas('courseOffering', function ($query) use ($contextOffering) {
                $query->where('academic_year', $contextOffering->academic_year)
                    ->where('semester', $contextOffering->semester);
            })
            ->with(['result', 'courseOffering.course'])
            ->get();

        $points = 0.0;
        $credits = 0.0;

        foreach ($enrolments as $enrolment) {
            $result = $enrolment->result;

            if (! $result || $result->grade_points === null) {
                continue;
            }

            $creditHours = (float) ($enrolment->courseOffering->course->credit_hours ?? 0);

            if ($creditHours <= 0) {
                continue;
            }

            $points += (float) $result->grade_points * $creditHours;
            $credits += $creditHours;
        }

        return $credits > 0 ? round($points / $credits, 2) : null;
    }

    public function validateAssignmentWeight(CourseOffering $offering, float $weight, ?int $excludeAssignmentId = null): ?string
    {
        if ($weight < 0 || $weight > self::COURSEWORK_PORTION_PERCENT) {
            return 'Each assignment may contribute between 0% and '.self::COURSEWORK_PORTION_PERCENT.'% of the course grade.';
        }

        $remaining = $this->remainingCourseworkWeight($offering, $excludeAssignmentId);

        if ($weight > $remaining + 0.001) {
            return 'Only '.number_format($remaining, 1).'% of the '.self::COURSEWORK_PORTION_PERCENT.'% coursework allocation remains.';
        }

        return null;
    }
}
