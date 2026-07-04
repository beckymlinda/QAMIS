<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\ComplianceDashboardCache;
use App\Models\CorrectiveAction;
use App\Models\EvidenceDocument;
use App\Models\GeneratedReport;
use App\Models\GovernanceMember;
use App\Models\Programme;
use App\Models\StaffMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if (auth()->user()?->isStudent()) {
            return redirect()->route('student.dashboard');
        }

        if (auth()->user()?->isLecturer()) {
            return redirect()->route('lecturer.dashboard');
        }

        $this->authorize('view', ComplianceDashboardCache::class);

        $institutionId = auth()->user()->institution_id ?? $request->session()->get('active_institution_id');

        $cache = ComplianceDashboardCache::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->where('scope_type', 'institution')
            ->first();

        $stats = [
            'programmes' => Programme::when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))->count(),
            'assessments' => Assessment::when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))->count(),
            'evidence' => EvidenceDocument::when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))->count(),
            'staff' => StaffMember::when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))->count(),
            'reports' => GeneratedReport::when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))->count(),
            'outstanding_actions' => CorrectiveAction::query()
                ->whereHas('recommendation', fn ($q) => $q->when($institutionId, fn ($q2) => $q2->where('institution_id', $institutionId)))
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
        ];

        $recentAssessments = Assessment::with('complianceResult')
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('cache', 'stats', 'recentAssessments'));
    }
}
