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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_product_id')->constrained('card_products')->restrictOnDelete();
            $table->foreignId('provider_id')->constrained('card_providers')->restrictOnDelete();
            $table->string('provider_card_id')->nullable();
            $table->string('card_number')->nullable();
            $table->string('expiry')->nullable();
            $table->string('cvv')->nullable();
            $table->string('currency');
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('fee_debt', 12, 2)->default(0);
            $table->decimal('price_rub', 12, 2)->default(0);
            $table->decimal('issue_cost_usd', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, active, frozen, closed
            $table->string('billing_country')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_region')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_post_code')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('balance_checked_at')->nullable();
            $table->timestamp('history_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
