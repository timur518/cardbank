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
            // Мерчант из справочника admin/merchants, определённый автоматически по
            // подстроке кода в 'merchant' (описании операции от провайдера) —
            // см. Merchant::matchByDescription(). null, если совпадение не найдено —
            // тогда клиенту показывается 'merchant' как есть (см. CardTransactionResource).
            $table->foreignId('merchant_id')->nullable()->after('merchant')
                ->constrained('merchants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('merchant_id');
        });
    }
};
