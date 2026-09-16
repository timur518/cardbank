<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LegalDocumentType: string implements HasLabel
{
    case TermsOfUse = 'terms_of_use';
    case PrivacyPolicy = 'privacy_policy';
    case RefundPolicy = 'refund_policy';

    public function getLabel(): string
    {
        return match ($this) {
            self::TermsOfUse => 'Условия использования',
            self::PrivacyPolicy => 'Политика конфиденциальности',
            self::RefundPolicy => 'Условия возврата',
        };
    }
}
