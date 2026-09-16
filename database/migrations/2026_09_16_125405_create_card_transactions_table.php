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
        Schema::create('card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // purchase, topup, fee, refund, decline
            $table->decimal('amount', 14, 2);
            $table->string('currency');
            $table->string('merchant')->nullable();
            $table->string('status');
            $table->text('decline_reason')->nullable();
            $table->string('provider_tx_id')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_transactions');
    }
};
