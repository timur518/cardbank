<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * «Доверенные устройства» для быстрой разблокировки ЛК по ПИН-коду без пароля, когда
     * обычная Sanctum-сессия истекла — см. App\Models\PinDeviceToken.
     */
    public function up(): void
    {
        Schema::create('pin_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Публичная часть значения куки — по ней ищем запись; секретная часть (validator)
            // в базе не хранится, только её sha256-хэш (validator_hash).
            $table->string('selector', 40)->unique();
            $table->string('validator_hash', 64);
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pin_device_tokens');
    }
};
