<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<title>{{ $title ?? \App\Services\Mail\EmailBranding::siteName() }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f1ef;font-family:'Segoe UI',Roboto,Arial,sans-serif;">
<span style="display:none;font-size:1px;line-height:1px;color:#f3f1ef;max-height:0;max-width:0;opacity:0;overflow:hidden;">{{ $preheader ?? '' }}</span>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f1ef;">
<tr>
<td align="center" style="padding:32px 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background-color:#ffffff;border-radius:24px;">
<tr>
<td align="center" style="padding:32px 24px 8px;">
<img src="{{ \App\Services\Mail\EmailBranding::logoUrl() }}" width="56" height="56" alt="{{ \App\Services\Mail\EmailBranding::siteName() }}" style="display:block;border:0;outline:none;width:56px;height:56px;">
</td>
</tr>
<tr>
<td align="center" style="padding:0 24px 24px;">
<span style="font-size:13px;letter-spacing:.04em;color:#77736e;text-transform:uppercase;">{{ \App\Services\Mail\EmailBranding::siteName() }}</span>
</td>
</tr>
<tr>
<td style="padding:0 32px 8px;">
<h1 style="margin:0 0 16px;font-size:22px;line-height:1.3;color:#3A3C40;font-weight:600;">{{ $heading }}</h1>
{{ $slot }}
</td>
</tr>
@isset($actionUrl)
<tr>
<td align="center" style="padding:8px 32px 32px;">
<a href="{{ $actionUrl }}" style="display:inline-block;background-color:#f37338;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:14px 28px;border-radius:14px;">{{ $actionLabel ?? 'Перейти в личный кабинет' }}</a>
</td>
</tr>
@endisset
<tr>
<td style="padding:24px 32px 32px;border-top:1px solid #dedbd6;">
<p style="margin:16px 0 0;font-size:13px;color:#77736e;line-height:1.5;">Это автоматическое письмо от {{ \App\Services\Mail\EmailBranding::siteName() }}. Если у вас есть вопросы, напишите нам: <a href="mailto:{{ \App\Services\Mail\EmailBranding::supportEmail() }}" style="color:#f37338;text-decoration:none;">{{ \App\Services\Mail\EmailBranding::supportEmail() }}</a></p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
