<?php

namespace App\Services;

use App\Enums\AccreditationRecommendation;
use App\Enums\AssessmentStatus;
use App\Enums\ComplianceStatus;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentResponse;
use App\Models\AssessmentSection;
use App\Models\AssessmentSectionSummary;
use App\Models\ComplianceDashboardCache;
use App\Models\ComplianceResult;
use App\Models\CorrectiveAction;
use App\Models\Recommendation;
use Illuminate\Support\Collection;

class ComplianceEngine
{
    public function compute(Assessment $assessment): ComplianceResult
    {
        $assessment->load(['template.sections.criteria', 'responses.criterion']);

        $sectionSummaries = [];
        $mandatoryFailures = [];
        $sectionAggregates = [];

        foreach ($assessment->template->sections as $section) {
            $criteria = $section->criteria->whereNull('parent_criterion_id');
            $responses = $this->responsesForSection($assessment, $section);
            $totalScore = $responses->sum(fn ($r) => $r->score ?? 0);
            $divisor = max($section->divisor, 1);
            $aggregate = round($totalScore / $divisor, 2);

            foreach ($responses as $response) {
                $criterion = $response->criterion;
                if ($criterion?->is_mandatory && ($response->score === null || $response->score < $criterion->minimum_score)) {
                    $mandatoryFailures[] = [
                        'criterion_id' => $criterion->id,
                        'title' => $criterion->title,
                        'score' => $response->score,
                        'minimum' => $criterion->minimum_score,
                    ];
                }
            }

            $sectionSummaries[] = AssessmentSectionSummary::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'assessment_section_id' => $section->id,
                ],
                [
                    'total_score' => $totalScore,
                    'aggregate_score' => $aggregate,
                    'divisor' => $divisor,
                ]
            );

            $sectionAggregates[] = $aggregate;
        }

        $overallAverage = count($sectionAggregates)
            ? round(collect($sectionAggregates)->avg(), 2)
            : 0;

        $hasMandatoryFailures = count($mandatoryFailures) > 0;
        $missingMandatoryEvidence = $this->hasMissingMandatoryEvidence($assessment, collect($mandatoryFailures));

        $complianceStatus = $this->determineComplianceStatus($overallAverage, $hasMandatoryFailures);
        $recommendation = $this->determineRecommendation(
            $complianceStatus,
            $hasMandatoryFailures,
            $missingMandatoryEvidence,
            $assessment
        );

        $result = ComplianceResult::updateOrCreate(
            ['assessment_id' => $assessment->id],
            [
                'overall_average' => $overallAverage,
                'compliance_status' => $complianceStatus,
                'accreditation_recommendation' => $recommendation,
                'mandatory_failures' => $mandatoryFailures,
                'risk_level' => $this->determineRiskLevel($overallAverage, $hasMandatoryFailures),
                'missing_mandatory_evidence' => $missingMandatoryEvidence,
                'computed_at' => now(),
            ]
        );

        $this->syncRecommendationsFromSummaries($assessment, $sectionSummaries);
        $this->refreshDashboardCache($assessment);

        return $result;
    }

    protected function responsesForSection(Assessment $assessment, AssessmentSection $section): Collection
    {
        $criterionIds = AssessmentCriterion::query()
            ->where('assessment_section_id', $section->id)
            ->pluck('id');

        return AssessmentResponse::query()
            ->where('assessment_id', $assessment->id)
            ->whereIn('assessment_criterion_id', $criterionIds)
            ->with('criterion')
            ->get();
    }

    protected function determineComplianceStatus(float $average, bool $hasMandatoryFailures): ComplianceStatus
    {
        if ($average <= 1.99 || $hasMandatoryFailures) {
            return ComplianceStatus::NonCompliant;
        }

        if ($average >= 3.0) {
            return ComplianceStatus::FullyCompliant;
        }

        return ComplianceStatus::PartiallyCompliant;
    }

    protected function determineRecommendation(
        ComplianceStatus $status,
        bool $hasMandatoryFailures,
        bool $missingMandatoryEvidence,
        Assessment $assessment
    ): AccreditationRecommendation {
        $outstandingActions = CorrectiveAction::query()
            ->whereHas('recommendation', fn ($q) => $q->where('assessment_id', $assessment->id))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($missingMandatoryEvidence || $hasMandatoryFailures) {
            return $outstandingActions
                ? AccreditationRecommendation::Deferred
                : AccreditationRecommendation::NotReady;
        }

        return match ($status) {
            ComplianceStatus::FullyCompliant => AccreditationRecommendation::Ready,
            ComplianceStatus::PartiallyCompliant => AccreditationRecommendation::WithConditions,
            ComplianceStatus::NonCompliant => $outstandingActions
                ? AccreditationRecommendation::Deferred
                : AccreditationRecommendation::NotReady,
        };
    }

    protected function hasMissingMandatoryEvidence(Assessment $assessment, Collection $mandatoryFailures): bool
    {
        if ($mandatoryFailures->isEmpty()) {
            return false;
        }

        $failedCriterionIds = $mandatoryFailures->pluck('criterion_id');

        $responsesWithEvidence = AssessmentResponse::query()
            ->where('assessment_id', $assessment->id)
            ->whereIn('assessment_criterion_id', $failedCriterionIds)
            ->whereHas('evidenceVersions')
            ->pluck('assessment_criterion_id');

        return $failedCriterionIds->diff($responsesWithEvidence)->isNotEmpty();
    }

    protected function determineRiskLevel(float $average, bool $hasMandatoryFailures): string
    {
        if ($hasMandatoryFailures || $average < 2) {
            return 'high';
        }

        if ($average < 3) {
            return 'medium';
        }

        return 'low';
    }

    protected function syncRecommendationsFromSummaries(Assessment $assessment, array $summaries): void
    {
        foreach ($summaries as $summary) {
            if ($summary->recommendations) {
                Recommendation::updateOrCreate(
                    [
                        'assessment_id' => $assessment->id,
                        'assessment_section_id' => $summary->assessment_section_id,
                        'source' => 'section_summary',
                    ],
                    [
                        'institution_id' => $assessment->institution_id,
                        'description' => $summary->recommendations,
                        'status' => 'open',
                    ]
                );
            }
        }
    }

    protected function refreshDashboardCache(Assessment $assessment): void
    {
        $result = $assessment->fresh()->complianceResult;
        if (! $result) {
            return;
        }

        $pct = min(100, round(($result->overall_average / 4) * 100, 2));

        ComplianceDashboardCache::updateOrCreate(
            [
                'institution_id' => $assessment->institution_id,
                'scope_type' => $assessment->programme_id ? 'programme' : 'institution',
                'scope_id' => $assessment->programme_id,
            ],
            [
                'overall_compliance_pct' => $pct,
                'risk_level' => $result->risk_level,
                'outstanding_actions' => CorrectiveAction::query()
                    ->whereHas('recommendation', fn ($q) => $q->where('institution_id', $assessment->institution_id))
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->count(),
                'updated_at' => now(),
            ]
        );
    }
}
