<?php

namespace App\Policies;

use App\Models\CorrectiveAction;
use App\Models\User;

class CorrectiveActionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('corrective_action.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('corrective_action.manage');
    }

    public function update(User $user, CorrectiveAction $action): bool
    {
        return $user->can('corrective_action.manage')
            || $user->id === $action->assigned_to;
    }
}
