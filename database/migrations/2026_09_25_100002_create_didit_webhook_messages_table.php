<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Журнал входящих вебхуков Didit (POST /api/webhooks/didit) — тот же паттерн, что
     * и у ProviderMessage/PaymentMethodMessage: сырое тело сохраняется всегда, до
     * разбора, чтобы не терять доставку при сбое обработки. Без внешнего ключа —
     * в отличие от карточного провайдера или способа оплаты, Didit в системе один.
     */
    public function up(): void
    {
        Schema::create('didit_webhook_messages', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->nullable()->index();
            $table->string('webhook_type')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('status')->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('didit_webhook_messages');
    }
};
