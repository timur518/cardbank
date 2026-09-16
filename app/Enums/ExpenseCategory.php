<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExpenseCategory: string implements HasLabel
{
    case CardIssue = 'card_issue';
    case CardTopup = 'card_topup';
    case Penalty = 'penalty';
    case PaidRefund = 'paid_refund';
    case Salary = 'salary';
    case ProjectUpkeep = 'project_upkeep';
    case AdPlacement = 'ad_placement';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::CardIssue => 'Выпуск карты',
            self::CardTopup => 'Пополнение карты',
            self::Penalty => 'Штраф',
            self::PaidRefund => 'Платный возврат',
            self::Salary => 'Зарплата',
            self::ProjectUpkeep => 'Содержание проекта',
            self::AdPlacement => 'Рекламное размещение',
            self::Other => 'Прочие',
        };
    }
}
