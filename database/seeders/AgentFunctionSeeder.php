<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgentFunctionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The callable functions each agent type exposes, keyed by agent_type.
     * Icons are Element Plus icon component names, matching the frontend's
     * icon library.
     *
     * @var array<string, array<int, array{name: string, icon: string, slug: string, description: string, parameters: array<string, string>}>>
     */
    private const FUNCTIONS = [
        'farmer_agent' => [
            ['name' => 'Log Harvest', 'icon' => 'Notebook', 'slug' => 'log_harvest', 'description' => 'Record a new harvest entry for a farm.', 'parameters' => ['farm_id' => 'integer', 'quantity_kg' => 'number', 'harvest_date' => 'date']],
            ['name' => 'Create Batch', 'icon' => 'Box', 'slug' => 'create_batch', 'description' => 'Create a processing batch from a logged harvest.', 'parameters' => ['harvest_id' => 'integer', 'process_method' => 'string']],
            ['name' => 'Publish Lot', 'icon' => 'Sell', 'slug' => 'publish_lot', 'description' => 'Publish a graded batch as a sellable lot on the marketplace.', 'parameters' => ['batch_id' => 'integer', 'price_per_kg' => 'number']],
        ],
        'supplier_agent' => [
            ['name' => 'Match Suppliers', 'icon' => 'Connection', 'slug' => 'match_suppliers', 'description' => 'Find verified suppliers for a requested farm input.', 'parameters' => ['input_type' => 'string', 'region' => 'string']],
            ['name' => 'Place Supply Order', 'icon' => 'ShoppingCart', 'slug' => 'place_supply_order', 'description' => 'Place an order for farm inputs from a matched supplier.', 'parameters' => ['supplier_id' => 'integer', 'item' => 'string', 'quantity' => 'number']],
            ['name' => 'Track Supply Order', 'icon' => 'Van', 'slug' => 'track_supply_order', 'description' => 'Check the delivery status of a supply order.', 'parameters' => ['order_id' => 'integer']],
        ],
        'finance_agent' => [
            ['name' => 'Check Wallet Balance', 'icon' => 'Wallet', 'slug' => 'check_wallet_balance', 'description' => 'Retrieve the current wallet balance for a user.', 'parameters' => ['user_id' => 'integer']],
            ['name' => 'Flag Financing Opportunity', 'icon' => 'TrendCharts', 'slug' => 'flag_financing_opportunity', 'description' => 'Surface a working-capital offer based on trading history.', 'parameters' => ['user_id' => 'integer']],
            ['name' => 'Reconcile Transaction', 'icon' => 'DocumentChecked', 'slug' => 'reconcile_transaction', 'description' => 'Reconcile a wallet transaction against an order.', 'parameters' => ['transaction_id' => 'integer', 'order_id' => 'integer']],
        ],
        'regulator_agent' => [
            ['name' => 'Review Lot Compliance', 'icon' => 'Search', 'slug' => 'review_lot_compliance', 'description' => "Check a lot's documentation against export regulations.", 'parameters' => ['lot_id' => 'integer']],
            ['name' => 'Flag Non-Compliant Listing', 'icon' => 'WarningFilled', 'slug' => 'flag_noncompliant_listing', 'description' => 'Flag a listing that fails a compliance check.', 'parameters' => ['lot_id' => 'integer', 'reason' => 'string']],
            ['name' => 'Generate Compliance Report', 'icon' => 'Document', 'slug' => 'generate_compliance_report', 'description' => 'Produce a compliance summary for a date range.', 'parameters' => ['start_date' => 'date', 'end_date' => 'date']],
        ],
        'exporter_agent' => [
            ['name' => 'Prepare Export Documents', 'icon' => 'Document', 'slug' => 'prepare_export_documents', 'description' => 'Generate export paperwork for a shipment.', 'parameters' => ['order_id' => 'integer', 'destination_country' => 'string']],
            ['name' => 'Schedule Shipment', 'icon' => 'Calendar', 'slug' => 'schedule_shipment', 'description' => 'Book a shipping slot for an outbound lot.', 'parameters' => ['lot_id' => 'integer', 'carrier' => 'string', 'ship_date' => 'date']],
            ['name' => 'Track Customs Clearance', 'icon' => 'Ship', 'slug' => 'track_customs_clearance', 'description' => 'Check customs clearance status for a shipment.', 'parameters' => ['shipment_id' => 'integer']],
        ],
        'inspector_agent' => [
            ['name' => 'Schedule Inspection', 'icon' => 'Calendar', 'slug' => 'schedule_inspection', 'description' => 'Book a quality inspection for a batch.', 'parameters' => ['batch_id' => 'integer', 'inspection_date' => 'date']],
            ['name' => 'Record Cup Score', 'icon' => 'StarFilled', 'slug' => 'record_cup_score', 'description' => 'Log a cupping score and defect count for a batch.', 'parameters' => ['batch_id' => 'integer', 'cup_score' => 'number', 'defect_count' => 'integer']],
            ['name' => 'Flag Quality Issue', 'icon' => 'WarningFilled', 'slug' => 'flag_quality_issue', 'description' => 'Flag a batch that fails minimum quality thresholds.', 'parameters' => ['batch_id' => 'integer', 'issue' => 'string']],
        ],
        'processor_agent' => [
            ['name' => 'Record Processing Method', 'icon' => 'SetUp', 'slug' => 'record_processing_method', 'description' => 'Log the processing method applied to a batch.', 'parameters' => ['batch_id' => 'integer', 'method' => 'string']],
            ['name' => 'Assign Quality Grade', 'icon' => 'Medal', 'slug' => 'assign_quality_grade', 'description' => 'Assign a quality grade to a processed batch.', 'parameters' => ['batch_id' => 'integer', 'grade' => 'string']],
            ['name' => 'Estimate Yield', 'icon' => 'DataAnalysis', 'slug' => 'estimate_yield', 'description' => 'Estimate finished-coffee yield from raw cherry input.', 'parameters' => ['batch_id' => 'integer', 'raw_weight_kg' => 'number']],
        ],
        'cooperative_agent' => [
            ['name' => 'Aggregate Member Harvests', 'icon' => 'Collection', 'slug' => 'aggregate_member_harvests', 'description' => 'Pool harvests from member farmers into a shared lot.', 'parameters' => ['cooperative_id' => 'integer', 'harvest_ids' => 'array']],
            ['name' => 'Coordinate Pooled Sale', 'icon' => 'Sell', 'slug' => 'coordinate_pooled_sale', 'description' => 'Coordinate a sale of a pooled lot on behalf of members.', 'parameters' => ['pooled_lot_id' => 'integer', 'buyer_id' => 'integer']],
            ['name' => 'Calculate Member Payout', 'icon' => 'Money', 'slug' => 'calculate_member_payout', 'description' => "Calculate each member's share of a pooled sale.", 'parameters' => ['pooled_lot_id' => 'integer']],
        ],
        'logistic_agent' => [
            ['name' => 'Assign Transporter', 'icon' => 'Van', 'slug' => 'assign_transporter', 'description' => 'Assign a transporter to move a lot after a bid is accepted.', 'parameters' => ['order_id' => 'integer', 'transporter_id' => 'integer']],
            ['name' => 'Schedule Warehouse Handoff', 'icon' => 'HomeFilled', 'slug' => 'schedule_warehouse_handoff', 'description' => "Schedule a lot's handoff to a warehouse.", 'parameters' => ['lot_id' => 'integer', 'warehouse_id' => 'integer', 'date' => 'date']],
            ['name' => 'Track Shipment', 'icon' => 'Position', 'slug' => 'track_shipment', 'description' => 'Track the live location and status of a shipment.', 'parameters' => ['shipment_id' => 'integer']],
        ],
        'bank_agent' => [
            ['name' => 'Verify Transaction', 'icon' => 'CircleCheck', 'slug' => 'verify_transaction', 'description' => 'Verify a wallet transaction before it settles.', 'parameters' => ['transaction_id' => 'integer']],
            ['name' => 'Release Escrow', 'icon' => 'Unlock', 'slug' => 'release_escrow', 'description' => 'Release escrowed funds on a completed trade.', 'parameters' => ['escrow_id' => 'integer']],
            ['name' => 'Process Financing Request', 'icon' => 'CreditCard', 'slug' => 'process_financing_request', 'description' => 'Process a trade-financing application.', 'parameters' => ['user_id' => 'integer', 'amount' => 'number']],
        ],
        'government_agent' => [
            ['name' => 'Verify Export License', 'icon' => 'DocumentChecked', 'slug' => 'verify_export_license', 'description' => "Verify an exporter's license is valid and current.", 'parameters' => ['user_id' => 'integer']],
            ['name' => 'Review Production Report', 'icon' => 'Document', 'slug' => 'review_production_report', 'description' => 'Review a submitted production report for a region.', 'parameters' => ['report_id' => 'integer']],
            ['name' => 'Flag Policy Violation', 'icon' => 'WarningFilled', 'slug' => 'flag_policy_violation', 'description' => 'Flag activity that violates national trade policy.', 'parameters' => ['subject_type' => 'string', 'subject_id' => 'integer', 'reason' => 'string']],
        ],
        'certifier_agent' => [
            ['name' => 'Issue Certification', 'icon' => 'Stamp', 'slug' => 'issue_certification', 'description' => 'Issue a certification (Organic, Fair Trade, etc.) to a farm or lot.', 'parameters' => ['subject_type' => 'string', 'subject_id' => 'integer', 'certification_type' => 'string']],
            ['name' => 'Verify Certification', 'icon' => 'CircleCheck', 'slug' => 'verify_certification', 'description' => 'Verify an existing certification is valid and unexpired.', 'parameters' => ['certification_id' => 'integer']],
            ['name' => 'Schedule Certification Audit', 'icon' => 'Calendar', 'slug' => 'schedule_certification_audit', 'description' => 'Schedule an audit visit for recertification.', 'parameters' => ['subject_type' => 'string', 'subject_id' => 'integer', 'audit_date' => 'date']],
        ],
        'roaster_agent' => [
            ['name' => 'Match Roast Profile', 'icon' => 'MagicStick', 'slug' => 'match_roast_profile', 'description' => "Match available lots to a roaster's preferred flavor and roast profile.", 'parameters' => ['roaster_id' => 'integer', 'profile' => 'string']],
            ['name' => 'Place Purchase Order', 'icon' => 'ShoppingCart', 'slug' => 'place_purchase_order', 'description' => 'Place a purchase order for a matched green-coffee lot.', 'parameters' => ['lot_id' => 'integer', 'quantity_kg' => 'number']],
            ['name' => 'Log Roast Result', 'icon' => 'CoffeeCup', 'slug' => 'log_roast_result', 'description' => 'Log the roast outcome for a purchased lot.', 'parameters' => ['order_id' => 'integer', 'roast_notes' => 'string']],
        ],
        'importer_agent' => [
            ['name' => 'Prepare Import Documents', 'icon' => 'Document', 'slug' => 'prepare_import_documents', 'description' => 'Prepare import documentation for an inbound shipment.', 'parameters' => ['shipment_id' => 'integer', 'origin_country' => 'string']],
            ['name' => 'Track Customs Status', 'icon' => 'Position', 'slug' => 'track_customs_status', 'description' => 'Track customs clearance status for an inbound shipment.', 'parameters' => ['shipment_id' => 'integer']],
            ['name' => 'Log Receiving Inspection', 'icon' => 'Search', 'slug' => 'log_receiving_inspection', 'description' => 'Log the receiving inspection results for an inbound lot.', 'parameters' => ['shipment_id' => 'integer', 'condition_notes' => 'string']],
        ],
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (self::FUNCTIONS as $agentType => $functions) {
            $agent = Agent::query()->where('agent_type', $agentType)->first();

            if (! $agent) {
                continue;
            }

            foreach ($functions as $function) {
                $agent->functions()->updateOrCreate(
                    ['slug' => $function['slug']],
                    [
                        'name' => $function['name'],
                        'icon' => $function['icon'],
                        'description' => $function['description'],
                        'parameters' => $function['parameters'],
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
