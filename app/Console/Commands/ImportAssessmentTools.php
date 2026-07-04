<?php

namespace App\Console\Commands;

use App\Models\AssessmentCriterion;
use App\Models\AssessmentSection;
use App\Models\AssessmentTemplate;
use App\Models\StandardVersion;
use App\Services\AccreditationToolParser;
use App\Services\RubricMarkdownParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportAssessmentTools extends Command
{
    protected $signature = 'heqamis:import-tools {--path=}';

    protected $description = 'Import institutional and programme assessment tools from content bank';

    /** @var array<string, string> */
    protected array $institutionalRubricSectionMap = [
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

    public function handle(RubricMarkdownParser $rubricParser, AccreditationToolParser $toolParser): int
    {
        $basePath = $this->option('path') ?: base_path('Content bank');
        $version = StandardVersion::where('code', 'nche-2015')->first();

        if (! $version) {
            $this->error('Run HeqamisSeeder first to create standard version.');

            return self::FAILURE;
        }

        $this->importInstitutionalTool($version, $basePath, $rubricParser, $toolParser);
        $this->importProgrammeTool($version, $basePath.'/ASSESSMENT_TOOL_FOR_ACCREDITATION_OF_PROGRAMMES.md');

        $this->info('Assessment tools imported successfully.');

        return self::SUCCESS;
    }

    protected function importInstitutionalTool(
        StandardVersion $version,
        string $basePath,
        RubricMarkdownParser $rubricParser,
        AccreditationToolParser $toolParser,
    ): void {
        $template = AssessmentTemplate::firstOrCreate(
            ['standard_version_id' => $version->id, 'type' => 'institutional'],
            ['name' => 'NCHE Institutional Accreditation Tool', 'version' => '1.0']
        );

        $rubricSections = $this->indexedRubricSections($rubricParser, $basePath.'/scoring');
        $toolSections = $toolParser->parse($basePath.'/Accreditation Tool.md');

        $sections = [
            ['code' => 'AREA-1', 'title' => 'Guiding Principles', 'ref' => '1.0', 'source' => 'rubric', 'rubric_key' => '1. Rating on Availability and Implementation of Guiding Principles'],
            ['code' => 'AREA-2.2', 'title' => 'Governance Structures', 'ref' => '2.2', 'source' => 'rubric', 'rubric_key' => '2.1 Rating on Governance and Management'],
            ['code' => 'AREA-2.3', 'title' => 'Public Accountability', 'ref' => '3.8.3', 'source' => 'rubric', 'rubric_key' => '2.3 Rating on Availability and Implementation of Public Accountability'],
            ['code' => 'AREA-3', 'title' => 'Governing Policies and Procedures', 'ref' => '3.0', 'source' => 'rubric', 'rubric_key' => '3.1 Rating on Availability and Implementation of Governing Policies and Procedures'],
            ['code' => 'AREA-4.1', 'title' => 'Finance Management', 'ref' => '4.1', 'source' => 'rubric_tool', 'rubric_key' => '4.1 Rating on Availability and Implementation of Management of Finance', 'tool_code' => 'AREA-4.1'],
            ['code' => 'AREA-4.2', 'title' => 'Equipment and Transport', 'ref' => '4.2', 'source' => 'rubric_tool', 'rubric_key' => '4.2 Rating on Availability and Implementation of Institutional Equipment and Transport', 'tool_code' => 'AREA-4.2'],
            ['code' => 'AREA-5.1', 'title' => 'Infrastructure', 'ref' => '5.1', 'source' => 'tool', 'tool_code' => 'AREA-5.1'],
            ['code' => 'AREA-5.2', 'title' => 'Library and Learning Resources', 'ref' => '5.2', 'source' => 'tool', 'tool_code' => 'AREA-5.2'],
            ['code' => 'AREA-5.2-ICT', 'title' => 'ICT and E-learning', 'ref' => '5.2', 'source' => 'tool', 'tool_code' => 'AREA-5.2-ICT'],
            ['code' => 'AREA-WATSAN', 'title' => 'Water and Sanitation Facilities', 'ref' => '6.0', 'source' => 'watsan'],
            ['code' => 'AREA-CLASSROOMS_LABS', 'title' => 'Classroom and Laboratory Space', 'ref' => '5.1', 'source' => 'classrooms'],
            ['code' => 'AREA-QAS', 'title' => 'Quality Assurance Systems', 'ref' => '13.0', 'source' => 'qas'],
            ['code' => 'AREA-6', 'title' => 'Student Support Services', 'ref' => '6.0', 'source' => 'rubric_tool', 'rubric_key' => '6.1 Rating on Availability and Implementation of Student Support Services', 'tool_code' => 'AREA-6'],
            ['code' => 'AREA-7', 'title' => 'Teaching and Learning Strategy', 'ref' => '7.0', 'source' => 'rubric_tool', 'rubric_key' => '7.1 Rating on Availability and Implementation of Teaching and Learning Strategy', 'tool_code' => 'AREA-7'],
            ['code' => 'AREA-8', 'title' => 'Research and Innovation', 'ref' => '8.0', 'source' => 'rubric_tool', 'rubric_key' => '8.1 Rating on Availability and Implementation of Research & Innovation', 'tool_code' => 'AREA-8'],
            ['code' => 'AREA-9', 'title' => 'Community Outreach / Industry Engagement', 'ref' => '9.0', 'source' => 'rubric_tool', 'rubric_key' => '9.1 Rating on Availability and Implementation of Community Outreach and Industry Engagement', 'tool_code' => 'AREA-9'],
        ];

        foreach ($sections as $index => &$sectionData) {
            $sectionData['criteria'] = $this->resolveCriteria($sectionData, $rubricSections, $toolSections, $toolParser);
            $sectionData['divisor'] = max(1, count($sectionData['criteria']));
            unset($sectionData['source'], $sectionData['rubric_key'], $sectionData['tool_code']);
        }
        unset($sectionData);

        $this->seedSections($template, $sections);
        $this->info('Institutional tool seeded with '.$template->sections()->count().' sections.');
    }

    /**
     * @param  array<string, array{title: string, criteria: array<int, array{sequence: int, title: string, levels: array<int, array{label: string, descriptor: string}>}>}>  $rubricSections
     * @param  array<string, array<int, array{title: string, mandatory: bool}>>  $toolSections
     * @param  array{code: string, title: string, ref: string, source: string, rubric_key?: string, tool_code?: string}  $sectionData
     * @return array<int, array{title: string, mandatory: bool}>
     */
    protected function resolveCriteria(
        array $sectionData,
        array $rubricSections,
        array $toolSections,
        AccreditationToolParser $toolParser,
    ): array {
        $fromRubric = isset($sectionData['rubric_key'])
            ? $this->criteriaFromRubricSection($rubricSections[$sectionData['code']] ?? null)
            : [];

        $fromTool = isset($sectionData['tool_code'])
            ? ($toolSections[$sectionData['tool_code']] ?? [])
            : [];

        $criteria = match ($sectionData['source']) {
            'rubric' => $fromRubric,
            'tool' => $fromTool,
            'rubric_tool' => $fromRubric !== [] ? $fromRubric : $fromTool,
            'watsan' => $toolParser->waterAndSanitationCriteria(),
            'classrooms' => $toolParser->classroomAndLaboratoryCriteria(),
            'qas' => $toolParser->qualityAssuranceCriteria(),
            default => [],
        };

        if ($criteria === []) {
            $this->warn("No criteria resolved for {$sectionData['code']} ({$sectionData['title']}).");
        }

        return $criteria;
    }

    /**
     * @param  array{title: string, criteria: array<int, array{sequence: int, title: string}>}|null  $section
     * @return array<int, array{title: string, mandatory: bool}>
     */
    protected function criteriaFromRubricSection(?array $section): array
    {
        if (! $section) {
            return [];
        }

        return collect($section['criteria'])
            ->sortBy('sequence')
            ->values()
            ->map(fn (array $criterion) => [
                'title' => $criterion['title'],
                'mandatory' => str_contains($criterion['title'], '*'),
            ])
            ->all();
    }

    /**
     * @return array<string, array{title: string, criteria: array<int, array{sequence: int, title: string, levels: array<int, array{label: string, descriptor: string}>}>}>
     */
    protected function indexedRubricSections(RubricMarkdownParser $parser, string $scoringPath): array
    {
        $file = $this->resolveRubricFile($scoringPath);
        if (! $file) {
            $this->warn('Institutional scoring rubric file not found.');

            return [];
        }

        $indexed = [];

        foreach ($parser->parse($file) as $section) {
            $code = $this->resolveRubricSectionCode($section['title']);
            if ($code) {
                $indexed[$code] = $section;
            }
        }

        return $indexed;
    }

    protected function resolveRubricSectionCode(string $title): ?string
    {
        $normalized = Str::lower($title);

        foreach ($this->institutionalRubricSectionMap as $needle => $areaCode) {
            if (Str::contains($normalized, Str::lower($needle))) {
                return $areaCode;
            }
        }

        return null;
    }

    protected function resolveRubricFile(string $basePath): ?string
    {
        foreach ([
            'Scoring rubric  for Accreditation of Instituions June 2026.md',
            'Scoring rubric for Accreditation of Instituions June 2026.md',
        ] as $candidate) {
            $path = $basePath.DIRECTORY_SEPARATOR.$candidate;
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function importProgrammeTool(StandardVersion $version, string $path): void
    {
        if (! File::exists($path)) {
            $this->warn("Programme tool not found: {$path}");

            return;
        }

        $template = AssessmentTemplate::firstOrCreate(
            ['standard_version_id' => $version->id, 'type' => 'programme'],
            ['name' => 'NCHE Programme Accreditation Tool', 'version' => '1.0']
        );

        $sections = [
            ['code' => 'P-AREA-1', 'title' => 'Programme Design', 'ref' => '8.2', 'divisor' => 32, 'criteria' => $this->programmeDesign()],
            ['code' => 'P-AREA-2', 'title' => 'Delivery of Academic Programme', 'ref' => '8.3', 'divisor' => 12, 'criteria' => $this->genericCriteria('Programme Delivery', 12, ['Teaching staff have relevant qualifications'])],
            ['code' => 'P-AREA-3', 'title' => 'Staff Complement', 'ref' => '9.0', 'divisor' => 10, 'criteria' => $this->genericCriteria('Staff Complement', 10)],
            ['code' => 'P-AREA-5', 'title' => 'Academic Assessment', 'ref' => '11.0', 'divisor' => 15, 'criteria' => $this->genericCriteria('Academic Assessment', 15)],
            ['code' => 'P-AREA-6', 'title' => 'Resources', 'ref' => '5.0', 'divisor' => 5, 'criteria' => $this->genericCriteria('Resources', 5)],
            ['code' => 'P-AREA-7', 'title' => 'Quality Enhancement', 'ref' => '13.0', 'divisor' => 10, 'criteria' => $this->genericCriteria('Quality Enhancement', 10)],
            ['code' => 'P-AREA-8', 'title' => 'Internationalization', 'ref' => '8.0', 'divisor' => 5, 'criteria' => $this->genericCriteria('Internationalization', 5)],
        ];

        $this->seedSections($template, $sections);
        $this->info('Programme tool seeded with '.$template->sections()->count().' sections.');
    }

    protected function seedSections(AssessmentTemplate $template, array $sections): void
    {
        foreach ($sections as $index => $sectionData) {
            $section = AssessmentSection::updateOrCreate(
                ['assessment_template_id' => $template->id, 'code' => $sectionData['code']],
                [
                    'title' => $sectionData['title'],
                    'minimum_standard_ref' => $sectionData['ref'],
                    'divisor' => $sectionData['divisor'],
                    'sort_order' => $index + 1,
                ]
            );

            foreach ($sectionData['criteria'] as $seq => $criterion) {
                AssessmentCriterion::updateOrCreate(
                    ['assessment_section_id' => $section->id, 'sequence_no' => $seq + 1],
                    [
                        'title' => ltrim($criterion['title'], '* '),
                        'description' => $criterion['description'] ?? null,
                        'is_mandatory' => $criterion['mandatory'] ?? false,
                        'minimum_score' => 3,
                        'weight' => 1,
                    ]
                );
            }

            AssessmentCriterion::query()
                ->where('assessment_section_id', $section->id)
                ->where('sequence_no', '>', count($sectionData['criteria']))
                ->delete();
        }
    }

    protected function programmeDesign(): array
    {
        $items = [
            'Curriculum documents available and accessible',
            'Programme approved only on NCHE-approved locations',
            'Needs assessment documented',
            'Equity and access addressed',
            'Outcome-based approach applied',
            'Programme benchmarked nationally, regionally, internationally',
            'Exit awards/qualifications defined',
            'Alignment to NQF',
            'Internal and external QA processes',
            'Resources for programme delivery',
            'Programme implementation and monitoring',
            'Frequency of review and evaluation',
            'Programme aims consistent with institutional mission',
            'Stakeholder involvement in programme development',
            'Learning outcomes reflect discipline/industry demands',
            'Core, prerequisite, elective, audit courses specified',
            'Intended learning outcomes specified per course',
            'Course outlines meet NCHE template requirements',
        ];

        $mandatory = ['Programme benchmarked nationally, regionally, internationally', 'Stakeholder involvement in programme development', 'Learning outcomes reflect discipline/industry demands'];

        return collect($items)->map(fn ($title) => [
            'title' => $title,
            'mandatory' => in_array($title, $mandatory, true),
        ])->take(32)->values()->all();
    }

    /**
     * @param  array<int, string>  $mandatoryTitles
     * @return array<int, array{title: string, mandatory: bool}>
     */
    protected function genericCriteria(string $prefix, int $count, array $mandatoryTitles = []): array
    {
        return collect(range(1, $count))->map(function ($i) use ($prefix, $mandatoryTitles) {
            $title = count($mandatoryTitles) >= $i
                ? $mandatoryTitles[$i - 1]
                : "{$prefix} criterion {$i}";

            return [
                'title' => $title,
                'mandatory' => in_array($title, $mandatoryTitles, true),
            ];
        })->all();
    }
}
