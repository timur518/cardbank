<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
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

    public function mount(): void
    {
        $stored = Setting::getMany(['analytics_counter_id', 'analytics_pixel_ids']);

        $this->form->fill([
            'analytics_counter_id' => $stored['analytics_counter_id'],
            'analytics_pixel_ids' => json_decode($stored['analytics_pixel_ids'] ?? '[]', true) ?: [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Аналитика и внешние сервисы')
                    ->schema([
                        TextInput::make('analytics_counter_id')
                            ->label('Идентификатор счётчика посещаемости')
                            ->maxLength(255),
                        TagsInput::make('analytics_pixel_ids')
                            ->label('Идентификаторы рекламных пикселей')
                            ->placeholder('Введите идентификатор и нажмите Enter'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::setMany([
            'analytics_counter_id' => $state['analytics_counter_id'],
            'analytics_pixel_ids' => json_encode($state['analytics_pixel_ids'] ?? []),
        ]);

        Notification::make()->title('Настройки аналитики сохранены')->success()->send();
    }
}
