<?php

namespace Tests\Unit;

use App\Support\GpaGrading;
use Tests\TestCase;

class GpaGradingTest extends TestCase
{
    public function test_it_maps_percentage_to_table_one_grades(): void
    {
        $this->assertSame('A+', GpaGrading::fromPercentage(90)['letter']);
        $this->assertSame(4.00, GpaGrading::fromPercentage(90)['points']);
        $this->assertSame('C-', GpaGrading::fromPercentage(52)['letter']);
        $this->assertSame('F', GpaGrading::fromPercentage(30)['letter']);
    }

    public function test_it_computes_weighted_final_mark(): void
    {
        $final = GpaGrading::computeFinal(80, 70);

        $this->assertSame(74.0, $final);
    }

    public function test_pass_threshold_is_c_minus_or_above(): void
    {
        $this->assertTrue(GpaGrading::hasPassed('C-'));
        $this->assertFalse(GpaGrading::hasPassed('D'));
    }

    public function test_tone_for_grade_points(): void
    {
        $this->assertSame('fail', GpaGrading::toneForPoints(1.5));
        $this->assertSame('warning', GpaGrading::toneForPoints(2.5));
        $this->assertSame('success', GpaGrading::toneForPoints(3.5));
    }

    public function test_semester_standing_uses_pass_threshold(): void
    {
        $pass = GpaGrading::semesterStanding(2.25);
        $fail = GpaGrading::semesterStanding(1.75);

        $this->assertTrue($pass['passed']);
        $this->assertSame('Pass', $pass['label']);
        $this->assertSame('warning', $pass['tone']);

        $this->assertFalse($fail['passed']);
        $this->assertSame('Fail', $fail['label']);
        $this->assertSame('fail', $fail['tone']);
    }
}
