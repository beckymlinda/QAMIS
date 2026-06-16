<?php

namespace App\Policies;

use App\Models\EvidenceDocument;
use App\Models\User;

class EvidencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('evidence.upload') || $user->can('assessment.review');
    }

    public function create(User $user): bool
    {
        return $user->can('evidence.upload');
    }
}
