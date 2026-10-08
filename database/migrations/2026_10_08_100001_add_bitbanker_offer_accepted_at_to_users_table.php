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
        Schema::table('users', function (Blueprint $table) {
            // Когда пользователь принял оферту BitBanker в ЛК (null — не принята).
            // По аналогии с уже существующим personal_data_consent_at — без
            // отдельного boolean-дублёра, см. BITBANKER_INTEGRATION_PLAN.md раздел 4.1.
            $table->timestamp('bitbanker_offer_accepted_at')->nullable()->after('personal_data_consent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bitbanker_offer_accepted_at');
        });
    }
};
