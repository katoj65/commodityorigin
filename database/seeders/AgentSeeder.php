<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * One agent per coffee supply-chain stakeholder — these back the Apps
     * (Agents) directory so each stakeholder type can subscribe to an AI
     * assistant tailored to their role in the trade. Icons are Element Plus
     * icon component names, matching the frontend's icon library.
     */
    public function run(): void
    {
        $agents = [
            [
                'agent_type' => 'farmer_agent',
                'name' => 'Farmer Agent',
                'icon' => 'Cherry',
                'description' => 'Helps farmers log harvests, create batches, and publish lots to the marketplace, and flags seasonal yield anomalies.',
                'action' => 'manage_harvest',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'supplier_agent',
                'name' => 'Supplier Agent',
                'icon' => 'Box',
                'description' => 'Matches farmers and cooperatives with verified suppliers of seedlings, fertilizer, and farm equipment, and tracks input orders.',
                'action' => 'match_supply',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'finance_agent',
                'name' => 'Finance Agent',
                'icon' => 'Money',
                'description' => 'Monitors wallet balances and transaction history, and surfaces financing or working-capital opportunities for traders.',
                'action' => 'process_finance',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'regulator_agent',
                'name' => 'Regulator Agent',
                'icon' => 'DocumentChecked',
                'description' => 'Reviews lots and export documentation against trade regulations, flagging non-compliant listings before they reach market.',
                'action' => 'verify_compliance',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'exporter_agent',
                'name' => 'Exporter Agent',
                'icon' => 'Ship',
                'description' => 'Coordinates export paperwork, shipping schedules, and customs clearance for lots leaving the country of origin.',
                'action' => 'coordinate_export',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'inspector_agent',
                'name' => 'Inspector Agent',
                'icon' => 'Search',
                'description' => 'Schedules quality inspections and records cup scores, moisture content, and defect counts for batches and lots.',
                'action' => 'inspect_batch',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'processor_agent',
                'name' => 'Processor Agent',
                'icon' => 'SetUp',
                'description' => 'Tracks processing method (washed, natural, honey) through milling and assigns a quality grade to finished batches.',
                'action' => 'grade_batch',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'cooperative_agent',
                'name' => 'Cooperative Agent',
                'icon' => 'Connection',
                'description' => 'Aggregates member farmer harvests into pooled lots and coordinates cooperative-level sales and payouts.',
                'action' => 'manage_cooperative',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'logistic_agent',
                'name' => 'Logistics Agent',
                'icon' => 'Van',
                'description' => 'Coordinates transporter assignment, warehouse handoff, and shipment tracking once a bid or order is accepted.',
                'action' => 'coordinate_fulfillment',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'bank_agent',
                'name' => 'Bank Agent',
                'icon' => 'CreditCard',
                'description' => 'Verifies wallet transactions, releases escrow on completed trades, and manages trade-financing requests.',
                'action' => 'process_transaction',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'government_agent',
                'name' => 'Government Agent',
                'icon' => 'OfficeBuilding',
                'description' => 'Oversees national trade-policy compliance, export licensing, and production reporting across the platform.',
                'action' => 'oversee_policy',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'certifier_agent',
                'name' => 'Certifier Agent',
                'icon' => 'Stamp',
                'description' => 'Issues and verifies certifications (Organic, Fair Trade, Rainforest Alliance, UTZ) against farm and lot records.',
                'action' => 'verify_certification',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'roaster_agent',
                'name' => 'Roaster Agent',
                'icon' => 'CoffeeCup',
                'description' => 'Matches roasters with green-coffee lots suited to their roast profile and manages purchase orders.',
                'action' => 'match_roast_profile',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'importer_agent',
                'name' => 'Importer Agent',
                'icon' => 'Suitcase',
                'description' => 'Manages import documentation, customs clearance, and receiving inspection for inbound shipments.',
                'action' => 'coordinate_import',
                'status' => 'pending',
            ],
        ];

        foreach ($agents as $agent) {
            Agent::query()->updateOrCreate(
                ['agent_type' => $agent['agent_type']],
                $agent,
            );
        }
    }
}
