<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // uuid, а не сквозной id — аналогично CardResource/CardTransactionResource.
            'id' => $this->uuid,
            'type' => $this->type->value,
            'title' => $this->title,
            'body' => $this->body,
            // Маршрут SPA (например /cards/{uuid}) или внешняя ссылка — задаётся в админке;
            // null, если уведомление не кликабельно.
            'action_url' => $this->action_url,
            'is_read' => $this->read_at !== null,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
