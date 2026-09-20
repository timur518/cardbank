<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * У card_transactions отсутствовала колонка updated_at (модель жила с
     * `const UPDATED_AT = null`), а порядок колонок в физической таблице разошёлся
     * с порядком объявления в миграциях — SQLite игнорирует `->after(...)`
     * (эффекта не имеет ни у одной из миграций, где он использовался), поэтому
     * cost_amount/commission_amount/origin_tx_id осели в конце, а не там, где были
     * объявлены. Добавляем updated_at и пересобираем таблицу с колонками в нужном
     * порядке: бизнес-поля, потом created_at/updated_at — в самом конце.
     */
    public function up(): void
    {
        Schema::create('card_transactions_reordered', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 14, 2);
            $table->decimal('cost_amount', 14, 2)->nullable();
            $table->decimal('commission_amount', 14, 2)->nullable();
            $table->string('currency');
            $table->string('merchant')->nullable();
            $table->string('status');
            $table->text('decline_reason')->nullable();
            $table->string('provider_tx_id')->nullable();
            $table->string('origin_tx_id')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            // Защита от дублей при повторной доставке вебхука CARD_TRANSACTION (NULL в provider_tx_id не участвует в уникальности).
            // Имя пока авто-под имя временной таблицы — старая таблица ещё жива со своим индексом
            // card_transactions_card_id_provider_tx_id_unique, имена индексов глобальны для всей базы в SQLite —
            // переименуем его ниже уже после drop+rename таблиц.
            $table->unique(['card_id', 'provider_tx_id']);
        });

        DB::statement(<<<'SQL'
            INSERT INTO card_transactions_reordered
                (id, card_id, type, amount, cost_amount, commission_amount, currency, merchant, status, decline_reason, provider_tx_id, origin_tx_id, occurred_at, created_at, updated_at)
            SELECT
                id, card_id, type, amount, cost_amount, commission_amount, currency, merchant, status, decline_reason, provider_tx_id, origin_tx_id, occurred_at, created_at, created_at
            FROM card_transactions
        SQL);

        Schema::drop('card_transactions');
        Schema::rename('card_transactions_reordered', 'card_transactions');

        // Имя уникального индекса после rename осталось со старым префиксом
        // (card_transactions_reordered_...) — пересоздаём его с чистым именем, имя теперь
        // свободно (старая таблица и её индекс уже удалены выше).
        Schema::table('card_transactions', function (Blueprint $table) {
            $table->dropUnique('card_transactions_reordered_card_id_provider_tx_id_unique');
            $table->unique(['card_id', 'provider_tx_id'], 'card_transactions_card_id_provider_tx_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_transactions', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
