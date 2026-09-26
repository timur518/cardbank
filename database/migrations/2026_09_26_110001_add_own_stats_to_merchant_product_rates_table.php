<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Собственная статистика успешных/отказанных операций по паре «мерчант × карточный
 * продукт», в дополнение к рейтингу от CardsPro в том же `rate` (см.
 * App\Models\CardTransaction::upsertFromProvider() и
 * App\Models\MerchantProductRate::recordOutcome()). `rate` становится nullable —
 * своя статистика может появиться раньше, чем `providers:sync-merchant-rates`
 * впервые синхронизирует рейтинг CardsPro для этой пары.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_product_rates', function (Blueprint $table) {
            $table->unsignedInteger('success_count')->default(0)->after('card_product_id');
            $table->unsignedInteger('decline_count')->default(0)->after('success_count');
            $table->decimal('rate', 5, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('merchant_product_rates', function (Blueprint $table) {
            $table->dropColumn(['success_count', 'decline_count']);
            $table->decimal('rate', 5, 4)->nullable(false)->change();
        });
    }
};
