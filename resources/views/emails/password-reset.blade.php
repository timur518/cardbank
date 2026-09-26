@component('emails.layout', ['title' => 'Новый пароль для входа', 'heading' => 'Новый пароль для входа', 'preheader' => 'Вы запросили восстановление пароля', 'actionUrl' => $actionUrl, 'actionLabel' => 'Войти в личный кабинет'])
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Здравствуйте{{ $firstName ? ', '.$firstName : '' }}!</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#3A3C40;">Вы запросили восстановление пароля. Ваш новый пароль для входа:</p>
<p style="margin:0 0 20px;text-align:center;">
<span style="display:inline-block;background-color:#f3f1ef;color:#3A3C40;font-size:20px;font-weight:700;letter-spacing:.06em;padding:14px 24px;border-radius:14px;">{{ $newPassword }}</span>
</p>
<p style="margin:0;font-size:15px;line-height:1.6;color:#3A3C40;">После входа рекомендуем сменить его на свой в разделе «Профиль». Если вы не запрашивали сброс пароля, срочно обратитесь в поддержку.</p>
@endcomponent
