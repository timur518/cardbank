<?php

namespace App\Models;

use App\Enums\KycStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'referral_code',
        'invite_code',
        'kyc_status',
        'is_blocked',
        'block_reason',
        'two_factor_enabled',
        'pwa_installed',
        'pin_hash',
        'pin_set_at',
        'last_login_at',
        'personal_data_consent_at',
        'bitbanker_offer_accepted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'kyc_status' => KycStatus::class,
            'is_blocked' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'pwa_installed' => 'boolean',
            'pin_set_at' => 'datetime',
            'last_login_at' => 'datetime',
            'personal_data_consent_at' => 'datetime',
            'bitbanker_offer_accepted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->uuid ??= (string) Str::uuid();
            $user->invite_code ??= static::generateUniqueInviteCode();
        });
    }

    /**
     * Собственный код приглашения пользователя (6 символов, например «X71KJN») — им делятся
     * как ?pid=КОД в ссылках-приглашениях. Уникальность гарантируется повторной генерацией
     * при коллизии (см. также бэкфилл в миграции 2026_10_03_100001_add_invite_code_to_users_table).
     */
    public static function generateUniqueInviteCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (static::query()->where('invite_code', $code)->exists());

        return $code;
    }

    public function kycVerifications(): HasMany
    {
        return $this->hasMany(KycVerification::class);
    }

    /**
     * Последняя по времени проверка личности (внутренняя или через провайдера, например
     * Didit) — используется, чтобы показать пользователю причину отказа в ЛК (см. UserResource).
     */
    public function latestKycVerification(): HasOne
    {
        return $this->hasOne(KycVerification::class)->latestOfMany();
    }

    public function riskFlags(): HasMany
    {
        return $this->hasMany(RiskFlag::class);
    }

    public function operatorNotes(): HasMany
    {
        return $this->hasMany(OperatorNote::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Пользователи, зарегистрировавшиеся с кодом приглашения этого пользователя
     * ($this->invite_code в их referral_code) — см. также App\Models\Partner и
     * App\Services\Referral\ReferralService.
     */
    public function referredUsers(): HasMany
    {
        return $this->hasMany(self::class, 'referral_code', 'invite_code');
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    /**
     * «Разрешённые методы оплаты». Для BitBanker запись сюда добавляется/удаляется
     * автоматически (см. BitbankerClientService::syncAllowedPaymentMethod()) и он всегда
     * требует явного попадания в этот список — правило «пустой список = нет
     * ограничения» для него не действует. Для остальных способов список
     * формируется вручную в админке, и пустой список действительно означает
     * отсутствие ограничений. Точная логика — PaymentMethod::isAllowedFor().
     */
    public function allowedPaymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class);
    }

    /**
     * Состояние регистрации этого пользователя в BitBanker (см. BitbankerClientService).
     */
    public function bitbankerClient(): HasOne
    {
        return $this->hasOne(BitbankerClient::class);
    }

    /**
     * Переопределяет одноимённый метод из трейта Notifiable (там это MorphMany на встроенный
     * в Laravel \Illuminate\Notifications\DatabaseNotification, который мы не используем) — у нас
     * своя таблица/модель {@see Notification} для ленты «Уведомления» в ЛК.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Является ли пользователь сотрудником (есть хотя бы одна роль).
     */
    public function isStaff(): bool
    {
        return $this->roles()->exists();
    }

    /**
     * Доступ в Filament-панель (админка). Пользователи с ролью `customer`
     * (присваивается автоматически при регистрации через API личного кабинета,
     * см. AuthController::register()) никогда не проходят, даже если у них верный
     * пароль/сессия.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return ! $this->hasRole('customer');
    }

    /**
     * Есть ли у пользователя активная (не снятая) пометка о риске.
     */
    public function hasActiveRiskFlag(): bool
    {
        return $this->riskFlags()->whereNull('removed_at')->exists();
    }

    /**
     * Установлен ли у клиента 4-значный ПИН-код для быстрого входа в ЛК (PinSetupModal.tsx).
     */
    public function hasPin(): bool
    {
        return ! is_null($this->pin_hash);
    }
}
