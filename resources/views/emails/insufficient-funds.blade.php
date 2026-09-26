@component('emails.layout', ['title' => 'Избегайте блокировки карты!', 'heading' => 'Избегайте блокировки карты!', 'preheader' => 'Недостаточно средств на балансе', 'actionUrl' => $actionUrl, 'actionLabel' => 'Пополнить баланс'])
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Здравствуйте{{ $firstName ? ', '.$firstName : '' }}!</p>
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">
    Попытка оплаты{{ $merchant ? ' в '.$merchant : '' }} на {{ $amount }} по карте •••• {{ $last4 }} отклонена — на балансе недостаточно средств.
</p>
<p style="margin:0;font-size:15px;line-height:1.6;color:#3A3C40;">Частые неудачные попытки оплаты могут привести к блокировке карты. Рекомендуем регулярно проверять баланс и пополнять его заранее.</p>
@endcomponent
