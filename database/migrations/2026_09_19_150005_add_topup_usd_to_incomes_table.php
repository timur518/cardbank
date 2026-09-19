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
        Schema::table('incomes', function (Blueprint $table) {
            // Зафиксированная на шаге оформления заказа сумма пополнения в $ (см.
            // CARD_ORDER_AND_ISSUANCE_FLOW.md, шаг 1) — нужна webhook-обработчику
            // платёжной системы (шаг 3-4) для вызова issueCard()/topUpCard() и для
            // идемпотентных повторов POST /orders/issue и /orders/topup.
            $table->decimal('topup_usd', 14, 2)->nullable()->after('amount_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('topup_usd');
        });
    }
};
