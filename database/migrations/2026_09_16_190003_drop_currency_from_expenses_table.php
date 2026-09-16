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
        Schema::table('expenses', function (Blueprint $table) {
            // Валюта расхода больше не выбирается отдельно: «Сумма» всегда в рублях,
            // «Сумма в $» — долларовый эквивалент (см. amount_usd).
            $table->dropColumn('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('currency')->nullable()->after('amount');
        });
    }
};
