<?php

namespace App\Services\Integrations\Bitbanker;

use App\Enums\NotificationEvent;
use App\Models\BitbankerClient;
use App\Models\Notification;
use RuntimeException;

/**
 * Обработка Events Webhook BitBanker — пока единственное известное событие
 * `sbp_client_permission_changed` (смена `is_verified_for_sbp` у уже
 * зарегистрированного клиента, см. BITBANKER_INTEGRATION_PLAN.md раздел 5.5).
 * Формат тела официально не описан в OpenAPI (это push от BitBanker к нам, а не
 * их API), но в примере из документации в `data` есть только
 * `client_id`/`is_verified_for_sbp`/`previous_is_verified_for_sbp` —
 * **`check_status` в самом Events Webhook нет вообще**, он есть только в ответе
 * `POST`/`GET /api/v3/partner-clients`. Поэтому при получении этого события
 * `check_status` проставляется в `completed` явно: сам факт «смены разрешения»
 * означает, что фоновые проверки завершились — иначе, оставь `check_status=pending`
 * как было, `BitbankerClient::isApproved()` никогда не стал бы `true` через один
 * только вебхук, даже когда `is_verified_for_sbp=true`. Источником истины при этом
 * всё равно остаётся `GET /api/v3/partner-clients` (см. BitbankerClientService::refreshStatus()).
 */
class BitbankerEventsWebhookHandler
{
    public function __construct(protected BitbankerClientService $service) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $data = (array) ($payload['data'] ?? $payload);
        $externalClientId = (string) ($data['client_id'] ?? '');

        if ($externalClientId === '' || ! array_key_exists('is_verified_for_sbp', $data)) {
            return;
        }

        $record = BitbankerClient::query()
            ->where('external_client_id', $externalClientId)
            ->first();

        if (! $record) {
            report(new RuntimeException("BitBanker Events Webhook: неизвестный client_id {$externalClientId}."));

            return;
        }

        $wasApproved = $record->isApproved();

        $record->is_verified_for_sbp = (bool) $data['is_verified_for_sbp'];
        $record->check_status = 'completed';
        $record->last_synced_at = now();
        $record->save();

        $this->service->syncAllowedPaymentMethod($record);

        if (! $wasApproved && $record->fresh()->isApproved()) {
            Notification::notify($record->user, NotificationEvent::BitbankerAvailable, [], '/profile');
        }
    }
}
