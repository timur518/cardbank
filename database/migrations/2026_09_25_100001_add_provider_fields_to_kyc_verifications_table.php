<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Поля для проверок с type=provider (см. App\Enums\KycVerificationType) — сейчас
     * единственный провайдер это Didit (https://docs.didit.me), см.
     * App\Services\Integrations\Didit. Для type=internal (ручная проверка админом)
     * эти поля остаются пустыми.
     */
    public function up(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('type');
            $table->string('provider_session_id')->nullable()->unique()->after('provider');
            $table->string('provider_status')->nullable()->after('provider_session_id');
            $table->json('provider_response')->nullable()->after('provider_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_session_id', 'provider_status', 'provider_response']);
        });
    }
};
