<?php

namespace App\Http\Controllers;

use App\Enums\AssessmentStatus;
use App\Models\Assessment;
use App\Models\AssessmentResponse;
use App\Models\AssessmentSection;
use App\Models\AssessmentTemplate;
use App\Models\Programme;
use App\Services\AssessmentStrengthsAnalysis;
use App\Services\AssessmentWorkflowService;
use App\Services\ComplianceEngine;
use App\Support\InstitutionScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function __construct(
        protected ComplianceEngine $complianceEngine,
        protected AssessmentWorkflowService $workflowService,
        protected AssessmentStrengthsAnalysis $strengthsAnalysis,
    ) {}

    public function index(): View
    {
        return redirect()->route('assessments.institution.index');
    }

    public function institutionIndex(): View
    {
        $this->authorize('viewAny', Assessment::class);
        $assessments = InstitutionScope::apply(Assessment::query())
            ->where('assessment_type', 'institutional')
            ->with(['template', 'complianceResult'])
            ->latest()
            ->paginate(20);

        return view('assessments.institution.index', compact('assessments'));
    }

    public function programmeIndex(): View
    {
        $this->authorize('viewAny', Assessment::class);
        $assessments = InstitutionScope::apply(Assessment::query())
            ->where('assessment_type', 'programme')
            ->with(['template', 'programme', 'complianceResult'])
            ->latest()
            ->paginate(20);

        return view('assessments.programme.index', compact('assessments'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Assessment::class);
        $templates = AssessmentTemplate::where('is_active', true)->get();
        $programmes = InstitutionScope::apply(Programme::query())->orderBy('name')->get();
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

        return redirect()->route('assessments.edit', $assessment)
            ->with('success', 'Assessment created. You can now score each section.');
    }

    public function show(Assessment $assessment): View
    {
        $this->authorize('view', $assessment);

        $assessment = $this->loadAssessment($assessment);
        $analysis = $this->strengthsAnalysis->analyze($assessment);
        $sectionRows = $this->buildSectionRows($assessment);

        return view('assessments.show', compact('assessment', 'analysis', 'sectionRows'));
    }

    public function edit(Assessment $assessment): View
    {
        $this->authorize('update', $assessment);
        $this->syncMissingResponses($assessment);

        $assessment = $this->loadAssessment($assessment);

        return view('assessments.edit', compact('assessment'));
    }

    public function showSection(Assessment $assessment, AssessmentSection $section): View
    {
        $this->authorize('view', $assessment);
        abort_unless($section->assessment_template_id === $assessment->assessment_template_id, 404);

        $assessment = $this->loadAssessment($assessment);

        $responses = $assessment->responses
            ->whereIn('assessment_criterion_id', $section->criteria->pluck('id'))
            ->sortBy(fn ($response) => $response->criterion?->sequence_no ?? 0)
            ->values();

        $scoredCount = $responses->whereNotNull('score')->count();
        $summary = $assessment->sectionSummaries->firstWhere('assessment_section_id', $section->id);

        $strengths = $responses->filter(fn ($response) => $response->score !== null && $response->score >= 3)->values();
        $improvements = $responses->filter(fn ($response) => $response->score !== null && $response->score <= 2)->values();
        $significantGaps = $this->strengthsAnalysis->significantGapsForSection($assessment, $section->id);
        $hasGapSection = $significantGaps->isNotEmpty();

        return view('assessments.section', compact(
            'assessment',
            'section',
            'responses',
            'summary',
            'scoredCount',
            'strengths',
            'improvements',
            'significantGaps',
            'hasGapSection',
        ));
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $this->authorize('delete', $assessment);

        $assessment->delete();

        return redirect()->route('assessments.index')
            ->with('success', 'Assessment deleted.');
    }

    public function score(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('score', $assessment);

        if ($assessment->isReadOnly()) {
            return back()->with('error', 'Assessment is read-only.');
        }

        $normalizedResponses = collect($request->input('responses', []))
            ->map(function (array $item): array {
                if (! array_key_exists('score', $item) || $item['score'] === '' || $item['score'] === null) {
                    $item['score'] = null;
                } else {
                    $item['score'] = (int) $item['score'];
                }

                return $item;
            })
            ->values()
            ->all();

        $request->merge(['responses' => $normalizedResponses]);

        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*.id' => 'required|exists:assessment_responses,id',
            'responses.*.score' => 'nullable|integer|min:0|max:4',
            'responses.*.comments' => 'nullable|string',
        ]);

        foreach ($validated['responses'] as $data) {
            $response = AssessmentResponse::with('criterion.rubricLevels')->findOrFail($data['id']);
            if ($response->assessment_id !== $assessment->id) {
                continue;
            }

            $score = array_key_exists('score', $data) && $data['score'] !== null && $data['score'] !== ''
                ? (int) $data['score']
                : null;
            $rubricDescriptor = null;

            if ($score !== null) {
                $rubricDescriptor = $response->criterion?->rubricLevels
                    ?->firstWhere('score', $score)
                    ?->descriptor;
            }

            $response->update([
                'score' => $score,
                'comments' => $data['comments'] ?? null,
                'strengths' => $score !== null && $score >= 3
                    ? ($rubricDescriptor ?? 'Demonstrates good performance against this criterion.')
                    : null,
                'areas_for_improvement' => $score !== null && $score <= 2
                    ? ($rubricDescriptor ?? 'Requires improvement against this criterion.')
                    : null,
                'recommendations' => $score !== null && $score <= 2
                    ? 'Develop and implement a corrective action plan for this criterion.'
                    : null,
                'scored_by' => auth()->id(),
                'scored_at' => now(),
            ]);
        }

        $this->complianceEngine->compute($assessment->fresh());

        $sectionName = AssessmentResponse::with('criterion.section')
            ->find($validated['responses'][0]['id'] ?? null)
            ?->criterion
            ?->section
            ?->title;

        $message = $sectionName
            ? "Scores saved for {$sectionName} and compliance recalculated."
            : 'Scores saved and compliance recalculated.';

        return redirect()
            ->route('assessments.edit', $assessment)
            ->with('success', $message);
    }

    public function updateRecommendations(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        $validated = $request->validate([
            'recommendations' => 'nullable|array',
            'recommendations.*' => 'nullable|string|max:5000',
        ]);

        $items = collect($validated['recommendations'] ?? [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        $assessment->update(['narrative_recommendations' => $items]);

        return back()->with('success', 'Assessment recommendations saved.');
    }

    public function transition(Request $request, Assessment $assessment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $toStatus = AssessmentStatus::from($validated['status']);
        $this->authorize('transition', [$assessment, $toStatus]);

        if ($toStatus === AssessmentStatus::Submitted && ! $assessment->responses()->whereNotNull('score')->exists()) {
            return back()->with('error', 'No saved scores found. Select compliance levels and click the section Save button before submitting.');
        }

        $this->workflowService->transition($assessment, $toStatus, $validated['notes'] ?? null);

        if ($toStatus === AssessmentStatus::Submitted) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('success', 'Assessment submitted. Review your section scores below.');
        }

        return back()->with('success', 'Assessment status updated to '.$toStatus->label().'.');
    }

    protected function loadAssessment(Assessment $assessment): Assessment
    {
        return $assessment->load([
            'template.sections.criteria.rubricLevels',
            'responses.criterion.section',
            'responses.criterion.rubricLevels',
            'complianceResult',
            'sectionSummaries.section',
            'programme',
            'workflowHistory',
        ]);
    }

    /**
     * @return array<int, array{section: AssessmentSection, summary: ?\App\Models\AssessmentSectionSummary, scored_count: int, total_items: int}>
     */
    protected function buildSectionRows(Assessment $assessment): array
    {
        $rows = [];

        foreach ($assessment->template?->sections ?? [] as $section) {
            $responses = $assessment->responses
                ->whereIn('assessment_criterion_id', $section->criteria->pluck('id'));

            $rows[] = [
                'section' => $section,
                'summary' => $assessment->sectionSummaries->firstWhere('assessment_section_id', $section->id),
                'scored_count' => $responses->whereNotNull('score')->count(),
                'total_items' => $section->criteria->count(),
            ];
        }

        return $rows;
    }

    protected function syncMissingResponses(Assessment $assessment): void
    {
        $assessment->loadMissing('template.sections.criteria');

        if (! $assessment->template) {
            return;
        }

        $existingCriterionIds = $assessment->responses()->pluck('assessment_criterion_id');

        foreach ($assessment->template->sections as $section) {
            foreach ($section->criteria as $criterion) {
                if ($existingCriterionIds->contains($criterion->id)) {
                    continue;
                }

                AssessmentResponse::create([
                    'assessment_id' => $assessment->id,
                    'assessment_criterion_id' => $criterion->id,
                ]);
            }
        }
    }
}
