<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BitbankerController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\CardProductController;
use App\Http\Controllers\Api\V1\KycController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\Webhooks\CardsProWebhookController;
use App\Http\Controllers\Api\Webhooks\DiditWebhookController;
use App\Http\Controllers\Api\Webhooks\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/cardspro/{provider:code}', CardsProWebhookController::class)
    ->name('webhooks.cardspro');

Route::post('/webhooks/payment/{paymentMethod}', PaymentWebhookController::class)
    ->name('webhooks.payment');

Route::post('/webhooks/didit', DiditWebhookController::class)
    ->name('webhooks.didit');

// Личный кабинет (ЛК) — см. CABINET_API_SPEC.md.
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Раздел 1. Аутентификация и профиль.
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    // Регистрация с лендинга (блок #apply в welcome.blade.php) — без поля пароля, он генерируется автоматически.
    Route::post('/auth/register-landing', [AuthController::class, 'registerLanding'])->name('auth.register-landing');
    Route::post('/auth/password/forgot', [AuthController::class, 'forgotPassword'])->name('auth.password.forgot');

    // Разблокировка ЛК по ПИН-коду на доверенном устройстве, когда обычная сессия уже истекла —
    // публичные эндпоинты (вне auth:sanctum), личность подтверждается httpOnly-кукой
    // mojno_pin_device (PinDeviceToken), а не самим запросом — см. PinUnlockPage.tsx.
    Route::get('/auth/device-status', [AuthController::class, 'deviceStatus'])->name('auth.device-status');
    Route::post('/auth/unlock-pin', [AuthController::class, 'unlockPin'])->middleware('throttle:10,1')->name('auth.unlock-pin');
    Route::post('/auth/forget-device', [AuthController::class, 'forgetDevice'])->name('auth.forget-device');

    // Раздел 2. Настройки (публично, нужны и до регистрации).
    Route::get('/settings/brand', [SettingsController::class, 'brand'])->name('settings.brand');
    Route::get('/settings/referral', [SettingsController::class, 'referral'])->name('settings.referral');
    Route::get('/settings/currency-rates', [SettingsController::class, 'currencyRates'])->name('settings.currency-rates');
    Route::get('/settings/analytics', [SettingsController::class, 'analytics'])->name('settings.analytics');

    // Раздел 3. Каталог (публично).
    Route::get('/card-products', [CardProductController::class, 'index'])->name('card-products.index');
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/pwa-installed', [ProfileController::class, 'markPwaInstalled'])->name('profile.pwa-installed');
        // Ограничиваем попытки подбора current_pin — код всего из 4 цифр (10 000 комбинаций).
        Route::post('/profile/pin', [ProfileController::class, 'setPin'])->middleware('throttle:10,1')->name('profile.pin');
        Route::post('/profile/pin/verify', [ProfileController::class, 'verifyPin'])->middleware('throttle:10,1')->name('profile.pin.verify');

        // Верификация личности через Didit — блок на странице профиля (перед «Мои данные»).
        Route::post('/kyc/start', [KycController::class, 'start'])->name('kyc.start');

        // Оферта/статус BitBanker в блоке выбора способа оплаты — см. BITBANKER_INTEGRATION_PLAN.md раздел 7.1.
        Route::get('/bitbanker/status', [BitbankerController::class, 'status'])->name('bitbanker.status');
        Route::post('/bitbanker/accept', [BitbankerController::class, 'accept'])->name('bitbanker.accept');

        // Раздел 3. Оформление заказа.
        Route::post('/orders/issue', [OrderController::class, 'issue'])->name('orders.issue');
        Route::post('/orders/topup', [OrderController::class, 'topup'])->name('orders.topup');
        Route::post('/orders/topup/quote', [OrderController::class, 'quote'])->name('orders.topup.quote');

        // Раздел 4. Карты и история операций.
        // Связывание модели по uuid, а не сквозному cards.id — чтобы номер карты в базе не светился в URL ЛК.
        Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
        Route::get('/cards/{card:uuid}', [CardController::class, 'show'])->name('cards.show');
        Route::get('/cards/{card:uuid}/requisites', [CardController::class, 'requisites'])->name('cards.requisites');
        Route::get('/cards/{card:uuid}/otp-codes', [CardController::class, 'otpCodes'])->name('cards.otp-codes');
        Route::get('/cards/{card:uuid}/transactions', [TransactionController::class, 'forCard'])->name('cards.transactions');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

        // Раздел 5. Уведомления — заводятся в админке, сюда только читают/отмечают прочитанными.
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    });
});
