<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            $table->boolean('apple_pay_enabled')->default(false)->after('wallet_enabled');
            $table->boolean('google_pay_enabled')->default(false)->after('apple_pay_enabled');
        });

        DB::table('card_products')->update([
            'apple_pay_enabled' => DB::raw('wallet_enabled'),
            'google_pay_enabled' => DB::raw('wallet_enabled'),
        ]);

        Schema::table('card_products', function (Blueprint $table) {
            $table->dropColumn('wallet_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            $table->boolean('wallet_enabled')->default(false)->after('provider_issue_cost_usd');
        });

        DB::table('card_products')->update([
            'wallet_enabled' => DB::raw('apple_pay_enabled or google_pay_enabled'),
        ]);

        Schema::table('card_products', function (Blueprint $table) {
            $table->dropColumn(['apple_pay_enabled', 'google_pay_enabled']);
        });
    }
};
