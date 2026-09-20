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
        Schema::table('card_transactions', function (Blueprint $table) {
            // originTxId (GET /{san}/transactions) / originTxnId (вебхук CARD_TRANSACTION) у
            // CardsPro — id операции-холда (authorization), которую расчёт (expense) закрывает.
            // Используется, чтобы при поступлении расчёта СЛИТЬ его с уже существующей записью
            // холда по этой же покупке (см. CardTransaction::upsertFromProvider()), а не завести
            // вторую строку на одну и ту же операцию — раньше это давало дубли в истории.
            $table->string('origin_tx_id')->nullable()->after('provider_tx_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_transactions', function (Blueprint $table) {
            $table->dropColumn('origin_tx_id');
        });
    }
};
