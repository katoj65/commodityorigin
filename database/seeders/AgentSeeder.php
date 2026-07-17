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
     */
    public function run(): void
    {
        $agents = [
            [
                'agent_type' => 'buyer_agent',
                'name' => 'Buyer Agent',
                'description' => 'Matches buyer criteria against available lots and lot requests, and recommends or places bids.',
                'action' => 'match_lots',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'quality_grading_agent',
                'name' => 'Quality Grading Agent',
                'description' => 'Analyzes cup score, moisture content, and defect counts to assign a quality grade to batches and lots.',
                'action' => 'grade_batch',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'price_recommendation_agent',
                'name' => 'Price Recommendation Agent',
                'description' => 'Suggests a fair market price for a lot based on quality, season, and comparable market activity.',
                'action' => 'recommend_price',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'compliance_verification_agent',
                'name' => 'Compliance Verification Agent',
                'description' => 'Reviews batch and farm certification documents, flagging missing or expiring compliance records.',
                'action' => 'verify_compliance',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'market_matching_agent',
                'name' => 'Market Matching Agent',
                'description' => 'Matches open lot requests to available lots across the marketplace based on buyer demand signals.',
                'action' => 'match_market',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'bid_negotiation_agent',
                'name' => 'Bid Negotiation Agent',
                'description' => 'Manages counter-offers between buyers and sellers within seller-defined bounds to help close a bid.',
                'action' => 'negotiate_bid',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'harvest_forecasting_agent',
                'name' => 'Harvest Forecasting Agent',
                'description' => 'Predicts expected harvest yield and timing using farm, season, and climate metadata.',
                'action' => 'forecast_harvest',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'fraud_detection_agent',
                'name' => 'Fraud Detection Agent',
                'description' => 'Flags suspicious listings, duplicate batch numbers, and abnormal pricing for manual review.',
                'action' => 'detect_anomaly',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'logistics_agent',
                'name' => 'Logistics Agent',
                'description' => 'Coordinates transporter and warehouse assignment once a bid is accepted.',
                'action' => 'coordinate_fulfillment',
                'status' => 'pending',
            ],
            [
                'agent_type' => 'onboarding_agent',
                'name' => 'Farmer Onboarding Agent',
                'description' => 'Guides new farmers and cooperatives through profile setup and document verification.',
                'action' => 'onboard_farmer',
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
