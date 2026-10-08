<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `settings.value` был `TEXT` (лимит MySQL — 65 535 байт). Для некоторых настроек
 * (например, «Настройки BitBanker» → текст оферты) этого не хватает: длинный
 * текст в кодировке utf8mb4 (кириллица — 2-3 байта на символ, плюс HTML-разметка
 * после перехода на RichEditor) легко превышает лимит, и запись падает с
 * «Data too long for column». LONGTEXT снимает это ограничение (до 4 Гб).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->longText('value')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('value')->nullable()->change();
        });
    }
};
