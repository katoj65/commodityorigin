<?php

namespace Database\Seeders;

use App\Models\Cooperative;
use App\Models\Farmer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FarmerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $cooperativeIds = Cooperative::query()->pluck('id')->all();

        Farmer::factory()
            ->count(25)
            ->state(fn () => [
                'cooperative_id' => $cooperativeIds && fake()->boolean(60) ? fake()->randomElement($cooperativeIds) : null,
                'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']),
                'verification_status' => fake()->randomElement(['verified', 'verified', 'pending', 'rejected']),
            ])
            ->create();
    }
}
