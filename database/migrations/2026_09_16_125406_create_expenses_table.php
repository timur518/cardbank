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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('category'); // card_issue, card_topup, penalty, paid_refund, salary, project_upkeep, ad_placement, other
            $table->decimal('amount', 14, 2);
            $table->string('currency');
            $table->foreignId('card_id')->nullable()->constrained('cards')->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('card_providers')->nullOnDelete();
            // Таблица ad_placements появится в разделе «Маркетинг» — внешний ключ добавим отдельной миграцией позже.
            $table->unsignedBigInteger('ad_placement_id')->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
