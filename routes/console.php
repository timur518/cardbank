<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Фоновая синхронизация с провайдерами карт (см. docs/integrations/cardspro.md,
// раздел «Фоновая синхронизация»). На сервере нужен один крон-энтри:
//   * * * * * cd /path/to/cardbank && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('providers:sync-card-transactions')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('providers:sync-card-balances')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('providers:sync-pending-operations')
    ->everyTwoMinutes()
    ->withoutOverlapping();

Schedule::command('providers:sync-account-balances')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('providers:sync-card-catalog')
    ->daily()
    ->withoutOverlapping();
