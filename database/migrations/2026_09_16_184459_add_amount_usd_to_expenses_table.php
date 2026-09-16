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
            // Сумма расхода в долларах на момент операции — чтобы расходы, оплаченные
            // в рублях (зарплаты, реклама и т.д.), можно было сложить в одном P&L
            // вместе с расходами провайдерам, которые уже идут в долларах.
            $table->decimal('amount_usd', 14, 2)->nullable()->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('amount_usd');
        });
    }
};
