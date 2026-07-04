<?php

namespace Tests\Unit;

use App\Console\Commands\ImportScoringRubrics;
use App\Models\AssessmentTemplate;
use App\Services\RubricMarkdownParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportScoringRubricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_programme_rubric_import_maps_to_programme_design_section(): void
    {
        $this->seed(\Database\Seeders\HeqamisSeeder::class);
        $this->artisan('heqamis:import-tools')->assertSuccessful();

        $path = base_path('Content bank/scoring/Programme accreditation Scoring Rubric  June 2026 (2).md');
        if (! is_readable($path)) {
            $this->markTestSkipped('Programme rubric file not available.');
        }

        $sections = app(RubricMarkdownParser::class)->parse($path);
        $this->assertNotEmpty($sections);

        $this->artisan('heqamis:import-rubrics')->assertSuccessful();

        $template = AssessmentTemplate::where('type', 'programme')->first();
        $section = $template->sections()->where('code', 'P-AREA-1')->first();

        $withRubrics = $section->criteria()
            ->whereHas('rubricLevels')
            ->count();

        $this->assertGreaterThan(0, $withRubrics);
    }
}
