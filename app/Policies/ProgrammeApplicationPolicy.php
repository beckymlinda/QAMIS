<?php

namespace App\Policies;

use App\Models\ProgrammeApplication;
use App\Models\User;

class ProgrammeApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('application.manage');
    }

    public function view(User $user, ProgrammeApplication $application): bool
    {
        return $user->can('application.manage')
            && ($user->isNcheOrSystemAdmin() || $user->institution_id === $application->institution_id);
    }

    public function update(User $user, ProgrammeApplication $application): bool
    {
        return $this->view($user, $application);
    }
}
