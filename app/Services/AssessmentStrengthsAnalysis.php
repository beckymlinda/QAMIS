<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentResponse;
use Illuminate\Support\Collection;

class AssessmentStrengthsAnalysis
{
    /**
     * @return array{
     *     strengths: Collection<int, AssessmentResponse>,
     *     areas_for_improvement: Collection<int, AssessmentResponse>,
     *     unscored: Collection<int, AssessmentResponse>
     * }
     */
    public function analyze(Assessment $assessment): array
    {
        $assessment->loadMissing(['responses.criterion.section', 'responses.criterion.rubricLevels']);

        $responses = $assessment->responses
            ->filter(fn (AssessmentResponse $response) => $response->score !== null)
            ->values();

        return [
            'strengths' => $responses
                ->filter(fn (AssessmentResponse $response) => $response->score >= 3)
                ->sortBy(fn (AssessmentResponse $response) => [
                    $response->criterion?->section?->sort_order ?? 0,
                    $response->criterion?->sequence_no ?? 0,
                ])
                ->values(),
            'areas_for_improvement' => $responses
                ->filter(fn (AssessmentResponse $response) => $response->score <= 2)
                ->sortBy(fn (AssessmentResponse $response) => [
                    $response->criterion?->section?->sort_order ?? 0,
                    $response->criterion?->sequence_no ?? 0,
                ])
                ->values(),
            'unscored' => $assessment->responses
                ->filter(fn (AssessmentResponse $response) => $response->score === null)
                ->values(),
        ];
    }

    /**
     * @return array<int, array{section: string, criterion: string, score: int, descriptor: ?string, comments: ?string}>
     */
    public function strengthsSummary(Assessment $assessment): array
    {
        return $this->analyze($assessment)['strengths']
            ->map(fn (AssessmentResponse $response) => $this->formatItem($response))
            ->all();
    }

    /**
     * @return array<int, array{section: string, criterion: string, score: int, descriptor: ?string, comments: ?string}>
     */
    public function improvementsSummary(Assessment $assessment): array
    {
        return $this->analyze($assessment)['areas_for_improvement']
            ->map(fn (AssessmentResponse $response) => $this->formatItem($response))
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, AssessmentResponse>
     */
    public function significantGaps(Assessment $assessment): Collection
    {
        $assessment->loadMissing(['responses.criterion.section', 'responses.criterion.rubricLevels', 'complianceResult']);

        $gaps = $this->analyze($assessment)['areas_for_improvement']->keyBy(
            fn (AssessmentResponse $response) => $response->assessment_criterion_id
        );

        foreach ($assessment->complianceResult?->mandatory_failures ?? [] as $failure) {
            $criterionId = $failure['criterion_id'] ?? null;
            if (! $criterionId || $gaps->has($criterionId)) {
                continue;
            }

            $response = $assessment->responses->firstWhere('assessment_criterion_id', $criterionId);
            if ($response && $response->score !== null) {
                $gaps->put($criterionId, $response);
            }
        }

        return $gaps->sortBy(fn (AssessmentResponse $response) => [
            $response->criterion?->section?->sort_order ?? 0,
            $response->criterion?->sequence_no ?? 0,
        ])->values();
    }

    public function significantGapsForSection(Assessment $assessment, int $sectionId): Collection
    {
        return $this->significantGaps($assessment)
            ->filter(fn (AssessmentResponse $response) => $response->criterion?->assessment_section_id === $sectionId)
            ->values();
    }

    public function isNonCompliant(Assessment $assessment): bool
    {
        $result = $assessment->complianceResult;

        if (! $result) {
            return false;
        }

        return $result->compliance_status === \App\Enums\ComplianceStatus::NonCompliant
            || $result->accreditation_recommendation === \App\Enums\AccreditationRecommendation::NotReady;
    }

    public function gapGuidance(AssessmentResponse $response): array
    {
        $response->loadMissing('criterion.rubricLevels');

        $criterion = $response->criterion;
        $targetScore = max(3, (int) ($criterion?->minimum_score ?? 3));
        $currentScore = (int) $response->score;

        $currentRubric = $criterion?->rubricLevels?->firstWhere('score', $currentScore);
        $targetRubric = $criterion?->rubricLevels?->firstWhere('score', $targetScore);

        if (! $targetRubric) {
            $defaults = \App\Support\DefaultScoringRubric::levels();
            $targetRubric = (object) [
                'level_label' => $defaults[$targetScore]['label'],
                'descriptor' => $defaults[$targetScore]['descriptor'],
            ];
        }

        if (! $currentRubric) {
            $defaults = \App\Support\DefaultScoringRubric::levels();
            $currentRubric = (object) [
                'level_label' => $defaults[$currentScore]['label'] ?? 'Below standard',
                'descriptor' => $defaults[$currentScore]['descriptor'] ?? 'Does not meet the required standard.',
            ];
        }

        $whyFailed = "Scored {$currentScore}/4 ({$currentRubric->level_label}): {$currentRubric->descriptor}";

        if ($criterion?->is_mandatory) {
            $whyFailed .= " This is a mandatory item requiring a minimum score of {$targetScore}.";
        }

        return [
            'why_failed' => $whyFailed,
            'target_score' => $targetScore,
            'target_label' => $targetRubric->level_label,
            'action_required' => "To pass, achieve at least score {$targetScore} ({$targetRubric->level_label}): {$targetRubric->descriptor}",
        ];
    }

    protected function formatItem(AssessmentResponse $response): array
    {
        $rubric = $response->criterion?->rubricLevels?->firstWhere('score', $response->score);

        return [
            'section' => $response->criterion?->section?->title ?? 'General',
            'criterion' => $response->criterion?->title ?? 'Criterion',
            'score' => (int) $response->score,
            'descriptor' => $rubric?->descriptor,
            'comments' => $response->comments,
        ];
    }
}
