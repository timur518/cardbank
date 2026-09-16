<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BrandSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Бренд и сайт';

    protected static ?string $title = 'Бренд и сайт';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.admin.pages.brand-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public const KEYS = [
        'brand_domain',
        'brand_site_name',
        'brand_support_email',
        'brand_logo',
        'brand_theme_color',
    ];

    public function mount(): void
    {
        $this->form->fill(Setting::getMany(self::KEYS));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Бренд и сайт')
                    ->columns(2)
                    ->schema([
                        TextInput::make('brand_domain')
                            ->label('Домен сайта')
                            ->maxLength(255),
                        TextInput::make('brand_site_name')
                            ->label('Название сайта')
                            ->maxLength(255),
                        TextInput::make('brand_support_email')
                            ->label('Контактный email поддержки')
                            ->email()
                            ->maxLength(255),
                        ColorPicker::make('brand_theme_color')
                            ->label('Основной цвет оформления'),
                        FileUpload::make('brand_logo')
                            ->label('Логотип')
                            ->image()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()->title('Настройки бренда сохранены')->success()->send();
    }
}
