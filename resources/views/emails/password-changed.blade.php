@component('emails.layout', ['title' => 'Пароль изменён', 'heading' => 'Пароль изменён', 'preheader' => 'Пароль от вашего личного кабинета был изменён', 'actionUrl' => $actionUrl, 'actionLabel' => 'Перейти в профиль'])
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Здравствуйте{{ $firstName ? ', '.$firstName : '' }}!</p>
<p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#3A3C40;">Пароль от вашего личного кабинета был изменён {{ $datetime }}.</p>
<p style="margin:0;font-size:15px;line-height:1.6;color:#3A3C40;">Если это были не вы, срочно обратитесь в поддержку и смените пароль ещё раз.</p>
@endcomponent
