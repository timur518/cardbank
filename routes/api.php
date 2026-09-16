<?php

use App\Http\Controllers\Api\Webhooks\CardsProWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/cardspro/{provider:code}', CardsProWebhookController::class)
    ->name('webhooks.cardspro');
