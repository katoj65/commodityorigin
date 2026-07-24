<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'role' => 'user',
                'telephone' => '+1234567890',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ],
        );

        $this->call([
            RoleMetadataSeeder::class,
            CropMetadataSeeder::class,
            CropVarietyMetadataSeeder::class,
            ClimateZoneMetadataSeeder::class,
            RipenessGradeMetadataSeeder::class,
            PickMethodMetadataSeeder::class,
            QualityMetadataSeeder::class,
            FarmingPracticeMetadataSeeder::class,
            SustainableMetadataSeeder::class,
            ComplianceMetadataSeeder::class,
            FarmInputMetadataSeeder::class,
            FarmCultivationMetadataSeeder::class,
            FarmManagementMetadataSeeder::class,
            CertificationMetadataSeeder::class,
            RegulatoryComplianceMetadataSeeder::class,
            DocumentationMetadataSeeder::class,
            DocumentMetadataSeeder::class,
            SocialImpactMetadataSeeder::class,
            EnvironmentMetadataSeeder::class,
            LandMetadataSeeder::class,
            ClimateMetadataSeeder::class,
            HarvestingMetadataSeeder::class,
            SensoryMetadataSeeder::class,
            ProcessingMetadataSeeder::class,
            MarketMetadataSeeder::class,
            MarketSeeder::class,
            CropGradeMetadataSeeder::class,
            AgentSeeder::class,
            ExchangeRateSeeder::class,
            ForecastSeeder::class,
            CountrySeeder::class,
        ]);
    }
}
