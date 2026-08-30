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
        // Idempotent by count rather than a unique key — lot_code (the old
        // natural key) no longer exists as a real column now that markets
        // is a general commodity-listing shape; these seeded listings
        // never have a real lot_id to key off either.
        if (Market::query()->count() >= 100) {
            return;
        }

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
            $quantity = fake()->randomFloat(2, 200, 5000);

            Market::query()->create([
                'user_id' => $userIds ? fake()->randomElement($userIds) : null,
                'title' => ucfirst($type) . ' ' . explode(',', $origin)[0] . ' ' . fake()->randomElement($grades),
                'description' => fake()->boolean(40) ? fake()->sentence(10) : null,
                'quantity' => $quantity,
                'available_quantity' => $quantity,
                'unit' => 'kg',
                'currency' => 'USD',
                'price_per_unit' => fake()->randomFloat(2, 2.5, 9.5),
                'pricing_type' => 'fixed',
                'status' => fake()->randomElement($statuses),
                'is_featured' => fake()->boolean(15),
                'is_public' => true,
                'metadata' => [
                    'origin' => $origin,
                    'type' => $type,
                    'process' => fake()->randomElement($processes),
                    'quality_score' => fake()->randomFloat(2, 72, 92),
                    'demand' => fake()->randomElement($demands),
                    'badges' => fake()->randomElements($badgePool, fake()->numberBetween(0, 3)),
                    'target_market' => fake()->randomElement($targetMarkets),
                ],
            ]);
        }
    }
}
