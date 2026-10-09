<?php

use App\Enums\LegalDocumentType;
use App\Filament\Admin\Pages\AnalyticsSettings;
use App\Models\CardProduct;
use App\Models\LegalDocument;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Цены карт на лендинге берутся из админки (CardProduct), чтобы не расходиться
    // с ценами и тарифами, которые видит клиент в ЛК. Способы оплаты и сумма пополнения
    // теперь выбираются уже в ЛК (/cards/new), куда лендинг редиректит после регистрации.
    $cardProducts = CardProduct::query()
        ->where('active', true)
        ->orderBy('sort')
        ->get()
        ->keyBy('key');

    return view('welcome', [
        'cardProducts' => $cardProducts,
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

Route::get('/cards/orange', function () {
    return view('cards.orange', [
        'product' => CardProduct::query()->where('key', 'orange')->where('active', true)->first(),
        'analyticsCodes' => Setting::getMany(AnalyticsSettings::KEYS),
    ]);
})->name('cards.orange');

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
