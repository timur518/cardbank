<?php
// Общая конфигурация каналов и ядра консультанта.
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Store.php';
require_once __DIR__ . '/src/Consultant.php';
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
function setting(string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? getenv($key);
    return ($value === false || $value === '') ? $default : (string) $value;
}
function appStore(): Store { static $s; return $s ??= new Store(__DIR__ . '/storage/chat.sqlite'); }
function consultant(): Consultant {
    return new Consultant(appStore(), new GuzzleHttp\Client(['timeout' => 55, 'connect_timeout' => 10]),
        setting('OPENAI_API_KEY'), setting('OPENAI_MODEL', 'gpt-4o-mini'),
        max(2, (int) setting('MAX_HISTORY_MESSAGES', '20')),
        setting('OAI_PROXY_URL'), setting('OAI_PROXY_SECRET'));
}
