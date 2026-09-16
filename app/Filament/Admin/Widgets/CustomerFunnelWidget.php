<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\CardStatus;
use App\Enums\KycStatus;
use App\Models\Card;
use App\Models\User;
use Filament\Widgets\Widget;

/**
 * «Путь клиента от захода на сайт до активной карты». Заходы на сайт считает внешний
 * счётчик посещаемости (см. «Настройки» → «Аналитика и внешние сервисы») — в базе
 * данных фиксируются только шаги начиная с регистрации, поэтому воронка построена
 * от них.
 */
class CustomerFunnelWidget extends Widget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.admin.widgets.customer-funnel-widget';

    /**
     * @return array<int, array{label: string, count: int}>
     */
    public function getSteps(): array
    {
        $registered = User::count();
        $kycApproved = User::where('kyc_status', KycStatus::Approved)->count();
        $paidIssuance = Card::query()->distinct('user_id')->count('user_id');
        $activeHolders = Card::where('status', CardStatus::Active)->distinct('user_id')->count('user_id');

        return [
            ['label' => 'Зарегистрировались', 'count' => $registered],
            ['label' => 'Прошли проверку личности', 'count' => $kycApproved],
            ['label' => 'Оплатили выпуск карты', 'count' => $paidIssuance],
            ['label' => 'Стали активными держателями карты', 'count' => $activeHolders],
        ];
    }
}
