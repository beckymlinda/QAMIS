<?php

namespace App\Services;

use App\Models\CourseOffering;
use App\Models\StaffMember;
use App\Models\TeachingEvaluation;
use Illuminate\Support\Collection;

class LecturerPortalService
{
    public function staffProfile(int $userId): ?StaffMember
    {
        return StaffMember::query()
            ->where('user_id', $userId)
            ->with('programme')
            ->first();
    }

    public function offerings(StaffMember $staff): Collection
    {
        return CourseOffering::query()
            ->where('staff_member_id', $staff->id)
            ->with(['course.programme', 'timetableSlots.classroom', 'studentEnrolments.student'])
            ->orderByDesc('academic_year')
            ->orderBy('semester')
            ->get();
    }

    public function timetableSlots(StaffMember $staff): Collection
    {
        return $this->offerings($staff)
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

    public function evaluationSummary(StaffMember $staff): Collection
    {
        $offeringIds = $this->offerings($staff)->pluck('id');

        return CourseOffering::query()
            ->whereIn('id', $offeringIds)
            ->with(['course', 'teachingEvaluations' => fn ($q) => $q->where('status', 'submitted')])
            ->get()
            ->map(function (CourseOffering $offering) {
                $submitted = $offering->teachingEvaluations->count();

                return [
                    'offering' => $offering,
                    'response_count' => $submitted,
                ];
            });
    }
}
