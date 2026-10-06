<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Добавляет поддержку общих уведомлений «для всех пользователей» (рассылка одним
 * объявлением вместо записи на каждого): `notifications.user_id` становится
 * nullable — NULL означает «без конкретного получателя, видно всем», см.
 * App\Models\Notification::notify()/scopeVisibleTo().
 *
 * У обычного (персонального) уведомления прочтение — это просто `read_at` в самой
 * строке notifications. Для общего уведомления так сделать нельзя: `read_at` общий
 * на всех, и прочтение одним пользователем пометило бы уведомление прочитанным
 * для всех остальных. Поэтому прочтение общих уведомлений хранится отдельно —
 * по одной строке на каждого прочитавшего пользователя в notification_reads.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite не умеет снимать NOT NULL с внешнего ключа через ->change() без
        // пересборки таблицы в большинстве версий Doctrine DBAL, но в этом проекте
        // DBAL не подключен (см. отсутствие doctrine/dbal в composer.json) — поэтому
        // вместо ->nullable()->change() обновляем SQLite-схему через raw-пересборку,
        // аналогично 2026_09_20_130001_reorder_card_transactions_columns_and_add_updated_at.php,
        // а на MySQL/Postgres используем обычный ->change().
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // Защита от повторного запуска миграции (например, после сбоя на предыдущем
            // прогоне до down()): Schema::dropIfExists('notifications_reordered') удаляет таблицу, но
            // именные индексы/уникальные ограничения с этим префиксом остаются в sqlite_master
            // и ломают повторный CREATE TABLE — дропаем их явно.
            self::dropReorderedNotificationsTable();

            Schema::create('notifications_reordered', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                // nullable — NULL = уведомление без получателя (видно всем пользователям).
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('type')->index();
                $table->string('title');
                $table->text('body')->nullable();
                $table->json('data')->nullable();
                $table->string('action_url')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['user_id', 'created_at']);
                $table->index(['user_id', 'read_at']);
            });

            DB::statement(<<<'SQL'
                INSERT INTO notifications_reordered
                    (id, uuid, user_id, type, title, body, data, action_url, read_at, created_at, updated_at)
                SELECT
                    id, uuid, user_id, type, title, body, data, action_url, read_at, created_at, updated_at
                FROM notifications
            SQL);

            Schema::drop('notifications');
            Schema::rename('notifications_reordered', 'notifications');
        } else {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table('notifications', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->change();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at');

            $table->unique(['notification_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reads');

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // Любые NULL в user_id (общие уведомления) были бы недопустимы в старой схеме —
            // удаляем их перед восстановлением NOT NULL, чтобы down() не падал на реальных
            // данных рассылок.
            DB::table('notifications')->whereNull('user_id')->delete();

            self::dropReorderedNotificationsTable();

            Schema::create('notifications_reordered', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type')->index();
                $table->string('title');
                $table->text('body')->nullable();
                $table->json('data')->nullable();
                $table->string('action_url')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['user_id', 'created_at']);
                $table->index(['user_id', 'read_at']);
            });

            DB::statement(<<<'SQL'
                INSERT INTO notifications_reordered
                    (id, uuid, user_id, type, title, body, data, action_url, read_at, created_at, updated_at)
                SELECT
                    id, uuid, user_id, type, title, body, data, action_url, read_at, created_at, updated_at
                FROM notifications
            SQL);

            Schema::drop('notifications');
            Schema::rename('notifications_reordered', 'notifications');
        } else {
            DB::table('notifications')->whereNull('user_id')->delete();

            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table('notifications', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    /**
     * Полная очистка следов временной таблицы notifications_reordered (сама таблица +
     * её именованные индексы) перед повторным Schema::create — на SQLite индексы не
     * исчезают вместе с Schema::dropIfExists($table) из sqlite_master, если ранее
     * миграция уже успела дойти до создания индексов на предыдущем (упавшем) прогоне.
     */
    private static function dropReorderedNotificationsTable(): void
    {
        foreach ([
            'notifications_reordered_user_id_created_at_index',
            'notifications_reordered_user_id_read_at_index',
            'notifications_reordered_uuid_unique',
            'notifications_reordered_type_index',
        ] as $index) {
            DB::statement("DROP INDEX IF EXISTS {$index}");
        }

        Schema::dropIfExists('notifications_reordered');
    }
};
