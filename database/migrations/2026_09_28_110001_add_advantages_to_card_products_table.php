<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            // Маркированный список преимуществ карты (HTML из RichEditor, только bulletList) —
            // выводится на лендинге в блоке #products и на шаге выбора карты в ЛК
            // (CardProductChoice), чтобы не редактировать эти строки вручную в коде.
            $table->text('advantages')->nullable()->after('description');
        });

        // Заполняем тем же пунктами, что были зашиты в коде лендинга (welcome.blade.php) до этого
        // поля — чтобы лендинг не остался без преимуществ до того, как их отредактируют в админке.
        $defaults = [
            'black' => ['Для онлайн покупок', 'Пополнение от 25$', 'Обслуживание - бесплатно'],
            'orange' => ['Поддерживает Apple Pay и Google Pay', 'Пополнение от 25$', 'Можно платить в магазинах и кафе'],
            'white' => ['Apple Pay, Google Pay, Samsung Pay', 'Пополнение от 10$', 'Для онлайна и для оффлайна'],
        ];

        foreach ($defaults as $key => $points) {
            $html = '<ul>'.collect($points)->map(fn (string $point) => '<li><p>'.e($point).'</p></li>')->implode('').'</ul>';

            DB::table('card_products')->where('key', $key)->whereNull('advantages')->update(['advantages' => $html]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_products', function (Blueprint $table) {
            $table->dropColumn('advantages');
        });
    }
};
