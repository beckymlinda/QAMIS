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
use App\Models\ProgrammeApplication;
use App\Models\Student;
use App\Models\User;
use App\Policies\AssessmentPolicy;
use App\Policies\CorrectiveActionPolicy;
use App\Policies\DashboardPolicy;
use App\Policies\EvidencePolicy;
use App\Policies\ExternalEvaluatorPolicy;
use App\Policies\InstitutionPolicy;
use App\Policies\OrgUnitPolicy;
use App\Policies\ProgrammePolicy;
use App\Policies\ReportPolicy;
use App\Policies\StudentPolicy;
use App\Policies\UserPolicy;
use App\Services\LmsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Gate::policy(Institution::class, InstitutionPolicy::class);
        Gate::policy(ProgrammeApplication::class, \App\Policies\ProgrammeApplicationPolicy::class);
        Gate::policy(Programme::class, ProgrammePolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(OrgUnit::class, OrgUnitPolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(EvidenceDocument::class, EvidencePolicy::class);
        Gate::policy(GeneratedReport::class, ReportPolicy::class);
        Gate::policy(CorrectiveAction::class, CorrectiveActionPolicy::class);
        Gate::policy(ComplianceDashboardCache::class, DashboardPolicy::class);
        Gate::policy(ExternalEvaluatorInvitation::class, ExternalEvaluatorPolicy::class);

        View::composer(['layouts.topbar', 'layouts.sidebar', 'layouts.app'], function ($view): void {
            if (auth()->check() && (auth()->user()->hasRole('student') || auth()->user()->hasRole('lecturer'))) {
                $view->with('lmsUnreadCount', app(LmsService::class)->unreadNotifications(auth()->user()));
            }

            $branding = [
                'primary' => '#0f2744',
                'secondary' => '#8cc63f',
                'name' => config('app.short_name', 'EDUC - HEMIS'),
                'logo_url' => file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : null,
            ];

            if (auth()->check() && auth()->user()->institution_id) {
                $website = \App\Models\InstitutionWebsiteSetting::query()
                    ->where('institution_id', auth()->user()->institution_id)
                    ->first();

                if ($website) {
                    $branding = $website->branding();
                }
            }

            $view->with('institutionBranding', $branding);
        });
    }
}
