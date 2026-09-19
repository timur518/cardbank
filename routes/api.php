<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\CardProductController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\Webhooks\CardsProWebhookController;
use App\Http\Controllers\Api\Webhooks\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/cardspro/{provider:code}', CardsProWebhookController::class)
    ->name('webhooks.cardspro');

Route::post('/webhooks/payment/{paymentMethod}', PaymentWebhookController::class)
    ->name('webhooks.payment');

// Личный кабинет (ЛК) — см. CABINET_API_SPEC.md.
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Раздел 1. Аутентификация и профиль.
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/password/forgot', [AuthController::class, 'forgotPassword'])->name('auth.password.forgot');

    // Раздел 2. Настройки (публично, нужны и до регистрации).
    Route::get('/settings/brand', [SettingsController::class, 'brand'])->name('settings.brand');
    Route::get('/settings/referral', [SettingsController::class, 'referral'])->name('settings.referral');
    Route::get('/settings/currency-rates', [SettingsController::class, 'currencyRates'])->name('settings.currency-rates');

    // Раздел 3. Каталог (публично).
    Route::get('/card-products', [CardProductController::class, 'index'])->name('card-products.index');
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Раздел 3. Оформление заказа.
        Route::post('/orders/issue', [OrderController::class, 'issue'])->name('orders.issue');
        Route::post('/orders/topup', [OrderController::class, 'topup'])->name('orders.topup');

        // Раздел 4. Карты и история операций.
        Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
        Route::get('/cards/{card}', [CardController::class, 'show'])->name('cards.show');
        Route::get('/cards/{card}/transactions', [TransactionController::class, 'forCard'])->name('cards.transactions');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    });
});
