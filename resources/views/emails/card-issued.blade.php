@component('emails.layout', ['title' => 'Ваша карта готова!', 'heading' => 'Ваша карта готова! Можно платить', 'preheader' => 'Карта выпущена и активна', 'actionUrl' => $actionUrl, 'actionLabel' => 'Перейти к карте'])
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Здравствуйте{{ $firstName ? ', '.$firstName : '' }}!</p>
<p style="margin:0;font-size:15px;line-height:1.6;color:#3A3C40;">
    Карта{{ $productName ? ' «'.$productName.'»' : '' }} •••• {{ $last4 }} выпущена и активна. Реквизиты и настройки доступны в личном кабинете в разделе «Карты».
</p>
@endcomponent
