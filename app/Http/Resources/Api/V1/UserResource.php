<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Клиенту отдаём uuid, а не сквозной users.id — чтобы не раскрывать
            // наружу общее количество зарегистрированных пользователей.
            'id' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'date_of_birth' => optional($this->date_of_birth)->format('Y-m-d'),
            'kyc_status' => $this->kyc_status?->value,
            // Заполнено только когда последняя проверка отклонена — блок верификации в ЛК показывает это как пояснение под статусом.
            'kyc_decline_reason' => $this->relationLoaded('latestKycVerification')
                ? $this->latestKycVerification?->decline_reason
                : null,
            'two_factor_enabled' => (bool) $this->two_factor_enabled,
            'referral_code' => $this->referral_code,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
