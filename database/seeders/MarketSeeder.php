<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $origins = [
            'Bugisu, Uganda', 'Rwenzori, Uganda', 'Mount Elgon, Uganda', 'Kapchorwa, Uganda',
            'West Nile, Uganda', 'Mubende, Uganda', 'Sidamo, Ethiopia', 'Yirgacheffe, Ethiopia',
            'Santos, Brazil', 'Minas Gerais, Brazil', 'Huila, Colombia', 'Antioquia, Colombia',
            'Chiapas, Mexico', 'Kivu, DR Congo', 'Nyeri, Kenya', 'Kirinyaga, Kenya',
        ];
        $types = ['arabica', 'robusta'];
        $processes = ['washed', 'natural', 'honey', 'semi-washed'];
        $grades = ['AA', 'AB', 'PB', 'Grade 1', 'Grade 2', 'FAQ'];
        $targetMarkets = ['EU', 'North America', 'MENA', 'Asia Pacific', 'Domestic'];
        $demands = ['high', 'medium', 'low'];
        $statuses = ['live', 'live', 'live', 'live', 'pending', 'sold'];
        $badgePool = ['Organic', 'Fair Trade', 'Rainforest Alliance', 'UTZ Certified', 'Direct Trade', 'Single Origin', 'UCDA Graded'];

        $userIds = User::query()->pluck('id')->all();

        for ($i = 1; $i <= 100; $i++) {
            $origin = fake()->randomElement($origins);
            $type = fake()->randomElement($types);

            Market::query()->updateOrCreate(
                ['lot_code' => 'MKT-' . now()->format('Y') . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $userIds ? fake()->randomElement($userIds) : null,
                    'name' => ucfirst($type) . ' ' . explode(',', $origin)[0] . ' ' . fake()->randomElement($grades),
                    'origin' => $origin,
                    'type' => $type,
                    'process' => fake()->randomElement($processes),
                    'quality_score' => fake()->randomFloat(2, 72, 92),
                    'quantity' => fake()->randomFloat(2, 200, 5000),
                    'price_per_kg' => fake()->randomFloat(2, 2.5, 9.5),
                    'demand' => fake()->randomElement($demands),
                    'badges' => fake()->randomElements($badgePool, fake()->numberBetween(0, 3)),
                    'target_market' => fake()->randomElement($targetMarkets),
                    'status' => fake()->randomElement($statuses),
                    'notes' => fake()->boolean(40) ? fake()->sentence(10) : null,
                ],
            );
        }
    }
}
