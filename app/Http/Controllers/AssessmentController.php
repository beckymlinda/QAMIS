<?php

namespace App\Http\Controllers;

use App\Enums\AssessmentStatus;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentResponse;
use App\Models\AssessmentTemplate;
use App\Models\Programme;
use App\Services\AssessmentWorkflowService;
use App\Services\ComplianceEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function __construct(
        protected ComplianceEngine $complianceEngine,
        protected AssessmentWorkflowService $workflowService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Assessment::class);
        $assessments = Assessment::with(['template', 'programme', 'complianceResult'])
            ->latest()
            ->paginate(20);

        return view('assessments.index', compact('assessments'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Assessment::class);
        $templates = AssessmentTemplate::where('is_active', true)->get();
        $programmes = Programme::orderBy('name')->get();
        $type = $request->query('type', 'institutional');

        return view('assessments.create', compact('templates', 'programmes', 'type'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Assessment::class);

        $validated = $request->validate([
            'assessment_template_id' => 'required|exists:assessment_templates,id',
            'assessment_type' => 'required|in:institutional,programme',
            'programme_id' => 'nullable|required_if:assessment_type,programme|exists:programmes,id',
            'title' => 'required|string|max:255',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
            'assessor_names' => 'nullable|string|max:500',
        ]);

        $template = AssessmentTemplate::findOrFail($validated['assessment_template_id']);

        $assessment = Assessment::create([
            ...$validated,
            'institution_id' => auth()->user()->institution_id,
            'status' => AssessmentStatus::Draft,
        ]);

        foreach ($template->sections as $section) {
            foreach ($section->criteria as $criterion) {
                AssessmentResponse::create([
                    'assessment_id' => $assessment->id,
                    'assessment_criterion_id' => $criterion->id,
                ]);
            }
        }

        return redirect()->route('assessments.show', $assessment)
            ->with('success', 'Assessment created.');
    }

    public function show(Assessment $assessment): View
    {
        $this->authorize('view', $assessment);
        $assessment->load([
            'template.sections.criteria',
            'responses.criterion.section',
            'complianceResult',
            'sectionSummaries.section',
            'programme',
            'workflowHistory',
        ]);

        return view('assessments.show', compact('assessment'));
    }

    public function score(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('score', $assessment);

        if ($assessment->isReadOnly()) {
            return back()->with('error', 'Assessment is read-only.');
        }

        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*.id' => 'required|exists:assessment_responses,id',
            'responses.*.score' => 'nullable|integer|min:0|max:4',
            'responses.*.comments' => 'nullable|string',
            'responses.*.strengths' => 'nullable|string',
            'responses.*.areas_for_improvement' => 'nullable|string',
            'responses.*.recommendations' => 'nullable|string',
        ]);

        foreach ($validated['responses'] as $data) {
            $response = AssessmentResponse::findOrFail($data['id']);
            if ($response->assessment_id !== $assessment->id) {
                continue;
            }

            $response->update([
                'score' => $data['score'] ?? null,
                'comments' => $data['comments'] ?? null,
                'strengths' => $data['strengths'] ?? null,
                'areas_for_improvement' => $data['areas_for_improvement'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'scored_by' => auth()->id(),
                'scored_at' => now(),
            ]);
        }

        $this->complianceEngine->compute($assessment);

        return back()->with('success', 'Scores saved and compliance recalculated.');
    }

    public function transition(Request $request, Assessment $assessment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $toStatus = AssessmentStatus::from($validated['status']);
        $this->authorize('transition', [$assessment, $toStatus]);

        $this->workflowService->transition($assessment, $toStatus, $validated['notes'] ?? null);

        return back()->with('success', 'Assessment status updated to '.$toStatus->label().'.');
    }
}
