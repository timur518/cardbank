<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Тип асинхронной операции, которую мы инициировали у провайдера карт
 * (выпуск/пополнение/вывод/блокировка) и ждём финального статуса —
 * через вебхук или через опрос {@see \App\Console\Commands\Providers\SyncPendingOperations}.
 */
enum CardProviderOperationType: string implements HasLabel
{
    case Issue = 'issue';
    case Topup = 'topup';
    case Withdraw = 'withdraw';
    case Block = 'block';

    public function getLabel(): string
    {
        return match ($this) {
            self::Issue => 'Выпуск карты',
            self::Topup => 'Пополнение',
            self::Withdraw => 'Вывод средств',
            self::Block => 'Блокировка',
        };
    }
}
