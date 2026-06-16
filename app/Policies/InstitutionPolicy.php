<?php

namespace App\Policies;

use App\Models\Institution;
use App\Models\User;

class InstitutionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('institution.manage') || $user->can('institution.create');
    }

    public function view(User $user, Institution $institution): bool
    {
        if ($user->isNcheOrSystemAdmin()) {
            return true;
        }

        return $user->institution_id === $institution->id && $user->can('institution.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('institution.create');
    }

    public function update(User $user, Institution $institution): bool
    {
        if ($user->isNcheOrSystemAdmin()) {
            return $user->can('institution.manage');
        }

        return $user->institution_id === $institution->id && $user->can('institution.manage');
    }
}
