<?php

namespace Database\Seeders;

use App\Models\EvidenceCategory;
use App\Models\ReportTemplate;
use App\Models\ScoringRubric;
use App\Models\StandardArea;
use App\Models\StandardVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class HeqamisSeeder extends Seeder
{
    public function run(): void
    {
        $version = StandardVersion::firstOrCreate(
            ['code' => 'nche-2015'],
            [
                'name' => 'NCHE Minimum Standards October 2015',
                'effective_from' => '2015-10-01',
                'is_active' => true,
            ]
        );

        $areas = [
            ['code' => '1', 'title' => 'Guiding Principles'],
            ['code' => '2', 'title' => 'Governance'],
            ['code' => '3', 'title' => 'Governing Policies and Procedures'],
            ['code' => '4', 'title' => 'Financial and Material Resources'],
            ['code' => '5', 'title' => 'Physical Facilities'],
            ['code' => '6', 'title' => 'Water and Sanitation'],
            ['code' => '7', 'title' => 'Student Support'],
            ['code' => '8', 'title' => 'Academic Programmes'],
            ['code' => '9', 'title' => 'Staff Complement'],
            ['code' => '10', 'title' => 'Admission and Recruitment'],
            ['code' => '11', 'title' => 'Academic Assessment'],
            ['code' => '12', 'title' => 'Degree Specification'],
            ['code' => '13', 'title' => 'Quality Enhancement'],
            ['code' => '14', 'title' => 'Registration and Accreditation'],
        ];

        foreach ($areas as $index => $area) {
            StandardArea::firstOrCreate(
                ['standard_version_id' => $version->id, 'code' => $area['code']],
                ['title' => $area['title'], 'sort_order' => $index + 1]
            );
        }

        foreach ([
            0 => 'Poor / Unavailable',
            1 => 'Insufficient',
            2 => 'Satisfactory',
            3 => 'Good',
            4 => 'Excellent',
        ] as $score => $label) {
            ScoringRubric::firstOrCreate(
                ['standard_version_id' => $version->id, 'score' => $score],
                ['label' => $label]
            );
        }

        $categories = [
            ['name' => 'Policy', 'slug' => 'policy'],
            ['name' => 'Financial Statement', 'slug' => 'financial'],
            ['name' => 'Curriculum', 'slug' => 'curriculum'],
            ['name' => 'Staff CV', 'slug' => 'cv'],
            ['name' => 'Certificate/Licence', 'slug' => 'certificate'],
            ['name' => 'Audit Report', 'slug' => 'audit'],
            ['name' => 'Photograph', 'slug' => 'photo'],
            ['name' => 'Minutes', 'slug' => 'minutes'],
            ['name' => 'Other', 'slug' => 'other'],
        ];

        foreach ($categories as $cat) {
            EvidenceCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        ReportTemplate::firstOrCreate(
            ['type' => 'sar'],
            [
                'name' => 'Self-Assessment Report',
                'version' => '1.0',
                'section_schema' => ['sections' => ['introduction', 'background', 'swot', 'institutional', 'programme', 'appendices']],
                'is_active' => true,
            ]
        );

        ReportTemplate::firstOrCreate(
            ['type' => 'annual'],
            [
                'name' => 'Annual Report / Institutional Audit',
                'version' => '1.0',
                'section_schema' => ['sections' => range(1, 23)],
                'is_active' => true,
            ]
        );

        Artisan::call('heqamis:import-tools');
        $this->command?->info(Artisan::output());
    }
}
