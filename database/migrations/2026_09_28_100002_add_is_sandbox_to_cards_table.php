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
        Schema::table('cards', function (Blueprint $table) {
            // Карта выпущена через способ оплаты с sandbox_mode = true — с фейковыми
            // реквизитами и без реальной карты у провайдера (provider_card_id — синтетический
            // "SANDBOX-..." идентификатор). Используется, чтобы фоновые команды синхронизации
            // с провайдером (providers:sync-card-balances, providers:sync-card-transactions)
            // не пытались опрашивать по ней реальный API.
            $table->boolean('is_sandbox')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('is_sandbox');
        });
    }
};
