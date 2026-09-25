<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Учётные данные Didit (https://business.didit.me) для верификации личности
 * клиентов — попап в ЛК, раздел «Профиль». См. App\Services\Integrations\Didit.
 */
class KycSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Комплаенс';

    protected static ?string $navigationLabel = 'Верификация (Didit)';

    protected static ?string $title = 'Верификация личности — Didit';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.admin.pages.kyc-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public const KEYS = [
        'didit_api_key',
        'didit_workflow_id',
        'didit_webhook_secret',
    ];

    public function mount(): void
    {
        $this->form->fill(Setting::getMany(self::KEYS));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Учётные данные Didit')
                    ->description(
                        'Business Console → API & Webhooks (business.didit.me). Адрес для вебхука (событие status.updated, webhook_version v3): '
                        .url('/api/webhooks/didit')
                    )
                    ->columns(2)
                    ->schema([
                        TextInput::make('didit_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable(),
                        TextInput::make('didit_workflow_id')
                            ->label('Workflow ID'),
                        TextInput::make('didit_webhook_secret')
                            ->label('Webhook Secret Key')
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()->title('Настройки верификации сохранены')->success()->send();
    }
}
