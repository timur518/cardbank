<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Порядок колонок в физической таблице incomes разошёлся с порядком, который
     * задавали `->after(...)` в миграциях, добавлявших payment_transaction_id,
     * payment_status, amount_usd, idempotency_key, topup_usd, — SQLite игнорирует
     * `->after(...)` (см. также 2026_09_20_130001_..._card_transactions), поэтому все
     * они осели в конце таблицы вместо мест рядом с amount/currency/payment_method_id,
     * где были объявлены. Пересобираем таблицу с колонками, сгруппированными по
     * смыслу: денежные поля, связи, данные платёжного шлюза, затем comment/created_by/
     * created_at в конце. updated_at не добавляем — у Income сознательно
     * `const UPDATED_AT = null`.
     */
    public function up(): void
    {
        Schema::create('incomes_reordered', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->decimal('amount', 14, 2);
            $table->decimal('amount_usd', 14, 2)->nullable();
            $table->decimal('topup_usd', 14, 2)->nullable();
            $table->string('currency');
            $table->foreignId('card_id')->nullable()->constrained('cards')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('card_transaction_id')->nullable()->constrained('card_transactions')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_transaction_id')->nullable()->index();
            $table->string('payment_status')->default('pending');
            $table->string('idempotency_key')->nullable()->unique();
            $table->text('comment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });

        DB::statement(<<<'SQL'
            INSERT INTO incomes_reordered
                (id, type, amount, amount_usd, topup_usd, currency, card_id, user_id, card_transaction_id, payment_method_id, payment_transaction_id, payment_status, idempotency_key, comment, created_by, created_at)
            SELECT
                id, type, amount, amount_usd, topup_usd, currency, card_id, user_id, card_transaction_id, payment_method_id, payment_transaction_id, payment_status, idempotency_key, comment, created_by, created_at
            FROM incomes
        SQL);

        Schema::drop('incomes');
        Schema::rename('incomes_reordered', 'incomes');

        // Индексы после rename остались с префиксом incomes_reordered_... — пересоздаём с
        // чистыми именами (старая таблица и её индексы уже удалены выше, имена свободны).
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropIndex('incomes_reordered_payment_transaction_id_index');
            $table->dropUnique('incomes_reordered_idempotency_key_unique');
            $table->index('payment_transaction_id', 'incomes_payment_transaction_id_index');
            $table->unique('idempotency_key', 'incomes_idempotency_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Чисто косметическая перестановка колонок — откатывать физический порядок
        // столбцов смысла нет (модель и запросы работают по именам колонок).
    }
};
