<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleMetadataSeeder::class,
            CropMetadataSeeder::class,
            CropVarietyMetadataSeeder::class,
            RipenessGradeMetadataSeeder::class,
            PickMethodMetadataSeeder::class,
        ]);

        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'role' => 'user',
            'telephone' => '+1234567890',
            'email' => 'test@example.com',
        ]);
    }
}
