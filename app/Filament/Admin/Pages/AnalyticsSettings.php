<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AnalyticsSettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Аналитика и внешние сервисы';

    protected static ?string $title = 'Аналитика и внешние сервисы';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.admin.pages.analytics-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    /**
     * Ключи таблицы settings, отдаваемые публично через GET /api/v1/settings/analytics
     * (см. SettingsController::analytics()) и вставляемые в <head> и на лендинге
     * (routes/web.php + welcome.blade.php), и в ЛК (utils/analytics.ts).
     */
    public const KEYS = [
        'analytics_counter_id',
        'analytics_pixel_ids',
    ];

    public function mount(): void
    {
        $stored = Setting::getMany(self::KEYS);

        // Старый формат analytics_pixel_ids — JSON-массив идентификаторов (TagsInput).
        // Если значение распарсивается как массив — показываем его в textarea построчно, иначе берём как есть.
        $pixelCode = $stored['analytics_pixel_ids'] ?? null;
        $decodedPixelCode = json_decode((string) $pixelCode, true);

        $this->form->fill([
            'analytics_counter_id' => $stored['analytics_counter_id'],
            'analytics_pixel_ids' => is_array($decodedPixelCode) ? implode("\n", $decodedPixelCode) : $pixelCode,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Аналитика и внешние сервисы')
                    ->description('Код вставляется в <head> автоматически и на лендинге, и в личном кабинете.')
                    ->schema([
                        Textarea::make('analytics_counter_id')
                            ->label('Код счётчика (например, Яндекс.Метрика)')
                            ->helperText('Вставьте полный код счётчика целиком, включая тег <script>...</script>.')
                            ->rows(12)
                            ->columnSpanFull(),
                        Textarea::make('analytics_pixel_ids')
                            ->label('Код рекламных пикселей / прочих скриптов')
                            ->helperText('Вставьте код пикселей (Facebook, VK и т.п.) или другой сторонний код для <head>.')
                            ->rows(12)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'analytics_counter_id' => $state['analytics_counter_id'],
            'analytics_pixel_ids' => $state['analytics_pixel_ids'],
        ]);

        Notification::make()->title('Настройки аналитики сохранены')->success()->send();
    }
}
