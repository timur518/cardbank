<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Pivot «Разрешённые методы оплаты» — пустой список у пользователя означает
     * «без ограничений» (видны все активные способы), см. BITBANKER_INTEGRATION_PLAN.md
     * раздел 4.2. Для BitBanker запись сюда добавляется/удаляется автоматически кодом
     * (BitbankerClientService::syncAllowedPaymentMethod()), для остальных способов — вручную в админке.
     */
    public function up(): void
    {
        Schema::create('payment_method_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'payment_method_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_user');
    }
};
