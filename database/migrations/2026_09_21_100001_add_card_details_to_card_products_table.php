<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            $table->string('network')->nullable()->after('currency');
            $table->string('card_country')->nullable()->after('network');
            $table->string('bin')->nullable()->after('card_country');
            $table->decimal('successful_payment_fee_usd', 10, 2)->nullable()->after('provider_topup_fee_percent');
            $table->decimal('decline_fee_usd', 10, 2)->nullable()->after('successful_payment_fee_usd');
            $table->string('non_usd_payment_fee')->nullable()->after('decline_fee_usd');
            $table->decimal('risk_operation_fee_usd', 10, 2)->nullable()->after('non_usd_payment_fee');
            $table->boolean('three_ds_supported')->default(false)->after('risk_operation_fee_usd');
            $table->longText('restricted_merchants')->nullable()->after('billing_post_code');
            $table->longText('full_terms')->nullable()->after('restricted_merchants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            $table->dropColumn([
                'network',
                'card_country',
                'bin',
                'successful_payment_fee_usd',
                'decline_fee_usd',
                'non_usd_payment_fee',
                'risk_operation_fee_usd',
                'three_ds_supported',
                'restricted_merchants',
                'full_terms',
            ]);
        });
    }
};
