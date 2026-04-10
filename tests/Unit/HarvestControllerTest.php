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
            '2025-05',
            '2025-06',
            '2025-07',
            '2025-08',
            '2025-09',
        ], $months);
    }

    public function test_get_range_of_dates_returns_empty_array_when_harvest_date_is_before_planted_date(): void
    {
        $months = HarvestController::getRangeOfDates('2025-09-01', '2025-05-18');

        $this->assertSame([], $months);
    }
}
