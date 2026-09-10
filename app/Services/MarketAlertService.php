<?php

namespace App\Services;

use App\Models\MarketAlert;
use Illuminate\Support\Collection;

class MarketAlertService
{
    /**
     * Every alert preference for a user — one row per known alert type
     * (see MarketAlert::TYPES), defaulting to enabled for any type the
     * user hasn't explicitly set yet so the panel always shows a full,
     * consistent toggle list.
     *
     * @return Collection<int, MarketAlert>
     */
    public function preferencesForUser(int $userId): Collection
    {
        $existing = MarketAlert::query()
            ->forUser($userId)
            ->get()
            ->keyBy('type');

        return collect(MarketAlert::TYPES)
            ->map(fn (string $label, string $type): MarketAlert => $existing->get($type) ?? new MarketAlert([
                'user_id' => $userId,
                'type' => $type,
                'enabled' => true,
            ]))
            ->values();
    }

    /**
     * Enable or disable one alert type for a user.
     */
    public function setEnabled(int $userId, string $type, bool $enabled): MarketAlert
    {
        return MarketAlert::query()->updateOrCreate(
            ['user_id' => $userId, 'type' => $type],
            ['enabled' => $enabled],
        );
    }
}
