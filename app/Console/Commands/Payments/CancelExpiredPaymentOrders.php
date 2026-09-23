<?php

namespace App\Console\Commands\Payments;

use App\Enums\IncomePaymentStatus;
use App\Models\Income;
use App\Services\Payments\PaymentWebhookHandler;
use Illuminate\Console\Command;

/**
 * php artisan payments:cancel-expired-orders [--minutes=30]
 *
 * Общая для всех платёжных систем функция отмены заказа: если по заказу (`Income`)
 * так и не пришёл вебхук об успехе/отказе оплаты дольше `--minutes` минут с момента
 * его создания, заказ считается просроченным и отменяется — той же логикой
 * {@see PaymentWebhookHandler::cancelUnpaidOrder()}, что и явный отказ платёжной
 * системы, но с итоговым статусом `IncomePaymentStatus::Cancelled` вместо `Failed`.
 *
 * Работает одинаково для StubPaymentGateway, CardLink и любой другой платёжной
 * системы — ничего провайдер-специфичного здесь нет, только возраст самого `Income`.
 */
class CancelExpiredPaymentOrders extends Command
{
    protected $signature = 'payments:cancel-expired-orders {--minutes=30 : Через сколько минут без вебхука об оплате отменять заказ}';

    protected $description = 'Отменить заказы, по которым не пришло уведомление об оплате дольше N минут';

    public function handle(PaymentWebhookHandler $handler): int
    {
        $expiredBefore = now()->subMinutes((int) $this->option('minutes'));

        $orders = Income::where('payment_status', IncomePaymentStatus::Pending)
            ->where('created_at', '<=', $expiredBefore)
            ->get();

        foreach ($orders as $income) {
            $handler->cancelUnpaidOrder(
                $income,
                IncomePaymentStatus::Cancelled,
                'Не поступило уведомление об оплате в течение ' . (int) $this->option('minutes') . ' минут после создания заказа',
            );
        }

        $this->info("Отменено просроченных заказов: {$orders->count()}.");

        return self::SUCCESS;
    }
}
