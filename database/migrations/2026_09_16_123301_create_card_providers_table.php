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
        Schema::create('card_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('status')->default('active'); // active, inactive
            $table->string('environment')->default('sandbox'); // sandbox, production
            $table->string('api_base_url')->nullable();
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->json('issue_fee_tiers')->nullable();
            $table->decimal('topup_fee_percent', 5, 2)->default(0);
            $table->json('extra_channel_fees')->nullable();
            $table->decimal('min_topup_usd', 12, 2)->default(0);
            $table->decimal('reserve_balance_usd', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_providers');
    }
};
