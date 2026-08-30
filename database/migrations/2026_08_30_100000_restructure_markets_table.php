<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Restructures markets from a coffee-lot-specific listing shape into a
     * general commodity-listing shape. `title`/`description`/`price_per_unit`
     * replace `name`/`notes`/`price_per_kg`. The coffee-specific descriptive
     * fields with no new column equivalent (origin, type, process,
     * quality_score, demand, badges, target_market, image) are folded into
     * the new `metadata` JSON column rather than lost, so existing
     * market-intelligence/filtering features (see MarketService) keep
     * working — Market model accessors read them back out of metadata.
     * `lot_code` is dropped outright: it only ever duplicated the linked
     * lot's own lot_number.
     */
    public function up(): void
    {
        Schema::table('markets', function (Blueprint $table): void {
            $table->decimal('available_quantity', 10, 2)->nullable()->after('quantity');
            $table->string('unit')->default('kg')->after('available_quantity');
            $table->string('currency', 3)->default('USD')->after('unit');
            $table->string('pricing_type')->default('fixed')->after('price_per_kg');
            $table->decimal('minimum_order_quantity', 10, 2)->nullable()->after('pricing_type');
            $table->string('payment_terms')->nullable()->after('minimum_order_quantity');
            $table->string('delivery_terms')->nullable()->after('payment_terms');
            $table->string('delivery_location')->nullable()->after('delivery_terms');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('is_public')->default(true)->after('is_featured');
            $table->json('metadata')->nullable()->after('is_public');
        });

        foreach (DB::table('markets')->get() as $market) {
            DB::table('markets')->where('id', $market->id)->update([
                'available_quantity' => $market->quantity,
                'metadata' => json_encode(array_filter([
                    'lot_code' => $market->lot_code,
                    'origin' => $market->origin,
                    'type' => $market->type,
                    'process' => $market->process,
                    'quality_score' => $market->quality_score,
                    'demand' => $market->demand,
                    'badges' => $market->badges ? json_decode($market->badges, true) : null,
                    'target_market' => $market->target_market,
                    'image' => $market->image,
                ], fn ($value) => $value !== null)),
            ]);
        }

        Schema::table('markets', function (Blueprint $table): void {
            $table->renameColumn('name', 'title');
            $table->renameColumn('notes', 'description');
            $table->renameColumn('price_per_kg', 'price_per_unit');
        });

        Schema::table('markets', function (Blueprint $table): void {
            $table->dropUnique('markets_lot_code_unique');
        });

        Schema::table('markets', function (Blueprint $table): void {
            $table->dropColumn(['lot_code', 'origin', 'type', 'process', 'quality_score', 'demand', 'badges', 'target_market', 'image']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table): void {
            $table->string('lot_code')->nullable()->after('user_id');
            $table->string('origin')->nullable()->after('title');
            $table->string('type')->nullable()->after('origin');
            $table->string('process')->nullable()->after('type');
            $table->decimal('quality_score', 5, 2)->nullable()->after('process');
            $table->string('demand')->nullable()->after('price_per_unit');
            $table->json('badges')->nullable()->after('demand');
            $table->string('target_market')->nullable()->after('badges');
            $table->string('image')->nullable()->after('status');
        });

        foreach (DB::table('markets')->get() as $market) {
            $metadata = $market->metadata ? json_decode($market->metadata, true) : [];

            DB::table('markets')->where('id', $market->id)->update([
                'lot_code' => $metadata['lot_code'] ?? null,
                'origin' => $metadata['origin'] ?? null,
                'type' => $metadata['type'] ?? null,
                'process' => $metadata['process'] ?? null,
                'quality_score' => $metadata['quality_score'] ?? null,
                'demand' => $metadata['demand'] ?? null,
                'badges' => isset($metadata['badges']) ? json_encode($metadata['badges']) : null,
                'target_market' => $metadata['target_market'] ?? null,
                'image' => $metadata['image'] ?? null,
            ]);
        }

        Schema::table('markets', function (Blueprint $table): void {
            $table->renameColumn('title', 'name');
            $table->renameColumn('description', 'notes');
            $table->renameColumn('price_per_unit', 'price_per_kg');
        });

        Schema::table('markets', function (Blueprint $table): void {
            $table->dropColumn([
                'available_quantity', 'unit', 'currency', 'pricing_type',
                'minimum_order_quantity', 'payment_terms', 'delivery_terms',
                'delivery_location', 'is_featured', 'is_public', 'metadata',
            ]);
        });
    }
};
