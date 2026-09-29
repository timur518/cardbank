<?php

namespace App\Enums;

/**
 * Событие для админ-уведомлений в Telegram ({@see \App\Services\Telegram\AdminTelegramNotifier}).
 * Один `case` на событие — тексты собраны прямо здесь, без отдельного файла-шаблона на
 * каждое уведомление: подключение нового события в будущем — это один новый `case` и
 * один новый `match`-вариант в message(), вызывается напрямую из места события (контроллер,
 * вебхук-хендлер и т.п.) через AdminTelegramNotifier::notify(), без Event/Listener-прослойки.
 *
 * Параметры ($params), которых ожидает message() каждого события:
 * - NewRegistration: 'name', 'email', 'phone'
 * - CardIssueStarted: 'name', 'email', 'card_product', 'amount' (уже отформатированная строка, см. money())
 * - NewIncome: 'name', 'email', 'type' (строка "Выпуск карты"/"Пополнение карты"), 'amount'
 * - CardIssued: 'name', 'email', 'card_product', 'last4'
 * - PurchaseMade: 'name', 'email', 'last4', 'amount', 'merchant' (?string), 'pending' (bool —
 *   true для холда/авторизации, false для состоявшегося расчёта)
 * - KycStarted: 'name', 'email'
 * - KycResult: 'name', 'email', 'approved' (bool), 'reason' (?string, только для отказа)
 */
enum AdminTelegramEvent
{
    case NewRegistration;
    case CardIssueStarted;
    case NewIncome;
    case CardIssued;
    case PurchaseMade;
    case KycStarted;
    case KycResult;

    /**
     * @param  array<string, mixed>  $params
     */
    public function message(array $params = []): string
    {
        return match ($this) {
            self::NewRegistration => "🆕 <b>Новая регистрация</b>\n\n{$params['name']}\n{$params['email']}\n{$params['phone']}",

            self::CardIssueStarted => "💳 <b>Начался выпуск карты</b>\n\n{$params['name']} ({$params['email']})\n"
                ."Продукт: {$params['card_product']}\nСумма заказа: {$params['amount']}",

            self::NewIncome => "💰 <b>Новое поступление</b>\n\n{$params['name']} ({$params['email']})\n"
                ."{$params['type']}: {$params['amount']}",

            self::CardIssued => "✅ <b>Карта выпущена</b>\n\n{$params['name']} ({$params['email']})\n"
                ."Продукт: {$params['card_product']}\nКарта: •••• {$params['last4']}",

            self::PurchaseMade => ($params['pending'] ?? false)
                ? "🕐 <b>Покупка по карте (в обработке)</b>\n\n{$params['name']} ({$params['email']})\n"
                    ."Карта: •••• {$params['last4']}\nСумма: {$params['amount']}"
                    .(! empty($params['merchant']) ? "\nМерчант: {$params['merchant']}" : '')
                    ."\n\nАвторизация ещё не рассчитана мерчантом — может занять время или быть отменена."
                : "🛒 <b>Покупка по карте</b>\n\n{$params['name']} ({$params['email']})\n"
                    ."Карта: •••• {$params['last4']}\nСумма: {$params['amount']}"
                    .(! empty($params['merchant']) ? "\nМерчант: {$params['merchant']}" : ''),

            self::KycStarted => "🪪 <b>Начата верификация KYC</b>\n\n{$params['name']} ({$params['email']})",

            self::KycResult => ($params['approved'] ?? false)
                ? "🟢 <b>KYC пройден</b>\n\n{$params['name']} ({$params['email']})"
                : "🔴 <b>KYC отклонён</b>\n\n{$params['name']} ({$params['email']})"
                    .(! empty($params['reason']) ? "\nПричина: {$params['reason']}" : ''),
        };
    }
}
