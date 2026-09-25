<?php

namespace App\Services\Integrations\Didit;

use App\Enums\DecisionStatus;
use App\Enums\KycStatus;
use App\Enums\KycVerificationType;
use App\Models\KycVerification;
use App\Models\Setting;
use App\Models\User;
use App\Services\Integrations\Didit\Exceptions\DiditException;

/**
 * Оркестрация верификации личности через Didit (https://docs.didit.me): запуск
 * сессии для пользователя и перевод статусов Didit (10 значений, см.
 * https://docs.didit.me/integration/verification-statuses) в статусы приложения
 * (App\Enums\DecisionStatus — на конкретную попытку KycVerification, App\Enums\KycStatus —
 * итоговый на User). Разбор вебхука — в {@see DiditWebhookHandler}, тот использует
 * методы map*()/declineReasonFor() этого сервиса.
 */
class DiditService
{
    public function __construct(protected DiditClient $client) {}

    /**
     * Заполнены ли учётные данные для запуска верификации.
     */
    public function isEnabled(): bool
    {
        return filled(Setting::get('didit_api_key')) && filled(Setting::get('didit_workflow_id'));
    }

    /**
     * Создаёт сессию верификации в Didit и заводит/обновляет локальную запись
     * KycVerification (type=provider). `vendor_data` — uuid пользователя (тот же
     * публичный идентификатор, что и везде в API ЛК), по нему Didit сам не создаст
     * дубль сессии, если у пользователя уже есть незавершённая (см. «Idempotency»
     * в https://docs.didit.me/integration/api-full-flow).
     *
     * @return array{url: string, session_id: string, status: string}
     */
    public function startVerification(User $user): array
    {
        if (! $this->isEnabled()) {
            throw new DiditException('Верификация личности временно недоступна — обратитесь в поддержку.');
        }

        $session = $this->client->createSession(['vendor_data' => $user->uuid]);

        $sessionId = (string) ($session['session_id'] ?? '');
        $url = (string) ($session['url'] ?? '');
        $status = (string) ($session['status'] ?? 'Not Started');

        if ($sessionId === '' || $url === '') {
            throw new DiditException('Didit не вернул корректную сессию верификации.', 0, $session);
        }

        KycVerification::query()->updateOrCreate(
            ['type' => KycVerificationType::Provider, 'provider_session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'provider' => 'didit',
                'status' => $this->mapVerificationStatus($status),
                'provider_status' => $status,
                'submitted_at' => now(),
            ],
        );

        // Синхронизируем kyc_status сразу по фактическому статусу сессии (на случай, если из-за
        // идемпотентности Didit вернул уже существующую незавершённую сессию в более продвинутом
        // статусе — In Progress/Awaiting User/Resubmitted, см. «Idempotency» в
        // https://docs.didit.me/sessions-api/create-session). Для обычного первого запуска (status=Not
        // Started) это но-оп и не требует записи — у пользователя уже KycStatus::NotStarted по
        // умолчанию, и кнопка запуска верификации в профиле остаётся видимой. Approved не понижаем.
        $mappedStatus = $this->mapUserStatus($status);

        if ($user->kyc_status !== KycStatus::Approved && $user->kyc_status !== $mappedStatus) {
            $user->update(['kyc_status' => $mappedStatus]);
        }

        return ['url' => $url, 'session_id' => $sessionId, 'status' => $status];
    }

    /**
     * Статус конкретной попытки (KycVerification.status) — по значениям
     * DecisionStatus (pending/approved/declined), т.к. колонка общая с внутренними
     * (ручными) проверками. Expired/Abandoned/Kyc Expired считаем неуспешной
     * попыткой (declined) — см. declineReasonFor() для текста причины.
     */
    public function mapVerificationStatus(string $diditStatus): DecisionStatus
    {
        return match ($diditStatus) {
            'Approved' => DecisionStatus::Approved,
            'Declined', 'Expired', 'Abandoned', 'Kyc Expired' => DecisionStatus::Declined,
            default => DecisionStatus::Pending, // Not Started, In Progress, In Review, Resubmitted, Awaiting User
        };
    }

    /**
     * Итоговый статус пользователя (User.kyc_status). В отличие от
     * mapVerificationStatus(), незавершённые/истёкшие попытки (Expired, Abandoned,
     * Kyc Expired) возвращают пользователя в NotStarted, а не Declined — это не
     * отказ по итогам проверки личности, а прерванная/просроченная попытка,
     * пройти которую можно заново без негативного статуса в профиле.
     */
    public function mapUserStatus(string $diditStatus): KycStatus
    {
        return match ($diditStatus) {
            'Not Started' => KycStatus::NotStarted,
            'Approved' => KycStatus::Approved,
            'Declined' => KycStatus::Declined,
            'Expired', 'Abandoned', 'Kyc Expired' => KycStatus::NotStarted,
            default => KycStatus::Pending, // In Progress, In Review, Resubmitted, Awaiting User
        };
    }

    /**
     * Причина, которую увидит пользователь в блоке верификации профиля.
     *
     * @param  array<string, mixed>  $decision
     */
    public function declineReasonFor(string $diditStatus, array $decision): ?string
    {
        return match ($diditStatus) {
            'Declined' => $this->extractDeclineReason($decision) ?? 'Проверка личности не пройдена.',
            'Expired' => 'Сессия верификации истекла — ссылка не была открыта вовремя.',
            'Abandoned' => 'Верификация не была завершена до конца.',
            'Kyc Expired' => 'Срок действия предыдущей верификации истёк.',
            default => null,
        };
    }

    /**
     * Собирает человекочитаемые причины отказа из warnings[] всех отчётов decision —
     * см. https://docs.didit.me/reference/data-models#warning-object (поле
     * short_description). Возвращает null, если warnings не пришли — вызывающий код
     * подставляет обобщённую фразу.
     *
     * @param  array<string, mixed>  $decision
     */
    protected function extractDeclineReason(array $decision): ?string
    {
        $featureArrays = [
            'id_verifications',
            'nfc_verifications',
            'liveness_checks',
            'face_matches',
            'aml_screenings',
            'poa_verifications',
            'phone_verifications',
            'email_verifications',
        ];

        $descriptions = [];

        foreach ($featureArrays as $key) {
            foreach ((array) ($decision[$key] ?? []) as $report) {
                foreach ((array) ($report['warnings'] ?? []) as $warning) {
                    if (! empty($warning['short_description'])) {
                        $descriptions[] = $warning['short_description'];
                    }
                }
            }
        }

        $descriptions = array_values(array_unique($descriptions));

        return $descriptions === [] ? null : implode('; ', $descriptions);
    }
}
