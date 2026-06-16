<?php

namespace App\Policies;

use App\Models\User;

class OrgUnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('institution.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('institution.manage');
    }
}
