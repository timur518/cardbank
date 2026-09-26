@component('emails.layout', ['title' => 'Вы зарегистрированы', 'heading' => 'Вы зарегистрированы!', 'preheader' => 'Спасибо, что выбрали нас', 'actionUrl' => $actionUrl, 'actionLabel' => 'Оформить карту'])
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Здравствуйте{{ $firstName ? ', '.$firstName : '' }}!</p>
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Регистрация в личном кабинете прошла успешно. Теперь вам доступны все возможности: выпуск карт, пополнение баланса и контроль истории операций — в одном месте.</p>
<p style="margin:0;font-size:15px;line-height:1.6;color:#3A3C40;">Оформите первую карту и начните пользоваться сервисом уже сейчас.</p>
@endcomponent
