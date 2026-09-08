<?php

namespace Database\Seeders;

use App\Models\MarketIntelligenceArticle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketIntelligenceArticleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Bean Origin Launches a Real-Time Live Market Overview',
                'category' => 'Platform Update',
                'sentiment' => 'positive',
                'excerpt' => 'A new market-wide pulse page surfaces live listing counts, average pricing, demand sentiment, and supply gaps computed straight from the exchange.',
                'body' => "The Live Market page now gives every trader a single view of exchange-wide activity: total live listings, total volume, average price per kilogram, and a demand sentiment score, all computed live from active listings rather than static figures.\n\nBelow the headline numbers, a Demand Breakdown panel shows the real split of listings across high, medium, and low buyer demand, and a Market Opportunities panel highlights coffee types where demand is currently outpacing available supply — useful for sellers deciding what to bring to market next.",
            ],
            [
                'title' => 'Understanding Coffee Grading: AA, AB, PB, and FAQ Explained',
                'category' => 'Education',
                'sentiment' => 'neutral',
                'excerpt' => 'A quick primer on the bean-size and quality grades used across listings on the exchange, from Arabica screen sizes to Robusta\'s Fair Average Quality standard.',
                'body' => "Coffee grades on Bean Origin describe bean size and quality, not flavor. Arabica lots are commonly graded AA (screen 17-18, the largest), AB (screen 15-16), PB — peaberry, a single rounded bean per cherry — down to C and T grades for smaller or fragmented beans.\n\nRobusta lots typically carry screen-size grades like Screen 18 and Screen 15, or the FAQ (Fair Average Quality) designation used as the standard Robusta export grade. Above these sits Specialty (a cupping score of 80 or higher), Premium, and Commercial as broader quality tiers you'll see on listings and lot profiles.",
            ],
            [
                'title' => 'How Demand Sentiment Is Calculated on the Exchange',
                'category' => 'Education',
                'sentiment' => 'neutral',
                'excerpt' => 'The sentiment score shown on Live Market is a weighted read of real demand tags across every live listing — not a prediction, a snapshot.',
                'body' => "Every live listing carries a demand tag — high, medium, or low — set when it's posted to the market. The exchange turns that into a single sentiment score by weighting high-demand listings at 100, medium at 60, and low at 20, then averaging across every live listing.\n\nThe result is a 0-100 figure that moves as the mix of listings changes. It's a snapshot of what's currently listed and how buyers are responding, not a forecast — treat it alongside the Demand Breakdown and Market Opportunities panels for the fuller picture.",
            ],
            [
                'title' => 'Escrow and Wallet: How Settlement Works on Bean Origin',
                'category' => 'Trading',
                'sentiment' => 'neutral',
                'excerpt' => 'Every order settles through the platform wallet and an escrow step designed to protect both the buyer and the seller until the trade is confirmed.',
                'body' => "When a buyer and seller agree on a trade — whether through Buy Now, Auction, an accepted Offer, or an RFQ response — funds move through the Bean Origin wallet rather than directly between parties. Escrow holds the payment until the trade's terms are met, then releases it.\n\nCurrency conversion is handled automatically against the buyer's and seller's preferred settlement currencies, so neither side has to manage the exchange themselves.",
            ],
            [
                'title' => 'Traceability From Farm to Cup: QR Codes and the Blockchain Ledger',
                'category' => 'Traceability',
                'sentiment' => 'positive',
                'excerpt' => 'Every lot on the exchange carries a QR code linked to a blockchain-backed activity ledger, so buyers can verify origin and handling before they trade.',
                'body' => "Every lot registered on Bean Origin is issued a QR code the moment it's created. Scanning it opens the lot's traceability record — the farm it came from, the batch it was processed in, and a chronological activity ledger recorded on-chain.\n\nFor buyers, that means verifying origin, grade, and handling history is no longer a matter of trust in a listing description — it's a record they can check themselves before they commit to a trade.",
            ],
            [
                'title' => 'New: User Designations and Farm Ownership Accounts',
                'category' => 'Platform Update',
                'sentiment' => 'positive',
                'excerpt' => 'Every account can now carry a designation — Farmer, Cooperative, Trader, Exporter, and more — and farm ownership is now tracked against real user accounts.',
                'body' => "Accounts on Bean Origin can now be tagged with a designation describing their role in the trade: Farmer, Cooperative, Trader, Exporter, Processor, Buyer, Roaster, Logistics Provider, Financial Service Provider, or Other.\n\nFarm ownership has also moved onto real user accounts. When a farm is registered, its owner is either the account creating it or a new account created on the spot from the owner's details — tagged as a Farmer and linked to the farm — rather than a disconnected name-only record.",
            ],
        ];

        foreach ($articles as $index => $article) {
            MarketIntelligenceArticle::query()->updateOrCreate(
                ['slug' => Str::slug($article['title'])],
                [
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'body' => $article['body'],
                    'category' => $article['category'],
                    'sentiment' => $article['sentiment'],
                    'source' => 'Bean Origin Desk',
                    'is_published' => true,
                    'published_at' => now()->subDays(count($articles) - $index),
                ],
            );
        }
    }
}
