<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. A store no longer carries its own profile
     * details — opening one is just an admin-verified request tied to the
     * user's account, confirmed with the user's own email and password.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->dropColumn([
                'name',
                'description',
                'logo',
                'contact_phone',
                'contact_email',
                'address',
                'city',
                'country',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
        });
    }
};
