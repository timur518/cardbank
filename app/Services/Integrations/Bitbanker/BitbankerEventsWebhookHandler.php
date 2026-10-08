<?php

namespace App\Services\Integrations\Bitbanker;

use App\Enums\NotificationEvent;
use App\Models\BitbankerClient;
use App\Models\Notification;

/**
 * Обработка Events Webhook BitBanker — пока единственное известное событие
 * `sbp_client_permission_changed` (смена `is_verified_for_sbp`/`check_status` у
 * уже зарегистрированного клиента, см. BITBANKER_INTEGRATION_PLAN.md раздел 5.5).
 * Формат тела официально не описан в OpenAPI (это push от BitBanker к нам, а не
 * их API) — читаем поля максимально терпимо и не падаем, если какого-то из них
 * не оказалось: это push-уведомление, а не источник истины (им остаётся
 * `GET /api/v3/partner-clients`, см. BitbankerClientService::refreshStatus()).
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

        if ($externalClientId === '') {
            return;
        }

        $record = BitbankerClient::query()
            ->where('external_client_id', $externalClientId)
            ->first();

        if (! $record) {
            report(new \RuntimeException("BitBanker Events Webhook: неизвестный client_id {$externalClientId}."));

            return;
        }

        $wasApproved = $record->isApproved();

        $record->is_verified_for_sbp = (bool) ($data['is_verified_for_sbp'] ?? $record->is_verified_for_sbp);
        $record->check_status = isset($data['check_status']) ? (string) $data['check_status'] : $record->check_status;
        $record->last_synced_at = now();
        $record->save();

        $this->service->syncAllowedPaymentMethod($record);

        if (! $wasApproved && $record->fresh()->isApproved()) {
            Notification::notify($record->user, NotificationEvent::BitbankerAvailable, [], '/profile');
        }
    }
}
