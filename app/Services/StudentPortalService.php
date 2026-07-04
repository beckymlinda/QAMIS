<?php

namespace App\Services;

use App\Models\CourseOffering;
use App\Models\EvaluationPeriod;
use App\Models\EvaluationQuestion;
use App\Models\Student;
use App\Models\TeachingEvaluation;
use Illuminate\Support\Collection;

class StudentPortalService
{
    public function enrolledOfferings(Student $student): Collection
    {
        return CourseOffering::query()
            ->whereHas('studentEnrolments', fn ($q) => $q->where('student_id', $student->id))
            ->with(['course', 'lecturer', 'timetableSlots.classroom'])
            ->orderBy('academic_year')
            ->orderBy('semester')
            ->get();
    }

    public function timetableSlots(Student $student): Collection
    {
        return $this->enrolledOfferings($student)
            ->flatMap(fn (CourseOffering $offering) => $offering->timetableSlots->map(function ($slot) use ($offering) {
                $slot->setRelation('courseOffering', $offering);

                return $slot;
            }))
            ->sortBy([
                ['day_of_week', 'asc'],
                ['start_time', 'asc'],
            ])
            ->values();
    }

    public function activeEvaluationPeriod(Student $student): ?EvaluationPeriod
    {
        return EvaluationPeriod::query()
            ->where('institution_id', $student->institution_id)
            ->where('is_active', true)
            ->where('opens_at', '<=', now())
            ->where('closes_at', '>=', now())
            ->latest('opens_at')
            ->first();
    }

    /**
     * @return Collection<int, array{offering: CourseOffering, evaluation: ?TeachingEvaluation, status: string}>
     */
    public function evaluationItems(Student $student, ?EvaluationPeriod $period = null): Collection
    {
        $period ??= $this->activeEvaluationPeriod($student);

        if (! $period) {
            return collect();
        }

        return $this->enrolledOfferings($student)
            ->filter(fn (CourseOffering $offering) => $offering->academic_year === $period->academic_year
                && (int) $offering->semester === (int) $period->semester)
            ->map(function (CourseOffering $offering) use ($student, $period) {
                $evaluation = TeachingEvaluation::query()
                    ->where('student_id', $student->id)
                    ->where('course_offering_id', $offering->id)
                    ->where('evaluation_period_id', $period->id)
                    ->first();

                $status = 'pending';
                if ($evaluation?->isSubmitted()) {
                    $status = 'submitted';
                } elseif ($evaluation) {
                    $status = 'draft';
                }

                return [
                    'offering' => $offering,
                    'evaluation' => $evaluation,
                    'status' => $status,
                    'period' => $period,
                ];
            });
    }

    public function evaluationQuestions(): Collection
    {
        return EvaluationQuestion::query()
            ->with('category')
            ->get()
            ->sortBy(fn ($q) => [$q->category->sort_order, $q->sequence_no])
            ->values();
    }

    public function publishedResults(Student $student, ?string $academicYear = null, ?int $semester = null): Collection
    {
        return $student->courseEnrolments()
            ->with([
                'courseOffering.course',
                'courseOffering.lecturer',
                'result' => fn ($q) => $q->where('is_published', true),
            ])
            ->whereHas('result', fn ($q) => $q->where('is_published', true))
            ->when($academicYear, fn ($q) => $q->whereHas(
                'courseOffering',
                fn ($q2) => $q2->where('academic_year', $academicYear)
            ))
            ->when($semester, fn ($q) => $q->whereHas(
                'courseOffering',
                fn ($q2) => $q2->where('semester', $semester)
            ))
            ->get()
            ->filter(fn ($enrolment) => $enrolment->result !== null)
            ->sortBy(fn ($e) => [$e->courseOffering->academic_year, $e->courseOffering->semester, $e->courseOffering->course->code]);
    }

    public function availableResultPeriods(Student $student): Collection
    {
        return $student->courseEnrolments()
            ->whereHas('result', fn ($q) => $q->where('is_published', true))
            ->with('courseOffering')
            ->get()
            ->map(fn ($e) => [
                'academic_year' => $e->courseOffering->academic_year,
                'semester' => $e->courseOffering->semester,
            ])
            ->unique(fn ($p) => $p['academic_year'].'-'.$p['semester'])
            ->sortByDesc(fn ($p) => $p['academic_year'].$p['semester'])
            ->values();
    }

    public function semesterGpa(Collection $enrolments): ?float
    {
        $graded = $enrolments->filter(fn ($e) => $e->result?->is_published && $e->result->grade_points !== null);

        if ($graded->isEmpty()) {
            return null;
        }

        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($graded as $enrolment) {
            $credits = (float) $enrolment->courseOffering->course->credit_hours;
            $totalPoints += $enrolment->result->grade_points * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : null;
    }
}
