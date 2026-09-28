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
        Schema::table('payment_methods', function (Blueprint $table) {
            // Режим песочницы: способ оплаты принимает платежи и обрабатывает вебхуки как
            // обычно, но фактический выпуск/пополнение карты у провайдера (CardsPro и т.д.)
            // не выполняется — вместо этого сохраняются случайные тестовые реквизиты
            // (см. App\Services\Payments\SandboxOrderProcessor).
            $table->boolean('sandbox_mode')->default(false)->after('gateway_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('sandbox_mode');
        });
    }
};
