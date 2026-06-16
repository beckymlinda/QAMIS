<?php

namespace App\Policies;

use App\Models\Programme;
use App\Models\User;

class ProgrammePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('programme.manage') || $user->can('dashboard.view');
    }

    public function view(User $user, Programme $programme): bool
    {
        return $user->isNcheOrSystemAdmin() || $user->institution_id === $programme->institution_id;
    }

    public function create(User $user): bool
    {
        return $user->can('programme.manage');
    }
}
