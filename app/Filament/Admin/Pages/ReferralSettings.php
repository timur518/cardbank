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

class ReferralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Настройки реферальной системы';

    protected static ?string $title = 'Настройки реферальной системы';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.admin.pages.referral-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public const KEYS = [
        'referral_issue_rate',
        'referral_topup_rate',
        'referral_hold_days',
        'referral_min_wallet_rub',
        'referral_min_bank_rub',
    ];

    public function mount(): void
    {
        $this->form->fill(Setting::getMany(self::KEYS));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Общие правила программы')
                    ->columns(2)
                    ->schema([
                        TextInput::make('referral_issue_rate')
                            ->label('Доля партнёра от суммы выпуска карты, %')
                            ->numeric(),
                        TextInput::make('referral_topup_rate')
                            ->label('Доля партнёра от суммы пополнений, %')
                            ->numeric(),
                        TextInput::make('referral_hold_days')
                            ->label('Сколько дней начисление ожидает перед выводом')
                            ->numeric(),
                        TextInput::make('referral_min_wallet_rub')
                            ->label('Минимальная сумма для вывода на внутренний счёт, ₽')
                            ->numeric()
                            ->prefix('₽'),
                        TextInput::make('referral_min_bank_rub')
                            ->label('Минимальная сумма для вывода на банковскую карту, ₽')
                            ->numeric()
                            ->prefix('₽'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()->title('Настройки реферальной системы сохранены')->success()->send();
    }
}
