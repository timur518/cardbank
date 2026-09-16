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
            // Сумма поступления в долларах на момент операции (после конвертации платёжной
            // системой). Нужна, чтобы считать наценку и прибыль в единой валюте, так как
            // клиенты платят в рублях через СБП, а провайдеру карт мы платим в долларах.
            $table->decimal('amount_usd', 14, 2)->nullable()->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('amount_usd');
        });
    }
};
