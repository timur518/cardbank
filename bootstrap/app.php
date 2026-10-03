<?php

use App\Services\Mail\EmailBranding;
use App\Services\Telegram\AdminTelegramNotifier;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

        // В приложении нет Blade-роута с именем "login" (ЛК — отдельная SPA на CABINET_URL,
        // Filament-админка использует свой собственный guard/редирект). Без переопределения
        // Laravel по умолчанию зовёт route('login') для любого гостя, попавшего на
        // auth:sanctum-роут без заголовка Accept: application/json (например, прямой переход
        // по ссылке на API в браузере или бот/сканер) — и падает с
        // "Route [login] not defined". Отправляем таких гостей на страницу входа в ЛК.
        $middleware->redirectGuestsTo(fn () => EmailBranding::cabinetUrl('/login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Единая точка для Telegram-алертов об ошибках бэкенда — срабатывает на
        // любом исключении, которое Laravel записывает в laravel.log (report()), без
        // необходимости добавлять отправку в каждом отдельном месте. Ожидаемые исключения
        // (ValidationException, 404/403, ModelNotFoundException и т.д.) уже отсеяны Laravel и
        // сюда не доходят — см. Illuminate\Foundation\Exceptions\Handler::$internalDontReport.
        $exceptions->reportable(function (\Throwable $e) {
            AdminTelegramNotifier::notifyException($e);
        });
    })->create();
