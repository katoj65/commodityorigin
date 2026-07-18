<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $rates = [
            ['base_currency' => 'USD', 'quote_currency' => 'UGX', 'rate' => 3742.50, 'daily_change_percent' => 0.12],
            ['base_currency' => 'USD', 'quote_currency' => 'BRL', 'rate' => 5.41, 'daily_change_percent' => -0.24],
            ['base_currency' => 'USD', 'quote_currency' => 'ETB', 'rate' => 118.30, 'daily_change_percent' => 0.08],
            ['base_currency' => 'EUR', 'quote_currency' => 'USD', 'rate' => 1.0862, 'daily_change_percent' => 0.05],
            ['base_currency' => 'USD', 'quote_currency' => 'VND', 'rate' => 25410, 'daily_change_percent' => -0.10],
            ['base_currency' => 'GBP', 'quote_currency' => 'USD', 'rate' => 1.2704, 'daily_change_percent' => 0.18],
        ];

        foreach ($rates as $rate) {
            ExchangeRate::query()->updateOrCreate(
                ['base_currency' => $rate['base_currency'], 'quote_currency' => $rate['quote_currency']],
                [...$rate, 'recorded_at' => now()],
            );
        }
    }
}
