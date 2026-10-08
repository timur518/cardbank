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
            // Доп. поля ответа PaymentGatewayContract::initiate(), которых нет в виде
            // отдельных колонок (qr_code, fallback_url — см. BitbankerGateway) —
            // по той же причине, что и payment_url выше: при повторном идемпотентном
            // запросе на /orders/issue|/orders/topup их нужно вернуть клиенту снова,
            // не вызывая initiate() ещё раз.
            $table->json('payment_extra')->nullable()->after('payment_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('payment_extra');
        });
    }
};
