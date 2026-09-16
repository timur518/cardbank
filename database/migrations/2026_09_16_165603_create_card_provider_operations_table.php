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
        Schema::create('card_provider_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('card_providers')->cascadeOnDelete();
            // Для issue карты ещё нет (SAN появляется только в ответе провайдера) — заполняется по факту.
            $table->foreignId('card_id')->nullable()->constrained('cards')->nullOnDelete();
            $table->string('type'); // issue, topup, withdraw, block
            $table->string('request_id')->unique();
            $table->string('docid')->nullable()->index();
            $table->string('status')->default('pending'); // pending, completed, failed
            // Что мы намеревались сделать: для issue — user_id/card_product_id/amount/currency и т.д.,
            // чтобы по приходу подтверждения можно было создать карту, зная её будущего владельца.
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_provider_operations');
    }
};
