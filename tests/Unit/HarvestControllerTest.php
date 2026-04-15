<?php

namespace Tests\Unit;

use App\Http\Controllers\Harvest\HarvestController;
use Tests\TestCase;

class HarvestControllerTest extends TestCase
{
    public function test_get_range_of_dates_returns_inclusive_months_between_dates(): void
    {
        $months = HarvestController::getRangeOfDates('2025-05-01', '2025-09-18');

        $this->assertSame([
            ['start' => '2025-05', 'end' => '2025-05'],
            ['start' => '2025-06', 'end' => '2025-06'],
            ['start' => '2025-07', 'end' => '2025-07'],
            ['start' => '2025-08', 'end' => '2025-08'],
            ['start' => '2025-09', 'end' => '2025-09'],
        ], $months);
    }

    public function test_get_range_of_dates_returns_six_month_intervals_for_ranges_longer_than_twelve_months(): void
    {
        $months = HarvestController::getRangeOfDates('2024-01-01', '2025-05-18');

        $this->assertSame([
            ['start' => '2024-01', 'end' => '2024-06'],
            ['start' => '2024-07', 'end' => '2024-12'],
            ['start' => '2025-01', 'end' => '2025-05'],
        ], $months);
    }

    public function test_get_range_of_dates_returns_empty_array_when_harvest_date_is_before_planted_date(): void
    {
        $months = HarvestController::getRangeOfDates('2025-09-01', '2025-05-18');

        $this->assertSame([], $months);
    }
}
