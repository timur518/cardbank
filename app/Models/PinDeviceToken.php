<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * «Доверенное устройство» для быстрой разблокировки ЛК по ПИН-коду без пароля, когда обычная
 * Sanctum-сессия истекла (SESSION_LIFETIME) — см. AuthController::deviceStatus()/unlockPin()/
 * forgetDevice(), ProfileController::setPin() (выпуск куки при установке/смене ПИН-кода).
 *
 * Значение куки (COOKIE_NAME) — "{selector}.{validator}": selector публичный (по нему ищем
 * запись), validator секретный и в базе хранится только его sha256-хэш — как в классическом
 * remember-me, чтобы утечка БД сама по себе не давала возможности подделать куку.
 */
class PinDeviceToken extends Model
{
    public const COOKIE_NAME = 'mojno_pin_device';

    public const TTL_DAYS = 30;

    /** После стольких подряд неверных ПИН-кодов токен устройства отзывается — 4-значный ПИН
     *  сам по себе слабый (10 000 комбинаций), доверие устройству не должно позволять подбор. */
    public const MAX_FAILED_ATTEMPTS = 5;

    protected $fillable = [
        'user_id',
        'selector',
        'validator_hash',
        'failed_attempts',
        'expires_at',
        'last_used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Создаёт новую запись доверенного устройства и возвращает готовое значение куки —
     * вызывающий код кладёт его в ответ через Cookie::queue(self::makeCookie($value)).
     */
    public static function issueFor(User $user): string
    {
        $selector = Str::random(20);
        $validator = Str::random(40);

        static::create([
            'user_id' => $user->id,
            'selector' => $selector,
            'validator_hash' => hash('sha256', $validator),
            'expires_at' => now()->addDays(self::TTL_DAYS),
        ]);

        return "{$selector}.{$validator}";
    }

    /**
     * Находит и проверяет токен по значению куки (см. issueFor()) — hash_equals защищает
     * сравнение validator_hash от timing-атак. Возвращает null, если куки нет, формат
     * неверный, запись не найдена/истекла или validator не совпал.
     */
    public static function resolve(?string $token): ?self
    {
        if (! $token || ! str_contains($token, '.')) {
            return null;
        }

        [$selector, $validator] = explode('.', $token, 2);

        /** @var self|null $record */
        $record = static::where('selector', $selector)->where('expires_at', '>', now())->first();

        if (! $record || ! hash_equals($record->validator_hash, hash('sha256', $validator))) {
            return null;
        }

        return $record;
    }

    /**
     * Выдаёт новый validator взамен использованного (sliding-продление на TTL_DAYS дней и сброс
     * счётчика неудачных попыток) — вызывается при каждой успешной разблокировке
     * (AuthController::unlockPin()), чтобы однажды применённое значение куки больше не годилось.
     */
    public function rotate(): string
    {
        $validator = Str::random(40);

        $this->update([
            'validator_hash' => hash('sha256', $validator),
            'failed_attempts' => 0,
            'expires_at' => now()->addDays(self::TTL_DAYS),
            'last_used_at' => now(),
        ]);

        return "{$this->selector}.{$validator}";
    }

    /**
     * Учитывает неверную попытку ввода ПИН-кода; при достижении MAX_FAILED_ATTEMPTS
     * отзывает доверие устройству (удаляет запись). Возвращает true, если токен отозван.
     */
    public function registerFailedAttempt(): bool
    {
        $this->increment('failed_attempts');

        if ($this->failed_attempts >= self::MAX_FAILED_ATTEMPTS) {
            $this->delete();

            return true;
        }

        return false;
    }

    /**
     * Кука со значением токена — те же security-настройки (domain/secure/same_site), что
     * и у обычной сессионной куки Sanctum, но httpOnly (не читается из JS) и с TTL_DAYS днями жизни.
     */
    public static function makeCookie(string $value): Cookie
    {
        return cookie(
            self::COOKIE_NAME,
            $value,
            self::TTL_DAYS * 24 * 60,
            config('session.path'),
            config('session.domain'),
            (bool) config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax'),
        );
    }

    /**
     * Кука для удаления mojno_pin_device (logout, forgetDevice, отзыв по MAX_FAILED_ATTEMPTS).
     */
    public static function forgetCookie(): Cookie
    {
        return \Illuminate\Support\Facades\Cookie::forget(self::COOKIE_NAME, config('session.path'), config('session.domain'));
    }
}
