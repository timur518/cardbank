<?php

namespace App\Services\Integrations\Didit;

use App\Enums\DecisionStatus;
use App\Enums\KycVerificationType;
use App\Enums\NotificationEvent;
use App\Models\KycVerification;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Http\Request;
use stdClass;

/**
 * Разбор вебхуков Didit (status.updated/data.updated) — см.
 * https://docs.didit.me/integration/webhooks. Проверяет подпись X-Signature-V2,
 * находит KycVerification по provider_session_id и синхронизирует статус попытки
 * и итоговый User.kyc_status, отправляя уведомление только при реальной смене
 * статуса (защита от повторной доставки — Didit повторяет вебхук до 2 раз при
 * отсутствии ответа 2xx, event_id при этом переиспользуется).
 */
class DiditWebhookHandler
{
    public function __construct(protected DiditService $service) {}

    /**
     * Проверка подписи X-Signature-V2 — HMAC-SHA256 от канонической пересборки JSON
     * (ключи рекурсивно отсортированы, JSON_UNESCAPED_SLASHES|UNESCAPED_UNICODE).
     * Раскодируем НЕ ассоциативно (json_decode(..., false)) — иначе пустые JSON-объекты
     * ({}) станут пустыми массивами ([]) и пересобранная строка перестанет совпадать
     * с тем, что подписал Didit. См. пример на PHP/Laravel в
     * https://docs.didit.me/integration/webhooks#php-laravel-recommended-x-signature-v2.
     * Отклоняет запросы старше 5 минут (X-Timestamp) — защита от replay-атак.
     */
    public function verifySignature(Request $request): bool
    {
        $secret = (string) Setting::get('didit_webhook_secret', '');
        $timestamp = $request->header('X-Timestamp');
        $signature = $request->header('X-Signature-V2');

        if ($secret === '' || ! $timestamp || ! $signature) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $decoded = json_decode($request->getContent(), false);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        $canonical = json_encode($this->canonicalize($decoded), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash_equals(hash_hmac('sha256', (string) $canonical, $secret), $signature);
    }

    /**
     * Рекурсивно сортирует ключи JSON-объектов (SORT_STRING — побайтово, как
     * Python sort_keys) и оставляет массивы как есть — те же правила, что и в
     * PHP-примере Didit.
     */
    protected function canonicalize(mixed $value): mixed
    {
        if ($value instanceof stdClass) {
            $props = get_object_vars($value);
            ksort($props, SORT_STRING);
            $sorted = new stdClass;

            foreach ($props as $key => $item) {
                $sorted->{$key} = $this->canonicalize($item);
            }

            return $sorted;
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->canonicalize($item), $value);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $webhookType = $payload['webhook_type'] ?? null;

        // data.updated (правки ревьюера) обрабатываем так же, как status.updated —
        // остальные типы (entity/transaction) сейчас не подписаны ни на одном destination.
        if (! in_array($webhookType, ['status.updated', 'data.updated'], true)) {
            return;
        }

        $sessionId = (string) ($payload['session_id'] ?? '');

        if ($sessionId === '') {
            return;
        }

        $verification = KycVerification::query()
            ->where('type', KycVerificationType::Provider)
            ->where('provider_session_id', $sessionId)
            ->first();

        if (! $verification) {
            report(new \RuntimeException("Didit: вебхук для неизвестной сессии {$sessionId}."));

            return;
        }

        $diditStatus = (string) ($payload['status'] ?? '');
        $decision = (array) ($payload['decision'] ?? []);

        $newStatus = $this->service->mapVerificationStatus($diditStatus);
        $declineReason = $newStatus === DecisionStatus::Declined
            ? $this->service->declineReasonFor($diditStatus, $decision)
            : null;

        $statusChanged = $verification->status !== $newStatus;

        $verification->fill([
            'status' => $newStatus,
            'provider_status' => $diditStatus,
            'provider_response' => $decision !== [] ? $decision : $verification->provider_response,
            'decline_reason' => $declineReason,
        ]);

        if ($newStatus !== DecisionStatus::Pending && ! $verification->resolved_at) {
            $verification->resolved_at = now();
        }

        $verification->save();

        $user = $verification->user;

        if (! $user) {
            return;
        }

        $newUserStatus = $this->service->mapUserStatus($diditStatus);
        $user->update(['kyc_status' => $newUserStatus]);

        if (! $statusChanged) {
            // Повторная доставка того же вебхука — уведомление уже отправлено при первой обработке.
            return;
        }

        if ($newStatus === DecisionStatus::Approved) {
            Notification::notify($user, NotificationEvent::KycApproved, [], '/profile');
        } elseif ($newStatus === DecisionStatus::Declined) {
            Notification::notify($user, NotificationEvent::KycDeclined, ['reason' => $declineReason], '/profile');
        }
    }
}
