<?php

namespace App\Services;

use App\Models\HowItWorksStep;
use Illuminate\Database\Eloquent\Collection;

class HowItWorksService
{
    /**
     * Fetch every active "How It Works" step, ordered for display.
     *
     * @return Collection<int, HowItWorksStep>
     */
    public function activeSteps(): Collection
    {
        return HowItWorksStep::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }
}
