<?php

namespace App\Services\Mail;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Общие для всех писем значения бренда — логотип, название сайта, ссылки на ЛК.
 * Логотип и название сайта берутся из App\Filament\Admin\Pages\BrandSettings
 * (те же настройки, что использует лендинг и панель Filament), с запасным
 * вариантом на статический ассет, если админ ещё ничего не загрузил.
 */
class EmailBranding
{
    public static function logoUrl(): string
    {
        $path = Setting::get('brand_logo');

        return $path ? Storage::disk('public')->url($path) : asset('assets/images/logo.png');
    }

    public static function siteName(): string
    {
        return Setting::get('brand_site_name') ?: 'Можно';
    }

    public static function supportEmail(): string
    {
        return Setting::get('brand_support_email') ?: 'info@mojno.cc';
    }

    /**
     * Абсолютная ссылка на страницу личного кабинета — из относительного пути
     * (например, `/cards/{uuid}`, как хранится в Notification.action_url) собирает
     * полный адрес на публичном домене ЛК (App\Filament\Admin\Pages\BrandSettings,
     * config('app.cabinet_url')).
     */
    public static function cabinetUrl(string $path = ''): string
    {
        return rtrim(config('app.cabinet_url'), '/').'/'.ltrim($path, '/');
    }
}
