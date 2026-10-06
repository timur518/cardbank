@if($insufficientFunds ?? true)
@component('emails.layout', ['title' => 'Избегайте блокировки карты!', 'heading' => 'Избегайте блокировки карты!', 'preheader' => 'Недостаточно средств на балансе', 'actionUrl' => $actionUrl, 'actionLabel' => 'Пополнить баланс'])
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Здравствуйте{{ $firstName ? ', '.$firstName : '' }}!</p>
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">
    @if(!empty($reason))
        Попытка оплаты{{ $merchant ? ' в '.$merchant : '' }} на {{ $amount }} по карте •••• {{ $last4 }} отклонена. {{ $reason }}
    @else
        Попытка оплаты{{ $merchant ? ' в '.$merchant : '' }} на {{ $amount }} по карте •••• {{ $last4 }} отклонена — на балансе недостаточно средств.
    @endif
</p>
<p style="margin:0;font-size:15px;line-height:1.6;color:#3A3C40;">Частые неудачные попытки оплаты могут привести к блокировке карты. Рекомендуем регулярно проверять баланс и пополнять его заранее.</p>
@endcomponent
@else
@component('emails.layout', ['title' => 'Платёж по карте отклонён', 'heading' => 'Платёж по карте отклонён', 'preheader' => 'Оплата не прошла', 'actionUrl' => $actionUrl, 'actionLabel' => 'Посмотреть карту'])
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Здравствуйте{{ $firstName ? ', '.$firstName : '' }}!</p>
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">
    Попытка оплаты{{ $merchant ? ' в '.$merchant : '' }} на {{ $amount }} по карте •••• {{ $last4 }} отклонена.
    @if(!empty($reason)) {{ $reason }} @endif
</p>
<p style="margin:0;font-size:15px;line-height:1.6;color:#3A3C40;">Если это была ваша операция, попробуйте ещё раз позже или обратитесь в поддержку.</p>
@endcomponent
@endif
