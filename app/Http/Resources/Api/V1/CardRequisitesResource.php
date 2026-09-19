<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Полные реквизиты карты (номер целиком и CVV) — в отличие от CardResource/
 * CardDetailResource, отдаются только по отдельному запросу (см.
 * CardController::requisites()), чтобы фронтенд запрашивал их лишь по явному
 * действию клиента («Показать реквизиты» / «Показать CVV»), а не при каждой
 * загрузке страницы карты. Нужны клиенту, чтобы реально расплачиваться этой
 * виртуальной картой в интернете — это не PCI DSS зона провайдера (в отличие от
 * Apple Pay/Google Pay провижининга), а обычные реквизиты для ручного ввода на
 * сайте оплаты.
 *
 * @mixin \App\Models\Card
 */
class CardRequisitesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'card_number' => $this->formatCardNumber((string) $this->card_number),
            'expiry' => $this->expiry,
            'cvv' => $this->cvv,
        ];
    }

    private function formatCardNumber(string $number): ?string
    {
        if ($number === '') {
            return null;
        }

        return trim(chunk_split($number, 4, ' '));
    }
}
