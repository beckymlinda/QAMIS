<?php

namespace App\Policies;

use App\Models\GeneratedReport;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('report.view');
    }

    public function view(User $user, GeneratedReport $report): bool
    {
        return $user->isNcheOrSystemAdmin() || $user->institution_id === $report->institution_id;
    }

    public function generate(User $user): bool
    {
        return $user->can('report.generate');
    }
}
