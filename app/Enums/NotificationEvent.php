<?php

namespace App\Enums;

/**
 * Событие, по которому создаётся запись в ленте «Уведомления» ЛК
 * ({@see \App\Models\Notification::notify()}). Один `case` на событие — категория
 * и тексты собраны прямо здесь, без отдельного файла-класса на каждое уведомление:
 * подключение нового события в будущем — это один новый `case` и по одному новому
 * `match`-варианту в category()/title()/body(), вызывается напрямую из места
 * события (вебхук-хендлер, контроллер и т.п.), без Event/Listener-прослойки.
 *
 * Параметры ($params), которых ожидает body() каждого события (передаются в
 * Notification::notify(), сохраняются как есть и в Notification.data):
 * - CardIssued, CardFrozen, CardUnfrozen, CardClosed: 'last4'
 * - CardPurchaseDeclined: 'last4', 'amount' (уже отформатированная строка, см. money()), 'merchant' (?string)
 * - TopupSuccess: 'last4', 'amount', 'balance' (отформатированные строки)
 * - TopupFailed: 'last4', 'amount'
 * - PasswordChanged: 'datetime' (отформатированная строка)
 * - PasswordResetRequested: 'email'
 * - OtpCodeReceived: 'code'
 * - Welcome, CardOrderAccepted, CardIssueFailed: без параметров
 */
enum NotificationEvent
{
    case Welcome;
    case CardOrderAccepted;
    case CardIssued;
    case CardIssueFailed;
    case CardFrozen;
    case CardUnfrozen;
    case CardClosed;
    case CardPurchaseDeclined;
    case TopupSuccess;
    case TopupFailed;
    case PasswordChanged;
    case PasswordResetRequested;
    case OtpCodeReceived;

    public function category(): NotificationType
    {
        return match ($this) {
            self::Welcome => NotificationType::System,
            self::CardOrderAccepted,
            self::CardIssued,
            self::CardIssueFailed,
            self::CardFrozen,
            self::CardUnfrozen,
            self::CardClosed,
            self::CardPurchaseDeclined => NotificationType::Card,
            self::TopupSuccess,
            self::TopupFailed => NotificationType::Payment,
            self::PasswordChanged,
            self::PasswordResetRequested => NotificationType::Security,
            self::OtpCodeReceived => NotificationType::OtpCode,
        };
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function title(array $params = []): string
    {
        return match ($this) {
            self::Welcome => 'Добро пожаловать!',
            self::CardOrderAccepted => 'Заявка на выпуск карты принята',
            self::CardIssued => 'Карта готова к использованию',
            self::CardIssueFailed => 'Не удалось выпустить карту',
            self::CardFrozen => 'Карта заморожена',
            self::CardUnfrozen => 'Карта разморожена',
            self::CardClosed => 'Карта закрыта',
            self::CardPurchaseDeclined => 'Операция отклонена',
            self::TopupSuccess => 'Баланс пополнен',
            self::TopupFailed => 'Пополнение не прошло',
            self::PasswordChanged => 'Пароль изменён',
            self::PasswordResetRequested => 'Внимание! Сброс пароля',
            self::OtpCodeReceived => 'Код подтверждения',
        };
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function body(array $params = []): string
    {
        return match ($this) {
            self::Welcome => 'Спасибо, что выбрали нас. Оформите первую карту и начните пользоваться всеми возможностями личного кабинета.',
            self::CardOrderAccepted => 'Оформляем вашу карту. Обычно это занимает несколько минут — сообщим, как только она будет готова.',
            self::CardIssued => "Карта •••• {$params['last4']} выпущена. Реквизиты и настройки доступны в разделе «Карты».",
            self::CardIssueFailed => 'Возникла ошибка при выпуске карты. Попробуйте оформить заявку ещё раз или напишите в поддержку.',
            self::CardFrozen => "Карта •••• {$params['last4']} временно заморожена. Операции по ней недоступны до разблокировки.",
            self::CardUnfrozen => "Карта •••• {$params['last4']} снова активна — можно пользоваться как обычно.",
            self::CardClosed => "Карта •••• {$params['last4']} закрыта. Операции по ней больше недоступны.",
            self::CardPurchaseDeclined => $this->purchaseDeclinedBody($params),
            self::TopupSuccess => "Карта •••• {$params['last4']} пополнена на {$params['amount']}. Новый баланс: {$params['balance']}.",
            self::TopupFailed => "Не удалось пополнить карту •••• {$params['last4']} на {$params['amount']}. Попробуйте ещё раз или используйте другой способ оплаты.",
            self::PasswordChanged => "Пароль от вашего личного кабинета был изменён {$params['datetime']}. Если это были не вы, срочно обратитесь в поддержку.",
            self::PasswordResetRequested => "Запрошен сброс пароля. Новый пароль был отправлен к вам на E-mail. Рекомендуем сменить его на свой после входа в личный кабинет.",
            self::OtpCodeReceived => "Код подтверждения: {$params['code']}. Никому не сообщайте этот код!",
        };
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function purchaseDeclinedBody(array $params): string
    {
        $merchant = $params['merchant'] ?? null;

        return $merchant
            ? "Покупка на {$params['amount']} отклонена. Избегайте блокировки карты! Частые оплаты с нулевым балансом могут ограничить доступ к карте. Рекомендуем регулярно проверять баланс и пополнять его при необходимости."
            : "Избегайте блокировки карты! Покупка на {$params['amount']} по карте •••• {$params['last4']} отклонена. Неудачные попытки оплаты могут ограничить доступ к карте. Рекомендуем регулярно проверять баланс и пополнять его при необходимости.";
    }

    /**
     * Форматирование суммы для текста уведомления — тот же вид, что и formatMoney()
     * во фронтенде ЛК (resources/cabinet/src/utils/format.ts): "124.55 $".
     */
    public static function money(float|int|string $amount, string $currency): string
    {
        $symbols = ['USD' => '$', 'EUR' => '€', 'RUB' => '₽', 'GBP' => '£'];

        return number_format((float) $amount, 2, '.', '') . ' ' . ($symbols[$currency] ?? $currency);
    }
}
