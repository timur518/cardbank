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
            // Комиссия провайдера за пополнение карт этого продукта (у CardsPro — 3.5%) —
            // замена убранного ранее CardProvider.topup_fee_percent (см. миграцию
            // 2026_09_18_120001_drop_fee_columns_from_card_providers_table), теперь экономика
            // пополнения тоже считается на уровне карточного продукта.
            $table->decimal('provider_topup_fee_percent', 5, 2)->default(0)->after('provider_issue_cost_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            $table->dropColumn('provider_topup_fee_percent');
        });
    }
};
