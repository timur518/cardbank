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
        Schema::create('provider_discrepancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('card_providers')->cascadeOnDelete();
            $table->string('type');
            $table->decimal('expected_amount', 14, 2)->nullable();
            $table->decimal('actual_amount', 14, 2)->nullable();
            $table->foreignId('card_id')->nullable()->constrained('cards')->nullOnDelete();
            $table->string('status')->default('open'); // open, resolved
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_discrepancies');
    }
};
