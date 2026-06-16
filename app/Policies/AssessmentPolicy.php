<?php

namespace App\Policies;

use App\Enums\AssessmentStatus;
use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('assessment.create') || $user->can('assessment.review') || $user->can('dashboard.view');
    }

    public function view(User $user, Assessment $assessment): bool
    {
        return $this->sameInstitution($user, $assessment);
    }

    public function create(User $user): bool
    {
        return $user->can('assessment.create');
    }

    public function score(User $user, Assessment $assessment): bool
    {
        return $this->sameInstitution($user, $assessment)
            && $user->can('assessment.score')
            && $assessment->isEditable();
    }

    public function transition(User $user, Assessment $assessment, AssessmentStatus $toStatus): bool
    {
        if (! $this->sameInstitution($user, $assessment)) {
            return false;
        }

        return match ($toStatus) {
            AssessmentStatus::Submitted => $user->can('assessment.create'),
            AssessmentStatus::Reviewed => $user->can('assessment.review'),
            AssessmentStatus::Approved, AssessmentStatus::Locked => $user->can('assessment.approve'),
            AssessmentStatus::Draft => $user->can('assessment.review'),
        };
    }

    protected function sameInstitution(User $user, Assessment $assessment): bool
    {
        if ($user->isNcheOrSystemAdmin()) {
            return true;
        }

        return $user->institution_id === $assessment->institution_id;
    }
}
