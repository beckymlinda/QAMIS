<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.manage');
    }

    public function view(User $actor, User $target): bool
    {
        return $this->manage($actor, $target);
    }

    public function create(User $user): bool
    {
        return $user->can('user.manage');
    }

    public function update(User $actor, User $target): bool
    {
        return $this->manage($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        if (! $this->manage($actor, $target)) {
            return false;
        }

        if ($target->hasRole('system_admin') && ! $actor->hasRole('system_admin')) {
            return false;
        }

        return true;
    }

    protected function manage(User $actor, User $target): bool
    {
        if (! $actor->can('user.manage')) {
            return false;
        }

        if ($actor->isNcheOrSystemAdmin()) {
            return true;
        }

        if ($target->isNcheOrSystemAdmin()) {
            return false;
        }

        return true;
    }
}
