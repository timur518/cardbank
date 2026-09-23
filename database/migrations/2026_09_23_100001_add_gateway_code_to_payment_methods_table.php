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
            // Какая конкретно интеграция (App\Services\Payments\PaymentGatewayResolver) обслуживает
            // этот способ оплаты — 'type' ниже лишь категория для UI (шлюз/крипта/карта/кошелёк) и
            // не привязана к конкретному провайдеру. null (или неизвестный код) — используется
            // StubPaymentGateway, см. PaymentGatewayResolver::for().
            $table->string('gateway_code')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('gateway_code');
        });
    }
};
