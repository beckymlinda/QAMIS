<?php

namespace App\Console\Commands;

use App\Models\AssessmentCriterion;
use App\Models\AssessmentCriterionRubricLevel;
use App\Models\AssessmentSection;
use App\Models\AssessmentTemplate;
use App\Models\ScoringRubric;
use App\Models\StandardVersion;
use App\Services\RubricMarkdownParser;
use App\Support\DefaultScoringRubric;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportScoringRubrics extends Command
{
    protected $signature = 'heqamis:import-rubrics {--path=}';

    protected $description = 'Import per-criterion scoring rubrics from Content bank/scoring markdown files';

    /** @var array<string, string> */
    protected array $institutionalSectionMap = [
        'AREA 1: GUIDING PRINCIPLES' => 'AREA-1',
        '1. Rating on Availability and Implementation of Guiding Principles' => 'AREA-1',
        '2.1 Rating on Governance and Management' => 'AREA-2.2',
        '2.3 Rating on Availability and Implementation of Public Accountability' => 'AREA-2.3',
        '3.1 Rating on Availability and Implementation of Governing Policies and Procedures' => 'AREA-3',
        '4.1 Rating on Availability and Implementation of Management of Finance' => 'AREA-4.1',
        '4.2 Rating on Availability and Implementation of Institutional Equipment and Transport' => 'AREA-4.2',
        '6.1 Rating on Availability and Implementation of Student Support Services' => 'AREA-6',
        '7.1 Rating on Availability and Implementation of Teaching and Learning Strategy' => 'AREA-7',
        'AREA 7: TEACHING & LEARNING STRATEGY' => 'AREA-7',
        '8.1 Rating on Availability and Implementation of Research & Innovation' => 'AREA-8',
        '9.1 Rating on Availability and Implementation of Community Outreach and Industry Engagement' => 'AREA-9',
    ];

    /** @var array<string, string> */
    protected array $programmeSectionMap = [
        'AREA 1: PROGRAMME DESIGN' => 'P-AREA-1',
        'PROGRAMME DESIGN' => 'P-AREA-1',
    ];

    public function handle(RubricMarkdownParser $parser): int
    {
        $basePath = $this->option('path') ?: base_path('Content bank/scoring');
        $version = StandardVersion::where('code', 'nche-2015')->first();

        if (! $version) {
            $this->error('Run database seeders first to create the standard version.');

            return self::FAILURE;
        }

        $this->seedGlobalRubricLabels($version);

        $institutionalFile = $this->resolveFile($basePath, [
            'Scoring rubric  for Accreditation of Instituions June 2026.md',
            'Scoring rubric for Accreditation of Instituions June 2026.md',
        ]);

        $programmeFile = $this->resolveFile($basePath, [
            'Programme accreditation Scoring Rubric  June 2026 (2).md',
            'Programme accreditation Scoring Rubric June 2026 (2).md',
        ]);

        $institutionalTemplate = AssessmentTemplate::where('type', 'institutional')->first();
        $programmeTemplate = AssessmentTemplate::where('type', 'programme')->first();

        if ($institutionalFile && $institutionalTemplate) {
            $sections = $parser->parse($institutionalFile);
            $count = $this->importForTemplate($institutionalTemplate, $sections, $this->institutionalSectionMap);
            $this->info("Institutional rubrics imported for {$count} criteria.");
        } else {
            $this->warn('Institutional rubric file or template not found.');
        }

        if ($programmeFile && $programmeTemplate) {
            $sections = $parser->parse($programmeFile);
            $count = $this->importForTemplate($programmeTemplate, $sections, $this->programmeSectionMap);
            $this->info("Programme rubrics imported for {$count} criteria.");
        } else {
            $this->warn('Programme rubric file or template not found.');
        }

        $this->applyDefaultRubricsToRemainingCriteria();

        $this->info('Scoring rubric import complete.');

        return self::SUCCESS;
    }

    protected function seedGlobalRubricLabels(StandardVersion $version): void
    {
        foreach (DefaultScoringRubric::levels() as $score => $level) {
            ScoringRubric::updateOrCreate(
                ['standard_version_id' => $version->id, 'score' => $score],
                ['label' => $level['label'], 'description' => $level['descriptor']]
            );
        }
    }

    /**
     * @param  array<int, array{title: string, criteria: array<int, array{sequence: int, title: string, levels: array<int, array{label: string, descriptor: string}>}>}>  $parsedSections
     * @param  array<string, string>  $sectionMap
     */
    protected function importForTemplate(AssessmentTemplate $template, array $parsedSections, array $sectionMap): int
    {
        $imported = 0;

        foreach ($parsedSections as $parsedSection) {
            $sectionCode = $this->resolveSectionCode($parsedSection['title'], $sectionMap);
            if (! $sectionCode) {
                continue;
            }

            $section = AssessmentSection::query()
                ->where('assessment_template_id', $template->id)
                ->where('code', $sectionCode)
                ->first();

            if (! $section) {
                continue;
            }

            $criteria = AssessmentCriterion::query()
                ->where('assessment_section_id', $section->id)
                ->orderBy('sequence_no')
                ->get();

            foreach ($parsedSection['criteria'] as $sequence => $rubricCriterion) {
                $criterion = $criteria->firstWhere('sequence_no', $sequence)
                    ?? $this->matchCriterionByTitle($criteria, $rubricCriterion['title']);

                if (! $criterion) {
                    continue;
                }

                $imported += $this->storeRubricLevels($criterion, $rubricCriterion['levels']);
            }
        }

        return $imported;
    }

    protected function resolveSectionCode(string $title, array $sectionMap): ?string
    {
        $normalizedTitle = Str::lower($title);

        foreach ($sectionMap as $needle => $code) {
            if (Str::contains($normalizedTitle, Str::lower($needle))) {
                return $code;
            }
        }

        return null;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, AssessmentCriterion>  $criteria
     */
    protected function matchCriterionByTitle($criteria, string $rubricTitle): ?AssessmentCriterion
    {
        $needle = Str::lower($this->normalizeForMatch($rubricTitle));

        return $criteria->first(function (AssessmentCriterion $criterion) use ($needle) {
            $haystack = Str::lower($this->normalizeForMatch($criterion->title));

            return Str::contains($haystack, $needle) || Str::contains($needle, $haystack);
        });
    }

    protected function normalizeForMatch(string $text): string
    {
        $text = preg_replace('/\*\*/', '', $text) ?? $text;
        $text = preg_replace('/[^a-z0-9\s]/i', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    /**
     * @param  array<int, array{label: string, descriptor: string}>  $levels
     */
    protected function storeRubricLevels(AssessmentCriterion $criterion, array $levels): int
    {
        $stored = 0;

        foreach ($levels as $score => $level) {
            AssessmentCriterionRubricLevel::updateOrCreate(
                [
                    'assessment_criterion_id' => $criterion->id,
                    'score' => $score,
                ],
                [
                    'level_label' => $level['label'],
                    'descriptor' => $level['descriptor'],
                ]
            );
            $stored++;
        }

        return $stored > 0 ? 1 : 0;
    }

    protected function applyDefaultRubricsToRemainingCriteria(): void
    {
        $criteriaWithoutRubrics = AssessmentCriterion::query()
            ->whereDoesntHave('rubricLevels')
            ->get();

        foreach ($criteriaWithoutRubrics as $criterion) {
            $this->storeRubricLevels($criterion, DefaultScoringRubric::levels());
        }

        $this->info('Default rubrics applied to '.$criteriaWithoutRubrics->count().' criteria without specific rubrics.');
    }

    /**
     * @param  array<int, string>  $candidates
     */
    protected function resolveFile(string $basePath, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $path = $basePath.DIRECTORY_SEPARATOR.$candidate;
            if (is_readable($path)) {
                return $path;
            }
        }

        if (is_dir($basePath)) {
            foreach (scandir($basePath) ?: [] as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $path = $basePath.DIRECTORY_SEPARATOR.$file;
                if (! is_readable($path)) {
                    continue;
                }

                foreach ($candidates as $candidate) {
                    if (Str::contains(Str::lower($file), Str::lower(pathinfo($candidate, PATHINFO_FILENAME)))) {
                        return $path;
                    }
                }
            }
        }

        return null;
    }
}
