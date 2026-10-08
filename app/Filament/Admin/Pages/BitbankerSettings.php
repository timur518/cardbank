<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Текст оферты BitBanker, который пользователь видит в попапе принятия перед
 * регистрацией (см. BITBANKER_INTEGRATION_PLAN.md разделы 7.1, 9, 10.3) —
 * отдаётся клиенту через GET /v1/bitbanker/status (BitbankerController::status()),
 * отдельного публичного эндпоинта под один текст не заводится.
 */
class BitbankerSettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Настройки BitBanker';

    protected static ?string $title = 'Настройки BitBanker';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.bitbanker-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public const KEYS = [
        'bitbanker_offer_text',
    ];

    public function mount(): void
    {
        $this->form->fill(Setting::getMany(self::KEYS));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Оферта')
                    ->schema([
                        RichEditor::make('bitbanker_offer_text')
                            ->label('Текст оферты BitBanker')
                            ->helperText('Показывается пользователю в попапе принятия перед регистрацией в BitBanker. Длинный текст (например, на несколько страниц A4) — нормально, хранится без ограничения длины.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()->title('Настройки BitBanker сохранены')->success()->send();
    }
}
