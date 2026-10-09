<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            $table->boolean('samsung_pay_enabled')->default(false)->after('google_pay_enabled');
        });

        DB::table('card_products')->where('id', 6)->where('key', 'orange')->update([
            'samsung_pay_enabled' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            $table->dropColumn('samsung_pay_enabled');
        });
    }
};
