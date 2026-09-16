<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DiscrepancyType: string implements HasLabel
{
    case BalanceMismatch = 'balance_mismatch';
    case NewProviderProduct = 'new_provider_product';
    case ProviderProductChanged = 'provider_product_changed';
    case ProviderProductMissing = 'provider_product_missing';
    case StuckOperation = 'stuck_operation';

    public function getLabel(): string
    {
        return match ($this) {
            self::BalanceMismatch => 'Расхождение баланса',
            self::NewProviderProduct => 'Новый продукт у провайдера',
            self::ProviderProductChanged => 'У провайдера изменились условия продукта',
            self::ProviderProductMissing => 'Продукт пропал у провайдера',
            self::StuckOperation => 'Операция зависла без ответа провайдера',
        };
    }
}
