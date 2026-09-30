<?php

use App\Enums\ActiveStatus;
use App\Enums\LegalDocumentType;
use App\Filament\Admin\Pages\AnalyticsSettings;
use App\Models\CardProduct;
use App\Models\LegalDocument;
use App\Models\PaymentMethod;
use App\Models\Setting;
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
        // Код счётчика/пикселей из админки (Настройки -> Аналитика и внешние сервисы),
        // вставляется в <head> в welcome.blade.php как есть (сырой HTML); тот же код отдаётся
        // личному кабинету через GET /api/v1/settings/analytics (см. SettingsController::analytics()).
        'analyticsCodes' => Setting::getMany(AnalyticsSettings::KEYS),
    ]);
});

// Страница тарифов и условий карт — полностью автоматическая, без ручного контента в Blade:
// выводит полные условия по каждому активному CardProduct из админки (те же поля,
// что уже публичны в API ЛК — см. CardProductResource/CardDetailResource).
Route::get('/tariffs', function () {
    $cardProducts = CardProduct::query()
        ->where('active', true)
        ->orderBy('sort')
        ->get();

    return view('tariffs', [
        'cardProducts' => $cardProducts,
    ]);
})->name('tariffs');

// Страницы юридических документов — текст каждой берётся из админки (LegalDocument),
// показывается последняя действующая версия соответствующего типа документа.
// Слаг URL => [тип документа, заголовок страницы, имя роута].
foreach ([
    'oferta' => [LegalDocumentType::PublicOffer, 'Публичная оферта', 'legal.offer'],
    'privacy-policy' => [LegalDocumentType::PrivacyPolicy, 'Политика конфиденциальности', 'legal.privacy-policy'],
    'kyc-aml' => [LegalDocumentType::KycAmlPolicy, 'Политика KYC/AML', 'legal.kyc-aml'],
    'personal-data-consent' => [LegalDocumentType::PersonalDataConsent, 'Согласие на обработку персональных данных', 'legal.personal-data-consent'],
    'messaging-consent' => [LegalDocumentType::MessagingConsent, 'Согласие на получение сообщений', 'legal.messaging-consent'],
    'cookie-policy' => [LegalDocumentType::CookiePolicy, 'Политика использования cookie', 'legal.cookie-policy'],
] as $slug => [$type, $title, $routeName]) {
    Route::get("/{$slug}", function () use ($type, $title) {
        $document = LegalDocument::query()
            ->where('type', $type)
            ->orderByDesc('effective_at')
            ->orderByDesc('id')
            ->first();

        return view('legal.document', [
            'title' => $title,
            'document' => $document,
        ]);
    })->name($routeName);
}
