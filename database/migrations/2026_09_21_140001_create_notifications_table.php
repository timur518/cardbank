<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * История локальных уведомлений для ленты «Уведомления» в личном кабинете.
     * Пока только in-app: заполняется вручную/сервисом записи (следующий шаг),
     * без привязки к push/email — под них при необходимости добавим отдельные
     * поля доставки позже, не трогая эту таблицу целиком.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            // uuid, а не сквозной id — отдаётся в API ЛК, чтобы не раскрывать количество
            // уведомлений в базе (тот же паттерн, что у cards/card_transactions).
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('title');
            $table->text('body')->nullable();
            // Доп. параметры для дип-линка/шаблонизации (card_id, transaction_id, amount и т.п.).
            $table->json('data')->nullable();
            // Куда ведёт клик по уведомлению в ЛК — маршрут SPA или внешняя ссылка.
            $table->string('action_url')->nullable();
            // NULL — не прочитано. Ставится при открытии уведомления в ЛК.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
