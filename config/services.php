<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'cardspro' => [
        // Тайм-аут HTTP-запросов к API CardsPro, секунды. Учётные данные (api_base_url,
        // api_key, api_secret, webhook_secret) хранятся не здесь, а в записи провайдера
        // в разделе «Карты» → «Провайдеры карт», т.к. провайдеров может быть несколько.
        'timeout' => (int) env('CARDSPRO_TIMEOUT', 20),
    ],

    'cardlink' => [
        // Тайм-аут HTTP-запросов к API CardLink (https://cardlink.link/reference/api), секунды.
        // Учётные данные (api_token, shop_id, ...) хранятся не здесь, а в settlement_config
        // способа оплаты в разделе «Настройки» → «Способы оплаты», т.к. магазинов CardLink может быть несколько.
        'timeout' => (int) env('CARDLINK_TIMEOUT', 20),
    ],

    'didit' => [
        // Базовый URL API Didit (https://docs.didit.me) и тайм-аут HTTP-запросов, секунды.
        // Учётные данные (api_key, workflow_id, webhook secret) хранятся не здесь, а в Setting —
        // см. App\Filament\Admin\Pages\KycSettings (раздел «Комплаенс» → «Верификация (Didit)»).
        'base_url' => env('DIDIT_BASE_URL', 'https://verification.didit.me'),
        'timeout' => (int) env('DIDIT_TIMEOUT', 20),
    ],

];
