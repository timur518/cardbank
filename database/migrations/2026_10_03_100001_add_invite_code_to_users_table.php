<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Собственный код приглашения каждого пользователя (6 символов, например «X71KJN») —
 * им делятся как ?pid=КОД в ссылках, см. resources/cabinet/src/utils/tracking.ts и
 * resources/js/app.js. В отличие от users.referral_code (код ТОГО, кто пригласил
 * этого пользователя), invite_code — это код САМОГО пользователя для приглашения других.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('invite_code')->nullable()->unique()->after('referral_code');
        });

        // Бэкфилл для уже существующих пользователей — новые получают код автоматически
        // через User::booted() (см. App\Models\User).
        DB::table('users')->whereNull('invite_code')->orderBy('id')->select('id')->chunkById(500, function ($users) {
            foreach ($users as $user) {
                do {
                    $code = strtoupper(Str::random(6));
                } while (DB::table('users')->where('invite_code', $code)->exists());

                DB::table('users')->where('id', $user->id)->update(['invite_code' => $code]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('invite_code');
        });
    }
};
