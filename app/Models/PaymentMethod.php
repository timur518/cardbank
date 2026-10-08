<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use App\Enums\PaymentGatewayCode;
use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'gateway_code',
        'sandbox_mode',
        'currency',
        'status',
        'requires_kyc',
        'fee_percent',
        'min_amount',
        'max_amount',
        'settlement_config',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'gateway_code' => PaymentGatewayCode::class,
            'sandbox_mode' => 'boolean',
            'status' => ActiveStatus::class,
            'requires_kyc' => 'boolean',
            'settlement_config' => 'array',
            'fee_percent' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(PaymentMethodMessage::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PaymentMethodUsage::class);
    }

    /**
     * Пользователи, у которых этот способ оплаты явно в списке разрешённых
     * (см. User::allowedPaymentMethods()).
     */
    public function allowedForUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Клиенты BitBanker, зарегистрированные через эту кассу BitBanker — на случай
     * нескольких касс BitBanker, как у ParityPay.
     */
    public function bitbankerClients(): HasMany
    {
        return $this->hasMany(BitbankerClient::class);
    }

    /**
     * Доступен ли этот способ оплаты пользователю — общая проверка для
     * PaymentMethodController::index() и валидации payment_method_id в
     * IssueOrderRequest/TopupOrderRequest.
     *
     * Для BitBanker (gateway_code=bitbanker) правило «список пуст — не ограничен»
     * НЕ действует: он требует явного попадания в allowedPaymentMethods (успешная
     * регистрация+верификация, см. BitbankerClientService::syncAllowedPaymentMethod()),
     * иначе способ был бы доступен всем ещё до прохождения KYC, принятия
     * оферты и успешной регистрации — именно так ограничивается доступ к BitBanker.
     * Для гостя (user=null) BitBanker всегда недоступен.
     *
     * Для прочих способов оплаты (ParityPay и т.д.) действует старое правило:
     * список разрешённых формируется вручную в админке, пустой список означает
     * отсутствие ограничения (видны все активные способы). Важно: автоматически
     * добавленная запись BitBanker в allowedPaymentMethods не считается «ручным
     * ограничением» для этой проверки — иначе одобрение BitBanker для пользователя
     * случайно скрывало бы ему все остальные ранее неограниченные способы оплаты.
     *
     * $allowedMethods можно передать заранее полученной коллекцией моделей PaymentMethod
     * из user.allowedPaymentMethods, чтобы не делать запрос на каждый способ оплаты в списке.
     */
    public function isAllowedFor(?User $user, ?Collection $allowedMethods = null): bool
    {
        if (! $user) {
            return $this->gateway_code !== PaymentGatewayCode::Bitbanker;
        }

        $allowedMethods ??= $user->allowedPaymentMethods()->get();

        if ($this->gateway_code === PaymentGatewayCode::Bitbanker) {
            return $allowedMethods->contains('id', $this->id);
        }

        $manualAllowedMethods = $allowedMethods->where('gateway_code', '!=', PaymentGatewayCode::Bitbanker);

        if ($manualAllowedMethods->isEmpty()) {
            return true;
        }

        return $manualAllowedMethods->contains('id', $this->id);
    }
}
