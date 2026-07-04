<?php

namespace Tests\Unit;

use App\Services\TimetableSchedulingService;
use PHPUnit\Framework\TestCase;

class TimetableSchedulingServiceTest extends TestCase
{
    public function test_times_overlap_detects_shared_period(): void
    {
        $service = new TimetableSchedulingService;

        $this->assertTrue($service->timesOverlap('08:00', '10:00', '09:00', '11:00'));
        $this->assertFalse($service->timesOverlap('08:00', '10:00', '10:00', '12:00'));
    }

    public function test_build_daily_windows_reserves_lunch_hour(): void
    {
        $service = new TimetableSchedulingService;
        $windows = $service->buildDailyWindows('08:00', '17:00', 120);

        $this->assertSame([
            ['start' => '08:00', 'end' => '10:00'],
            ['start' => '10:00', 'end' => '12:00'],
            ['start' => '13:00', 'end' => '15:00'],
            ['start' => '15:00', 'end' => '17:00'],
        ], $windows);

        foreach ($windows as $window) {
            $this->assertFalse($service->timesOverlap(
                $window['start'],
                $window['end'],
                TimetableSchedulingService::LUNCH_START,
                TimetableSchedulingService::LUNCH_END,
            ));
        }
    }
}
