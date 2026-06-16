<?php

namespace App\Providers;

use App\Models\Assessment;
use App\Models\ComplianceDashboardCache;
use App\Models\CorrectiveAction;
use App\Models\EvidenceDocument;
use App\Models\ExternalEvaluatorInvitation;
use App\Models\GeneratedReport;
use App\Models\Institution;
use App\Models\OrgUnit;
use App\Models\Programme;
use App\Policies\AssessmentPolicy;
use App\Policies\CorrectiveActionPolicy;
use App\Policies\DashboardPolicy;
use App\Policies\EvidencePolicy;
use App\Policies\ExternalEvaluatorPolicy;
use App\Policies\InstitutionPolicy;
use App\Policies\OrgUnitPolicy;
use App\Policies\ProgrammePolicy;
use App\Policies\ReportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Institution::class, InstitutionPolicy::class);
        Gate::policy(Programme::class, ProgrammePolicy::class);
        Gate::policy(OrgUnit::class, OrgUnitPolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(EvidenceDocument::class, EvidencePolicy::class);
        Gate::policy(GeneratedReport::class, ReportPolicy::class);
        Gate::policy(CorrectiveAction::class, CorrectiveActionPolicy::class);
        Gate::policy(ComplianceDashboardCache::class, DashboardPolicy::class);
        Gate::policy(ExternalEvaluatorInvitation::class, ExternalEvaluatorPolicy::class);
    }
}
