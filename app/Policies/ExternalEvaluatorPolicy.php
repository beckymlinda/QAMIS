<?php

namespace App\Policies;

use App\Models\User;

class ExternalEvaluatorPolicy
{
    public function create(User $user): bool
    {
        return $user->can('user.manage') || $user->hasRole('nche_admin');
    }
}
