<?php

namespace App\Models;

use App\Enums\KycStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'kyc_status',
        'is_blocked',
        'block_reason',
        'two_factor_enabled',
        'last_login_at',
        'personal_data_consent_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'last_login_at' => 'datetime',
            'personal_data_consent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->uuid ??= (string) Str::uuid();
        });
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

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
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
}
