<?php

namespace App\Services\Integrations\Bitbanker;

use App\Enums\DecisionStatus;
use App\Enums\KycStatus;
use App\Enums\KycVerificationType;
use App\Models\BitbankerClient as BitbankerClientRecord;
use App\Models\KycVerification;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Integrations\Bitbanker\Exceptions\BitbankerException;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * Регистрация пользователя в BitBanker (`POST`/`GET /api/v2/partner-clients`) и
 * единая синхронизация «Разрешённых методов оплаты»
 * (User::allowedPaymentMethods()) по её результату — вызывается и при первой
 * регистрации, и при повторном опросе статуса (фоновая команда
 * bitbanker:sync-client-status), и из обработчика вебхука событий BitBanker
 * (BitbankerEventsWebhookHandler).
 */
class BitbankerClientService
{
    private const PARTNER_CLIENTS_PATH = '/api/v2/partner-clients';

    /**
     * Регистрирует/обновляет пользователя в BitBanker. Бросает BitbankerException
     * с понятным текстом, если данных недостаточно или сам вызов API
     * завершился ошибкой — в обоих случаях вызывающий код (BitbankerController)
     * должен вернуть клиенту 422.
     */
    public function register(User $user, PaymentMethod $paymentMethod): BitbankerClientRecord
    {
        $payload = $this->buildPayload($user);

        $record = BitbankerClientRecord::firstOrNew(['user_id' => $user->id]);
        $record->payment_method_id = $paymentMethod->id;
        $record->external_client_id = $user->uuid;

        $client = new BitbankerClient($paymentMethod);

        try {
            $response = $client->postSigned(self::PARTNER_CLIENTS_PATH, $payload, (string) Str::uuid());
        } catch (BitbankerException $e) {
            $record->last_error = $e->responseBody() ?: ['message' => $e->getMessage()];
            $record->last_synced_at = now();
            $record->save();

            report($e);

            throw new BitbankerException('Не удалось зарегистрировать вас в BitBanker — обратитесь в поддержку.');
        }

        $record->registered_at ??= now();
        $this->applyStatus($record, $response);

        return $record;
    }

    /**
     * Переопрашивает текущий статус уже зарегистрированного клиента
     * (`GET /api/v2/partner-clients`) — используется фоновой командой
     * `bitbanker:sync-client-status` и кнопкой «Обновить статус» в админке.
     * Ошибки HTTP не пробрасываются — только логируются в `last_error`, чтобы
     * не прерывать пакетный опрос по всем клиентам.
     */
    public function refreshStatus(BitbankerClientRecord $record): void
    {
        $client = new BitbankerClient($record->paymentMethod);

        try {
            $response = $client->getSigned(self::PARTNER_CLIENTS_PATH, [
                'client_id' => $record->external_client_id,
            ]);
        } catch (BitbankerException $e) {
            $record->last_error = $e->responseBody() ?: ['message' => $e->getMessage()];
            $record->last_synced_at = now();
            $record->save();

            report($e);

            return;
        }

        $this->applyStatus($record, $response);
    }

    /**
     * Единая точка синхронизации «Разрешённых методов оплаты» для BitBanker —
     * вызывается из register()/refreshStatus() этого сервиса и из
     * BitbankerEventsWebhookHandler.
     */
    public function syncAllowedPaymentMethod(BitbankerClientRecord $record): void
    {
        $user = $record->user;
        $methodId = $record->payment_method_id;

        $isAttached = $user->allowedPaymentMethods()->where('payment_methods.id', $methodId)->exists();

        if ($record->isApproved()) {
            if (! $isAttached) {
                $user->allowedPaymentMethods()->attach($methodId);
            }
        } elseif ($isAttached) {
            $user->allowedPaymentMethods()->detach($methodId);
        }
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function applyStatus(BitbankerClientRecord $record, array $response): void
    {
        $record->is_verified_for_sbp = (bool) ($response['is_verified_for_sbp'] ?? false);
        $record->check_status = isset($response['check_status']) ? (string) $response['check_status'] : null;
        $record->last_error = null;
        $record->last_synced_at = now();
        $record->save();

        $this->syncAllowedPaymentMethod($record);
    }

    /**
     * Проверка полноты данных и сборка payload для `/api/v2/partner-clients`.
     * Бросает BitbankerException с понятным текстом при первом же несоответствии.
     *
     * @return array<string, mixed>
     */
    private function buildPayload(User $user): array
    {
        if ($user->kyc_status !== KycStatus::Approved) {
            throw new BitbankerException('Для подключения BitBanker нужно сначала пройти верификацию личности.');
        }

        if (blank($user->first_name) || blank($user->last_name) || blank($user->date_of_birth)
            || blank($user->phone) || blank($user->email)) {
            throw new BitbankerException('Не заполнены обязательные данные профиля — обратитесь в поддержку.');
        }

        $identityCard = $this->identityCardData($user);

        if ($identityCard === null) {
            throw new BitbankerException('Для BitBanker нужна верификация по внутреннему паспорту РФ — пройдите верификацию личности ещё раз, загрузив паспорт гражданина РФ.');
        }

        $documentNumber = preg_replace('/[^0-9]/', '', (string) ($identityCard['document_number'] ?? ''));
        $issueDate = $this->toDmy($identityCard['date_of_issue'] ?? null);
        $issuingState = (string) ($identityCard['issuing_state'] ?? '');

        if ($documentNumber === '' || $issueDate === null || $issuingState !== 'RUS') {
            throw new BitbankerException('Паспортные данные из верификации личности неполны — обратитесь в поддержку.');
        }

        $firstNameNative = (string) ($identityCard['extra_fields']['first_name_non_latin'] ?? '');
        $lastNameNative = (string) ($identityCard['extra_fields']['last_name_non_latin'] ?? '');

        if ($firstNameNative === '' || $lastNameNative === '') {
            throw new BitbankerException('Не удалось получить ФИО из верификации личности — обратитесь в поддержку.');
        }

        return [
            'client_id' => $user->uuid,
            'email' => $user->email,
            'phone' => $user->phone,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'first_name_native' => $firstNameNative,
            'last_name_native' => $lastNameNative,
            'patronymic' => $user->middle_name,
            'birth_date' => $this->toDmy($user->date_of_birth),
            'passport' => $documentNumber,
            'passport_issue_date' => $issueDate,
            'country_of_passport_issue' => $issuingState,
        ];
    }

    /**
     * Находит элемент `id_verifications[]` с `document_type === "Identity Card"`
     * (внутренний паспорт РФ, а не загранпаспорт) в последней одобренной
     * провайдерской верификации Didit.
     *
     * @return array<string, mixed>|null
     */
    private function identityCardData(User $user): ?array
    {
        $verification = KycVerification::query()
            ->where('user_id', $user->id)
            ->where('type', KycVerificationType::Provider)
            ->where('provider', 'didit')
            ->where('status', DecisionStatus::Approved)
            ->latest('resolved_at')
            ->first();

        if (! $verification) {
            return null;
        }

        $idVerifications = (array) ($verification->provider_response['id_verifications'] ?? []);

        foreach ($idVerifications as $entry) {
            if (is_array($entry) && ($entry['document_type'] ?? null) === 'Identity Card') {
                return $entry;
            }
        }

        return null;
    }

    private function toDmy(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            return Carbon::parse((string) $date)->format('d.m.Y');
        } catch (Throwable) {
            return null;
        }
    }
}
