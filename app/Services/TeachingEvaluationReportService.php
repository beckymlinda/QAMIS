<?php

namespace App\Services;

use App\Models\CourseOffering;
use App\Models\EvaluationPeriod;
use App\Models\EvaluationQuestionCategory;
use App\Models\Programme;
use App\Models\TeachingEvaluation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TeachingEvaluationReportService
{
    public const LIKERT_LABELS = [
        1 => 'Strongly Disagree',
        2 => 'Disagree',
        3 => 'Neutral',
        4 => 'Agree',
        5 => 'Strongly Agree',
    ];

    public function buildPeriodReport(Programme $programme, EvaluationPeriod $period): array
    {
        abort_unless($period->institution_id === $programme->institution_id, 404);

        $programme->load('institution');
        $categories = EvaluationQuestionCategory::query()
            ->with('questions')
            ->orderBy('sort_order')
            ->get();

        $offeringIds = CourseOffering::query()
            ->whereHas('course', fn ($q) => $q->where('programme_id', $programme->id))
            ->pluck('id');

        $evaluations = TeachingEvaluation::query()
            ->where('evaluation_period_id', $period->id)
            ->where('status', 'submitted')
            ->whereIn('course_offering_id', $offeringIds)
            ->with([
                'responses.question.category',
                'courseOffering.course',
                'courseOffering.lecturer',
            ])
            ->get();

        $responsesByQuestion = $evaluations
            ->flatMap(fn (TeachingEvaluation $evaluation) => $evaluation->responses)
            ->groupBy('evaluation_question_id');

        $offerings = CourseOffering::query()
            ->whereIn('id', $offeringIds)
            ->with(['course', 'lecturer'])
            ->get()
            ->sortBy(fn (CourseOffering $offering) => $offering->course?->code ?? '')
            ->values()
            ->map(function (CourseOffering $offering) use ($evaluations, $categories) {
                $offeringEvaluations = $evaluations->where('course_offering_id', $offering->id);
                $sections = $this->buildOfferingSections($offeringEvaluations, $categories);

                return [
                    'offering' => $offering,
                    'course_code' => $offering->course?->code ?? '—',
                    'course_title' => $offering->course?->title ?? '—',
                    'lecturer_name' => $offering->lecturer?->name ?? '—',
                    'response_count' => $offeringEvaluations->count(),
                    'sections' => $sections,
                    'course_average' => $this->averageSectionGroups($sections['course']),
                    'lecturer_average' => $this->averageSectionGroups($sections['lecturer']),
                ];
            });

        return [
            'programme' => $programme,
            'institution' => $programme->institution,
            'period' => $period,
            'generated_at' => now(),
            'total_submissions' => $evaluations->count(),
            'likert_labels' => self::LIKERT_LABELS,
            'programme_sections' => $this->buildAggregateSections($responsesByQuestion, $categories),
            'offerings' => $offerings,
        ];
    }

    public function downloadPdf(Programme $programme, EvaluationPeriod $period): Response
    {
        $report = $this->buildPeriodReport($programme, $period);
        $filename = Str::slug($programme->name.'-evaluation-'.$period->academic_year.'-sem-'.$period->semester).'.pdf';

        return Pdf::loadView('reports.teaching-evaluation', $report)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    /**
     * @param  Collection<int, TeachingEvaluation>  $evaluations
     * @return array{course: list<array<string, mixed>>, lecturer: list<array<string, mixed>>, open: list<array<string, mixed>>}
     */
    protected function buildOfferingSections(Collection $evaluations, Collection $categories): array
    {
        $responses = $evaluations->flatMap(fn (TeachingEvaluation $evaluation) => $evaluation->responses);

        return [
            'course' => $this->buildSectionGroups($categories->where('section', 'course'), $responses),
            'lecturer' => $this->buildSectionGroups($categories->where('section', 'lecturer'), $responses),
            'open' => $this->buildOpenSection($categories->where('section', 'open'), $responses),
        ];
    }

    /**
     * @param  Collection<int, \App\Models\TeachingEvaluationResponse>  $responsesByQuestion
     * @return array{course: list<array<string, mixed>>, lecturer: list<array<string, mixed>>, open: list<array<string, mixed>>}
     */
    protected function buildAggregateSections(Collection $responsesByQuestion, Collection $categories): array
    {
        $responses = $responsesByQuestion->flatMap(fn (Collection $items) => $items);

        return [
            'course' => $this->buildSectionGroups($categories->where('section', 'course'), $responses),
            'lecturer' => $this->buildSectionGroups($categories->where('section', 'lecturer'), $responses),
            'open' => $this->buildOpenSection($categories->where('section', 'open'), $responses),
        ];
    }

    /**
     * @param  Collection<int, EvaluationQuestionCategory>  $categories
     * @param  Collection<int, \App\Models\TeachingEvaluationResponse>  $responses
     * @return list<array<string, mixed>>
     */
    protected function buildSectionGroups(Collection $categories, Collection $responses): array
    {
        $groups = [];

        foreach ($categories as $category) {
            $questionRows = [];

            foreach ($category->questions as $question) {
                $questionResponses = $responses->where('evaluation_question_id', $question->id);
                $average = $this->averageRating($questionResponses);

                $questionRows[] = [
                    'text' => $question->question_text,
                    'average' => $average,
                    'count' => $questionResponses->whereNotNull('rating')->count(),
                    'distribution' => $this->ratingDistribution($questionResponses),
                ];
            }

            $categoryAverage = $this->averageRating(
                $responses->whereIn('evaluation_question_id', $category->questions->pluck('id'))
            );

            $groups[] = [
                'title' => $category->title,
                'questions' => $questionRows,
                'average' => $categoryAverage,
            ];
        }

        return $groups;
    }

    /**
     * @param  Collection<int, EvaluationQuestionCategory>  $categories
     * @param  Collection<int, \App\Models\TeachingEvaluationResponse>  $responses
     * @return list<array<string, mixed>>
     */
    protected function buildOpenSection(Collection $categories, Collection $responses): array
    {
        $items = [];

        foreach ($categories as $category) {
            foreach ($category->questions as $question) {
                $comments = $responses
                    ->where('evaluation_question_id', $question->id)
                    ->pluck('response_text')
                    ->filter(fn (?string $text) => filled(trim((string) $text)))
                    ->values()
                    ->all();

                $items[] = [
                    'question' => $question->question_text,
                    'comments' => $comments,
                ];
            }
        }

        return $items;
    }

    protected function averageRating(Collection $responses): ?float
    {
        $ratings = $responses->pluck('rating')->filter(fn ($rating) => $rating !== null);

        if ($ratings->isEmpty()) {
            return null;
        }

        return round((float) $ratings->avg(), 2);
    }

    /**
     * @return array<int, int>
     */
    protected function ratingDistribution(Collection $responses): array
    {
        $distribution = array_fill(1, 5, 0);

        foreach ($responses as $response) {
            if ($response->rating >= 1 && $response->rating <= 5) {
                $distribution[(int) $response->rating]++;
            }
        }

        return $distribution;
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     */
    protected function averageSectionGroups(array $groups): ?float
    {
        $averages = collect($groups)->pluck('average')->filter(fn ($value) => $value !== null);

        if ($averages->isEmpty()) {
            return null;
        }

        return round((float) $averages->avg(), 2);
    }
}
