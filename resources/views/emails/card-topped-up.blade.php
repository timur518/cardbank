@component('emails.layout', ['title' => 'Баланс карты пополнен!', 'heading' => 'Баланс карты пополнен! Можно платить', 'preheader' => 'Карта пополнена на '.$amount, 'actionUrl' => $actionUrl, 'actionLabel' => 'Перейти к карте'])
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Здравствуйте{{ $firstName ? ', '.$firstName : '' }}!</p>
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Карта •••• {{ $last4 }} пополнена на {{ $amount }}.</p>
<p style="margin:0;font-size:15px;line-height:1.6;color:#3A3C40;">Новый баланс: <strong>{{ $balance }}</strong>. Можно продолжать платить картой.</p>
@endcomponent
