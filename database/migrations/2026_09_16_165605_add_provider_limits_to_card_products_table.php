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
        Schema::table('card_products', function (Blueprint $table) {
            $table->decimal('issue_min_amount', 12, 2)->nullable()->after('provider_issue_cost_usd');
            $table->decimal('issue_max_amount', 12, 2)->nullable()->after('issue_min_amount');
            $table->decimal('topup_min_amount', 12, 2)->nullable()->after('issue_max_amount');
            $table->decimal('topup_max_amount', 12, 2)->nullable()->after('topup_min_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            $table->dropColumn(['issue_min_amount', 'issue_max_amount', 'topup_min_amount', 'topup_max_amount']);
        });
    }
};
