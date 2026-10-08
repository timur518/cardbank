<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Состояние регистрации клиента в BitBanker (аналог KycVerification, но для
     * стороны BitBanker). Один клиент BitBanker на пользователя (может быть
     * несколько payment_method — несколько касс BitBanker, как у ParityPay).
     */
    public function up(): void
    {
        Schema::create('bitbanker_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            // = User.uuid, то же значение, что отправлено как client_id в BitBanker.
            $table->string('external_client_id');
            $table->timestamp('registered_at')->nullable();
            $table->boolean('is_verified_for_sbp')->default(false);
            // pending|completed — сырой check_status из ответа BitBanker (/api/v3/partner-clients).
            $table->string('check_status')->nullable();
            // Сырое тело последнего неуспешного HTTP-ответа BitBanker — для разбора
            // оператором (у реального API нет именованных кодов ошибок).
            $table->json('last_error')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitbanker_clients');
    }
};
