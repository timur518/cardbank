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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            // Подстрока, по которой мерчант определяется в CardTransaction::merchant,
            // например "AUGMENT CODE" в "AUGMENT CODE           PALO ALTO     USA".
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            // Векторная иконка — только сами <path>, без обёртки <svg> (viewBox 0 0 24 24,
            // fill="currentColor"), как на лендинге в разделе «Что оплатить?».
            $table->text('logo_svg')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
