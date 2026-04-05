<?php

namespace Database\Seeders;

use App\Models\RoleMetadata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleMetadataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            [
                'slug' => 'user',
                'name' => 'User',
                'description' => 'Standard platform account for day-to-day access.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'slug' => 'farmer',
                'name' => 'Farmer',
                'description' => 'Producer profile for coffee growers and farm suppliers.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'slug' => 'buyer',
                'name' => 'Buyer',
                'description' => 'Marketplace buyer account for bids, lots, and purchasing.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'slug' => 'exporter',
                'name' => 'Exporter',
                'description' => 'Trade account for export operations and fulfillment workflows.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'slug' => 'processor',
                'name' => 'Processor',
                'description' => 'Operational account for processing, milling, and quality handling.',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'slug' => 'transporter',
                'name' => 'Transporter',
                'description' => 'Logistics account for moving lots, batches, and warehouse transfers.',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'slug' => 'warehouse',
                'name' => 'Warehouse',
                'description' => 'Storage and warehouse management account for lot custody.',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'slug' => 'regulator',
                'name' => 'Regulator',
                'description' => 'Oversight account for compliance, approvals, and regulatory checks.',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'slug' => 'auditor',
                'name' => 'Auditor',
                'description' => 'Audit account for review, traceability, and verification work.',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'slug' => 'investor',
                'name' => 'Investor',
                'description' => 'Observer account for portfolio visibility and market participation.',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'slug' => 'admin',
                'name' => 'Admin',
                'description' => 'Administrative account for system setup, support, and oversight.',
                'sort_order' => 11,
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            RoleMetadata::query()->updateOrCreate(
                ['slug' => $role['slug']],
                $role,
            );
        }
    }
}
