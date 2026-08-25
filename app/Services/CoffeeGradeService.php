<?php

namespace App\Services;

use App\Models\CoffeeGrade;
use Illuminate\Database\Eloquent\Collection;

class CoffeeGradeService
{
    /**
     * Fetch every active coffee grade, ordered for display.
     *
     * @return Collection<int, CoffeeGrade>
     */
    public function activeOptions(): Collection
    {
        return CoffeeGrade::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
