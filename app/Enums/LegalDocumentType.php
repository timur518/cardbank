<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LegalDocumentType: string implements HasLabel
{
    case PublicOffer = 'public_offer';
    case TermsOfUse = 'terms_of_use';
    case PrivacyPolicy = 'privacy_policy';
    case RefundPolicy = 'refund_policy';
    case KycAmlPolicy = 'kyc_aml_policy';
    case PersonalDataConsent = 'personal_data_consent';
    case MessagingConsent = 'messaging_consent';
    case CookiePolicy = 'cookie_policy';

    public function getLabel(): string
    {
        return match ($this) {
            self::PublicOffer => 'Публичная оферта',
            self::TermsOfUse => 'Условия использования',
            self::PrivacyPolicy => 'Политика конфиденциальности',
            self::RefundPolicy => 'Условия возврата',
            self::KycAmlPolicy => 'Политика KYC/AML',
            self::PersonalDataConsent => 'Согласие на обработку персональных данных',
            self::MessagingConsent => 'Согласие на получение сообщений',
            self::CookiePolicy => 'Политика использования cookie',
        };
    }
}
