<?php

use App\Enums\ActiveStatus;
use App\Enums\LegalDocumentType;
use App\Models\CardProduct;
use App\Models\LegalDocument;
use App\Models\PaymentMethod;
use App\Services\CurrencyRateService;
use Illuminate\Support\Facades\Route;

Route::get('/', function (CurrencyRateService $rates) {
    // Цены карт и способы оплаты на лендинге берутся из админки (CardProduct/PaymentMethod),
    // чтобы не расходиться с ценами и тарифами, которые видит клиент в ЛК.
    $cardProducts = CardProduct::query()
        ->where('active', true)
        ->orderBy('sort')
        ->get()
        ->keyBy('key');

    $paymentMethods = PaymentMethod::query()
        ->where('status', ActiveStatus::Active)
        ->get();

    return view('welcome', [
        'cardProducts' => $cardProducts,
        'paymentMethods' => $paymentMethods,
        'usdSellRate' => $rates->sellRate('usd'),
    ]);
});

// Публичная оферта — текст берётся из админки (LegalDocument), показывается последняя
// действующая версия этого типа документа.
Route::get('/oferta', function () {
    $document = LegalDocument::query()
        ->where('type', LegalDocumentType::PublicOffer)
        ->orderByDesc('effective_at')
        ->orderByDesc('id')
        ->first();

    return view('legal.document', [
        'title' => 'Публичная оферта',
        'document' => $document,
    ]);
})->name('legal.offer');
