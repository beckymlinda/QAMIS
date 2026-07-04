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
}
