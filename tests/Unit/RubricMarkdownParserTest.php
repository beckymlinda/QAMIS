<?php

namespace Tests\Unit;

use App\Services\RubricMarkdownParser;
use Tests\TestCase;

class RubricMarkdownParserTest extends TestCase
{
    public function test_it_parses_institutional_rubric_file(): void
    {
        $path = base_path('Content bank/scoring/Scoring rubric  for Accreditation of Instituions June 2026.md');
        if (! is_readable($path)) {
            $this->markTestSkipped('Institutional rubric file not available.');
        }

        $sections = app(RubricMarkdownParser::class)->parse($path);

        $this->assertNotEmpty($sections);
        $totalCriteria = collect($sections)->sum(fn ($section) => count($section['criteria']));
        $this->assertGreaterThan(50, $totalCriteria);
    }

    public function test_it_parses_programme_rubric_file(): void
    {
        $path = base_path('Content bank/scoring/Programme accreditation Scoring Rubric  June 2026 (2).md');
        if (! is_readable($path)) {
            $this->markTestSkipped('Programme rubric file not available.');
        }

        $sections = app(RubricMarkdownParser::class)->parse($path);

        $this->assertNotEmpty($sections);
        $totalCriteria = collect($sections)->sum(fn ($section) => count($section['criteria']));
        $this->assertGreaterThan(10, $totalCriteria);
    }
}
