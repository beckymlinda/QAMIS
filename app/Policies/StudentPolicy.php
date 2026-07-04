<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('programme.manage');
    }

    public function view(User $user, Student $student): bool
    {
        return $this->manage($user, $student);
    }

    public function create(User $user): bool
    {
        return $user->can('programme.manage')
            && ($user->isNcheOrSystemAdmin() || $user->institution_id !== null);
    }

    public function update(User $user, Student $student): bool
    {
        return $this->manage($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $this->manage($user, $student);
    }

    protected function manage(User $user, Student $student): bool
    {
        if (! $user->can('programme.manage')) {
            return false;
        }

        return $user->isNcheOrSystemAdmin() || $user->institution_id === $student->institution_id;
    }
}
