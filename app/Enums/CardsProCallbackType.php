<?php

namespace App\Enums;

/**
 * Значения заголовка X-CP-Callback-Type во входящих вебхуках CardsPro.
 * См. https://docs.cardspro.com/api/operations-callbacks
 */
enum CardsProCallbackType: string
{
    case CardIssue = 'CARD_ISSUE';
    case CardTopup = 'CARD_TOPUP';
    case CardWithdrawal = 'CARD_WITHDRAWAL';
    case CardBlock = 'CARD_BLOCK';
    case CardFreeze = 'CARD_FREEZE';
    case CardUnfreeze = 'CARD_UNFREEZE';
    case ExtraFeeCard = 'EXTRA_FEE_CARD';
    case ExtraFeeCap = 'EXTRA_FEE_CAP';
    case CardTransaction = 'CARD_TRANSACTION';
    case OtpCode = 'OTP_CODE';
    case KycChange = 'KYC_CHANGE';
}
