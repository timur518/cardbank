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
            // Ссылка на оплату, которую вернул PaymentGatewayContract::initiate(), сохраняется
            // сразу при создании заказа. До этого поля OrderController «угадывал» ссылку по
            // шаблону StubPaymentGateway при повторном идемпотентном запросе — для реальных
            // платёжных систем (CardLink и т.д.) формат ссылки провайдер-специфичен и не
            // восстанавливается по одному только payment_transaction_id.
            $table->string('payment_url')->nullable()->after('payment_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('payment_url');
        });
    }
};
