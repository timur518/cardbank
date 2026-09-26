<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Рейтинг успешных платежей (0..1) конкретного карточного продукта у конкретного
 * мерчанта — CardsPro `GET /products/search-by-merchant`, см.
 * App\Console\Commands\Providers\SyncMerchantProductRates. Храним по всем продуктам
 * (даже неактивным) — таблица «Рейтинг платежей по мерчантам» в админке показывает
 * только колонки активных продуктов, но данные не теряются при деактивации.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_product_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_product_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate', 5, 4);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['merchant_id', 'card_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_product_rates');
    }
};
