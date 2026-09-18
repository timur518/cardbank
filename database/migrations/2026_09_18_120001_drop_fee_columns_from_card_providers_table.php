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
        Schema::table('card_providers', function (Blueprint $table) {
            // Комиссии и уровни стоимости выпуска у провайдера больше не ведём —
            // экономика считается на уровне карточных продуктов (CardProduct).
            $table->dropColumn([
                'topup_fee_percent',
                'min_topup_usd',
                'issue_fee_tiers',
                'extra_channel_fees',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_providers', function (Blueprint $table) {
            $table->decimal('topup_fee_percent', 5, 2)->default(0)->after('webhook_secret');
            $table->decimal('min_topup_usd', 12, 2)->default(0)->after('topup_fee_percent');
            $table->json('issue_fee_tiers')->nullable()->after('min_topup_usd');
            $table->json('extra_channel_fees')->nullable()->after('issue_fee_tiers');
        });
    }
};
