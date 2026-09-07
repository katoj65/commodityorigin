<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Backfills every existing `farm_owners` row into a real `users` record
     * (found by email, or created with designation "farmer") plus a
     * `user_farm_ownership` pivot row, then drops `farm_owners` — ownership
     * is now tracked against genuine user accounts instead of freestanding
     * name/contact columns.
     */
    public function up(): void
    {
        if (Schema::hasTable('farm_owners')) {
            $farmerDesignationId = DB::table('user_designation_metadata')->where('slug', 'farmer')->value('id');

            foreach (DB::table('farm_owners')->get() as $owner) {
                $userId = $owner->email ? DB::table('users')->where('email', $owner->email)->value('id') : null;

                if (! $userId) {
                    $lastName = trim(collect([$owner->middle_name, $owner->last_name])->filter()->implode(' '));

                    $userId = DB::table('users')->insertGetId([
                        'first_name' => $owner->first_name,
                        'last_name' => $lastName,
                        'national_id' => $owner->national_id,
                        'telephone' => $owner->tel ?: '',
                        'email' => $owner->email ?: 'owner-'.$owner->id.'@placeholder.beanorigin.local',
                        'password' => Hash::make(Str::random(40)),
                        'role' => 'user',
                        'user_designation_metadata_id' => $farmerDesignationId,
                        'created_by_user_id' => $owner->user_id,
                        'created_at' => $owner->created_at,
                        'updated_at' => $owner->updated_at,
                    ]);
                }

                DB::table('user_farm_ownership')->insertOrIgnore([
                    'farm_id' => $owner->farm_id,
                    'user_id' => $userId,
                    'ownership_percentage' => $owner->ownership_percentage,
                    'is_primary' => $owner->is_primary,
                    'created_at' => $owner->created_at,
                    'updated_at' => $owner->updated_at,
                ]);
            }

            Schema::dropIfExists('farm_owners');
        }
    }

    /**
     * Reverse the migrations. Recreates the table structure; the backfilled
     * `users`/`user_farm_ownership` rows are left in place rather than
     * un-migrated, since that split can't be reversed unambiguously.
     */
    public function down(): void
    {
        Schema::create('farm_owners', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('national_id')->nullable();
            $table->string('tel')->nullable();
            $table->string('email')->nullable();
            $table->decimal('ownership_percentage', 5, 2)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }
};
