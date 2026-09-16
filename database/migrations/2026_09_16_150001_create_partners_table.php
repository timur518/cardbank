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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('invite_link')->nullable();
            $table->unsignedInteger('referrals_count')->default(0);
            $table->unsignedInteger('paying_count')->default(0);
            $table->decimal('available_usd', 12, 2)->default(0);
            $table->decimal('hold_usd', 12, 2)->default(0);
            $table->decimal('requested_usd', 12, 2)->default(0);
            $table->decimal('paid_usd', 12, 2)->default(0);
            $table->decimal('lifetime_usd', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
