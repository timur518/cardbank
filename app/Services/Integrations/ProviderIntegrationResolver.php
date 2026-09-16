<?php

namespace App\Services\Integrations;

use App\Models\CardProvider;
use App\Services\Integrations\CardsPro\CardsProService;
use App\Services\Integrations\Contracts\CardProviderIntegration;

/**
 * Единственное место, которое знает, какая интеграция обслуживает конкретного
 * провайдера. Сегодня подключён только CardsPro, поэтому резолвер всегда отдаёт
 * {@see CardsProService} — но фоновые команды синхронизации вызывают только этот
 * метод и интерфейс {@see CardProviderIntegration}, поэтому подключение второго
 * реального провайдера сведётся к добавлению одной ветки `match` здесь, без
 * изменений в командах.
 */
class ProviderIntegrationResolver
{
    public static function for(CardProvider $provider): CardProviderIntegration
    {
        return match (true) {
            // CardsPro — единственная подключённая интеграция на сегодня.
            default => CardsProService::for($provider),
        };
    }
}
