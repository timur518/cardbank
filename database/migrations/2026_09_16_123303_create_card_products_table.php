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
        Schema::create('card_products', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('skin')->nullable();
            $table->string('currency');
            $table->foreignId('provider_id')->constrained('card_providers')->cascadeOnDelete();
            $table->string('provider_product_code')->nullable();
            $table->boolean('provider_kyc_required')->default(false);
            $table->decimal('provider_issue_cost_usd', 12, 2)->default(0);
            $table->decimal('price_rub', 12, 2)->default(0);
            $table->boolean('wallet_enabled')->default(false);
            $table->string('wallet_activation')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_region')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_post_code')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('coming_soon')->default(false);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_products');
    }
};
