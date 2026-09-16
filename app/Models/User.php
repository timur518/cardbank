<?php

namespace App\Models;

use App\Enums\KycStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
        ];
    }

    public function kycVerifications(): HasMany
    {
        return $this->hasMany(KycVerification::class);
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
     * Является ли пользователь сотрудником (есть хотя бы одна роль).
     */
    public function isStaff(): bool
    {
        return $this->roles()->exists();
    }

    /**
     * Есть ли у пользователя активная (не снятая) пометка о риске.
     */
    public function hasActiveRiskFlag(): bool
    {
        return $this->riskFlags()->whereNull('removed_at')->exists();
    }
}
