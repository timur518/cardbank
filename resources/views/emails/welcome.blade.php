@component('emails.layout', ['title' => 'Вы зарегистрированы', 'heading' => 'Вы зарегистрированы!', 'preheader' => 'Спасибо, что выбрали нас', 'actionUrl' => $actionUrl, 'actionLabel' => 'Оформить карту'])
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Здравствуйте{{ $firstName ? ', '.$firstName : '' }}!</p>
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Регистрация в личном кабинете прошла успешно. Теперь вам доступны все возможности: выпуск карт, пополнение баланса и контроль истории операций — в одном месте.</p>
@if ($generatedPassword)
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#3A3C40;">Для входа используйте ваш email и сгенерированный пароль:</p>
<p style="margin:0 0 20px;text-align:center;">
<span style="display:inline-block;background-color:#f3f1ef;color:#3A3C40;font-size:20px;font-weight:700;letter-spacing:.06em;padding:14px 24px;border-radius:14px;">{{ $generatedPassword }}</span>
</p>
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">После входа рекомендуем сменить его на свой в разделе «Профиль».</p>
@endif
<p style="margin:0;font-size:15px;line-height:1.6;color:#3A3C40;">Оформите первую карту и начните пользоваться сервисом уже сейчас.</p>
@endcomponent
