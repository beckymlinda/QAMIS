<?php

namespace App\Services;

use App\Models\CourseOffering;
use App\Models\Student;
use App\Models\StudentCourseEnrolment;
use Illuminate\Support\Collection;

class StudentCourseRegistrationService
{
    public function defaultRegistrationPeriod(Student $student): array
    {
        $latest = CourseOffering::query()
            ->where('institution_id', $student->institution_id)
            ->whereHas('course', fn ($q) => $q->where('programme_id', $student->programme_id))
            ->orderByDesc('academic_year')
            ->orderByDesc('semester')
            ->first();

        if ($latest) {
            return [
                'academic_year' => $latest->academic_year,
                'semester' => (int) $latest->semester,
            ];
        }

        $year = (int) date('Y');

        return [
            'academic_year' => "{$year}/".($year + 1),
            'semester' => (int) date('n') <= 7 ? 1 : 2,
        ];
    }

    public function resolveRegistrationPeriod(Student $student, ?string $academicYear, ?int $semester): array
    {
        $default = $this->defaultRegistrationPeriod($student);

        return [
            'academic_year' => $academicYear ?: $default['academic_year'],
            'semester' => $semester ?: $default['semester'],
        ];
    }

    /**
     * Course offerings a student may register for this semester per programme curriculum.
     */
    public function registerableOfferings(Student $student, string $academicYear, int $semester): Collection
    {
        $enrolledIds = $student->courseEnrolments()->pluck('course_offering_id');

        return CourseOffering::query()
            ->where('institution_id', $student->institution_id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->whereHas('course', function ($query) use ($student, $semester) {
                $query->where('programme_id', $student->programme_id)
                    ->where(function ($q) use ($student) {
                        $q->whereNull('year_level')
                            ->orWhere('year_level', $student->year_of_study);
                    })
                    ->where(function ($q) use ($semester) {
                        $q->whereNull('semester_number')
                            ->orWhere('semester_number', $semester);
                    });
            })
            ->whereNotIn('id', $enrolledIds)
            ->with(['course', 'lecturer'])
            ->get()
            ->sortBy(fn (CourseOffering $o) => $o->course?->code ?? '')
            ->values();
    }

    public function enrolledOfferings(Student $student, ?string $academicYear = null, ?int $semester = null): Collection
    {
        return CourseOffering::query()
            ->whereHas('studentEnrolments', fn ($q) => $q->where('student_id', $student->id))
            ->when($academicYear, fn ($q) => $q->where('academic_year', $academicYear))
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->with(['course', 'lecturer'])
            ->get()
            ->sortBy(fn (CourseOffering $o) => $o->course?->code ?? '');
    }

    public function canRegister(Student $student, CourseOffering $offering): bool
    {
        if ($offering->course?->programme_id !== $student->programme_id) {
            return false;
        }

        $course = $offering->course;

        if ($course->year_level !== null && (int) $course->year_level !== (int) $student->year_of_study) {
            return false;
        }

        if ($course->semester_number !== null && (int) $course->semester_number !== (int) $offering->semester) {
            return false;
        }

        return ! $student->courseEnrolments()
            ->where('course_offering_id', $offering->id)
            ->exists();
    }

    public function register(Student $student, CourseOffering $offering): StudentCourseEnrolment
    {
        abort_unless($this->canRegister($student, $offering), 403, 'This course is not available for registration on your programme.');

        return StudentCourseEnrolment::create([
            'student_id' => $student->id,
            'course_offering_id' => $offering->id,
            'status' => 'registered',
        ]);
    }
}
