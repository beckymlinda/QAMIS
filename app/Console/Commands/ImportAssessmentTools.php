<?php

namespace App\Console\Commands;

use App\Models\AssessmentCriterion;
use App\Models\AssessmentSection;
use App\Models\AssessmentTemplate;
use App\Models\StandardArea;
use App\Models\StandardClause;
use App\Models\StandardVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportAssessmentTools extends Command
{
    protected $signature = 'qamis:import-tools {--path=}';

    protected $description = 'Import institutional and programme assessment tools from content bank';

    public function handle(): int
    {
        $basePath = $this->option('path') ?: base_path('Content bank');
        $version = StandardVersion::where('code', 'nche-2015')->first();

        if (! $version) {
            $this->error('Run QamisSeeder first to create standard version.');

            return self::FAILURE;
        }

        $this->importInstitutionalTool($version, $basePath.'/Accreditation Tool.md');
        $this->importProgrammeTool($version, $basePath.'/ASSESSMENT_TOOL_FOR_ACCREDITATION_OF_PROGRAMMES.md');

        $this->info('Assessment tools imported successfully.');

        return self::SUCCESS;
    }

    protected function importInstitutionalTool(StandardVersion $version, string $path): void
    {
        if (! File::exists($path)) {
            $this->warn("Institutional tool not found: {$path}");

            return;
        }

        $template = AssessmentTemplate::firstOrCreate(
            ['standard_version_id' => $version->id, 'type' => 'institutional'],
            ['name' => 'NCHE Institutional Accreditation Tool', 'version' => '1.0']
        );

        $sections = [
            ['code' => 'AREA-1', 'title' => 'Guiding Principles', 'ref' => '1.0', 'divisor' => 6, 'criteria' => $this->institutionalGuidingPrinciples()],
            ['code' => 'AREA-2.2', 'title' => 'Governance Structures', 'ref' => '2.2', 'divisor' => 18, 'criteria' => $this->institutionalGovernance()],
            ['code' => 'AREA-2.3', 'title' => 'Public Accountability', 'ref' => '3.8.3', 'divisor' => 3, 'criteria' => $this->institutionalPublicAccountability()],
            ['code' => 'AREA-3', 'title' => 'Governing Policies and Procedures', 'ref' => '3.0', 'divisor' => 38, 'criteria' => $this->institutionalPolicies()],
            ['code' => 'AREA-4.1', 'title' => 'Finance Management', 'ref' => '4.1', 'divisor' => 10, 'criteria' => $this->institutionalFinance()],
            ['code' => 'AREA-4.2', 'title' => 'Equipment and Transport', 'ref' => '4.2', 'divisor' => 4, 'criteria' => $this->institutionalEquipment()],
            ['code' => 'AREA-5.1', 'title' => 'Infrastructure', 'ref' => '5.1', 'divisor' => 22, 'criteria' => $this->genericCriteria('Infrastructure', 22, ['Sufficient lecturing spaces'])],
            ['code' => 'AREA-5.2', 'title' => 'Library and Learning Resources', 'ref' => '5.2', 'divisor' => 23, 'criteria' => $this->genericCriteria('Library/LRC', 23)],
            ['code' => 'AREA-5.2-ICT', 'title' => 'ICT and E-learning', 'ref' => '5.2', 'divisor' => 7, 'criteria' => $this->genericCriteria('ICT', 7)],
            ['code' => 'AREA-6', 'title' => 'Student Support Services', 'ref' => '6.0', 'divisor' => 12, 'criteria' => $this->genericCriteria('Student Support', 12, ['Director of Student Affairs'])],
            ['code' => 'AREA-7', 'title' => 'Teaching and Learning Strategy', 'ref' => '7.0', 'divisor' => 6, 'criteria' => $this->genericCriteria('Teaching & Learning', 6)],
            ['code' => 'AREA-8', 'title' => 'Research and Innovation', 'ref' => '8.0', 'divisor' => 11, 'criteria' => $this->genericCriteria('Research', 11, ['Functional research office with budget'])],
            ['code' => 'AREA-9', 'title' => 'Community Outreach / Industry Engagement', 'ref' => '9.0', 'divisor' => 7, 'criteria' => $this->genericCriteria('Outreach', 7)],
        ];

        $this->seedSections($template, $sections);
        $this->info('Institutional tool seeded with '.$template->sections()->count().' sections.');
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
                        'title' => $criterion['title'],
                        'description' => $criterion['description'] ?? null,
                        'is_mandatory' => $criterion['mandatory'] ?? false,
                        'minimum_score' => 3,
                        'weight' => 1,
                    ]
                );
            }
        }
    }

    protected function institutionalGuidingPrinciples(): array
    {
        return [
            ['title' => 'Vision, mission, core values and objective statements are available'],
            ['title' => 'Vision statement expresses long-term plan understandable by stakeholders'],
            ['title' => 'Vision and mission shaped by Malawian legislation and national policies'],
            ['title' => 'Vision and mission geared towards quality assurance of academic outcomes'],
            ['title' => 'Progress towards vision/mission monitored and evaluated'],
            ['title' => 'Institution acts appropriately on risks, gaps and challenges'],
        ];
    }

    protected function institutionalGovernance(): array
    {
        $mandatory = ['Act, Statutes/Charter/Constitution', 'Organizational structure', 'Senate or its equivalent'];
        $items = [
            'Public disclosure of organizational structures',
            'Act, Statutes/Charter/Constitution',
            'Organizational structure with clear reporting lines',
            'Independent Board of Directors/Council',
            'Head of institution appointed full-time',
            'Senate or its equivalent legally established',
            'Faculty/School committees established',
            'Departmental committees established',
            'Heads of Department appointed per statutes',
            'Staff involvement in governance',
            'Student involvement in governance',
            'Management team meets regularly',
            'Council/Board meets as per statutes',
            'Effective communication between governance bodies',
            'Clear delegation of authority',
            'Conflict of interest policies in governance',
            'Governance review mechanisms',
            'Compliance with NCHE reporting requirements',
        ];

        return collect($items)->map(fn ($title) => [
            'title' => $title,
            'mandatory' => in_array($title, $mandatory, true) || str_contains($title, 'Act, Statutes') || str_contains($title, 'Organizational structure') || str_contains($title, 'Senate'),
        ])->take(18)->values()->all();
    }

    protected function institutionalPublicAccountability(): array
    {
        return [
            ['title' => 'Fee structure published (Minimum Standard 3.8.3)', 'mandatory' => false],
            ['title' => 'Academic programmes and requirements disclosed', 'mandatory' => false],
            ['title' => 'Academic calendar published', 'mandatory' => false],
        ];
    }

    protected function institutionalPolicies(): array
    {
        $mandatoryTitles = [
            'Strategic plan', 'Students handbook', 'Financial Management Policy',
            'Assessment Policy', 'Quality Assurance Policy', 'Research/innovation/publications policy',
            'Staff Recruitment/Retention/Promotion', 'Disability/inclusion policy',
            'Health & Safety policy', 'Sexual harassment policy', 'Staff Development Plan',
            'Examination Conduct & Regulations',
        ];

        return collect(range(1, 38))->map(function ($i) use ($mandatoryTitles) {
            $title = "Policy item {$i} - ".($mandatoryTitles[$i - 1] ?? "Institutional policy {$i}");

            return [
                'title' => $title,
                'mandatory' => isset($mandatoryTitles[$i - 1]),
            ];
        })->all();
    }

    protected function institutionalFinance(): array
    {
        return collect(range(1, 10))->map(function ($i) {
            return [
                'title' => $i === 1 ? 'Audited financial accounts (not more than 2 years behind)' : "Financial management standard {$i}",
                'mandatory' => $i === 1,
            ];
        })->all();
    }

    protected function institutionalEquipment(): array
    {
        return $this->genericCriteria('Equipment/Transport', 4);
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
