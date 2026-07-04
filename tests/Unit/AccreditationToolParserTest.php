<?php

namespace Tests\Unit;

use App\Services\AccreditationToolParser;
use App\Services\RubricMarkdownParser;
use Tests\TestCase;

class AccreditationToolParserTest extends TestCase
{
    public function test_it_parses_infrastructure_and_library_sections_from_accreditation_tool(): void
    {
        $path = base_path('Content bank/Accreditation Tool.md');
        if (! is_readable($path)) {
            $this->markTestSkipped('Accreditation tool file not available.');
        }

        $sections = app(AccreditationToolParser::class)->parse($path);

        $this->assertGreaterThanOrEqual(17, count($sections['AREA-5.1'] ?? []));
        $this->assertGreaterThanOrEqual(20, count($sections['AREA-5.2'] ?? []));
        $this->assertSame(7, count($sections['AREA-5.2-ICT'] ?? []));
    }

    public function test_rubric_descriptors_repair_broken_words(): void
    {
        $parser = app(RubricMarkdownParser::class);

        $this->assertSame(
            'Policy exists and is consistently implemented.',
            $parser->repairBrokenWords('Policy exists and is consistently implemente d.')
        );
    }
}
