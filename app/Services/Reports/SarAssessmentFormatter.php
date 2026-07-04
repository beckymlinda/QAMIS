<?php

namespace App\Services\Reports;

use App\Models\Assessment;
use App\Models\AssessmentResponse;
use App\Support\AccreditationScoreInterpreter;

class SarAssessmentFormatter
{
    public function formatAssessment(Assessment $assessment, bool $isProgramme = false): array
    {
        $assessment->loadMissing([
            'complianceResult',
            'sectionSummaries.section',
            'responses.criterion',
            'programme',
        ]);

        $sectionRows = $this->buildSectionScoreRows($assessment, $isProgramme);
        $overallAverage = (float) ($assessment->complianceResult?->overall_average ?? 0);
        $overallCriticalFailed = collect($sectionRows)->contains(fn ($row) => str_contains($row['critical_comment'], 'failed'));

        return [
            'id' => $assessment->id,
            'title' => $assessment->title,
            'assessment_type' => $assessment->assessment_type,
            'programme' => $assessment->programme?->toArray(),
            'compliance_result' => $assessment->complianceResult?->toArray(),
            'section_summaries' => $assessment->sectionSummaries->toArray(),
            'section_score_rows' => $sectionRows,
            'narrative_recommendations' => $assessment->narrative_recommendations ?? [],
            'overall_average' => $overallAverage,
            'overall_recommendation' => AccreditationScoreInterpreter::scoreRecommendation(
                $overallAverage,
                $overallCriticalFailed,
                $isProgramme
            ),
            'overall_band' => AccreditationScoreInterpreter::bandLabel($overallAverage),
            'overall_outcome' => AccreditationScoreInterpreter::bandOutcome($overallAverage),
        ];
    }

    public function buildStrengthsImprovementRows(Assessment $assessment): array
    {
        $assessment->loadMissing(['sectionSummaries.section', 'responses.criterion.section']);

        $rows = [];
        $sn = 1;

        foreach ($assessment->sectionSummaries->sortBy(fn ($s) => $s->section?->sort_order ?? 0) as $summary) {
            $sectionResponses = $assessment->responses
                ->filter(fn ($r) => $r->criterion?->assessment_section_id === $summary->assessment_section_id);

            $strengths = $sectionResponses
                ->filter(fn ($r) => $r->score !== null && $r->score >= 3)
                ->map(fn ($r) => $this->reviewerCommentForReport($r))
                ->filter()
                ->values()
                ->all();

            $improvements = $sectionResponses
                ->filter(fn ($r) => $r->score !== null && $r->score <= 2)
                ->map(fn ($r) => $this->reviewerCommentForReport($r))
                ->filter()
                ->values()
                ->all();

            if ($strengths === [] && $improvements === []) {
                continue;
            }

            $rows[] = [
                'sn' => $sn++,
                'area' => $summary->section?->title ?? 'Section',
                'strengths' => $strengths,
                'improvements' => $improvements,
            ];
        }

        return $rows;
    }

    protected function reviewerCommentForReport(AssessmentResponse $response): ?string
    {
        foreach (['comments', 'reviewer_observations'] as $field) {
            $text = trim((string) ($response->{$field} ?? ''));

            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    protected function buildSectionScoreRows(Assessment $assessment, bool $isProgramme): array
    {
        $responsesBySection = $assessment->responses
            ->groupBy(fn ($r) => $r->criterion?->assessment_section_id);

        $rows = [];
        $sn = 1;

        foreach ($assessment->sectionSummaries->sortBy(fn ($s) => $s->section?->sort_order ?? 0) as $summary) {
            $sectionResponses = $responsesBySection->get($summary->assessment_section_id, collect());
            $mandatory = $sectionResponses->filter(fn ($r) => $r->criterion?->is_mandatory);
            $criticalFailed = $mandatory->contains(fn ($r) => $r->score === null || $r->score < ($r->criterion->minimum_score ?? 3));

            $aggregate = (float) $summary->aggregate_score;

            $rows[] = [
                'sn' => $sn++,
                'area' => $summary->section?->title ?? 'Section',
                'assessment_areas' => $summary->total_score,
                'critical_comment' => AccreditationScoreInterpreter::criticalAreasComment(
                    $mandatory->isNotEmpty(),
                    $criticalFailed
                ),
                'aggregate_score' => number_format($aggregate, 2),
                'recommendation' => AccreditationScoreInterpreter::scoreRecommendation(
                    $aggregate,
                    $criticalFailed,
                    $isProgramme
                ),
            ];
        }

        return $rows;
    }
}
