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

class CurrencySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Валютная система';

    protected static ?string $title = 'Валютная система';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.currency-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public const MARKUP_KEYS = [
        'currency_markup_usd_percent',
        'currency_markup_eur_percent',
        'currency_markup_gbp_percent',
    ];

    public function mount(): void
    {
        $this->form->fill(Setting::getMany(self::MARKUP_KEYS));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Наценка на покупку валюты')
                    ->description('Наценка добавляется к текущему курсу ЦБ при расчёте стоимости валюты для клиентов.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('currency_markup_usd_percent')
                            ->label('Наценка % на $')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->helperText(fn (mixed $state) => $this->costHelperText('currency_rate_usd', '$', $state)),
                        TextInput::make('currency_markup_eur_percent')
                            ->label('Наценка % на EUR')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->helperText(fn (mixed $state) => $this->costHelperText('currency_rate_eur', '€', $state)),
                        TextInput::make('currency_markup_gbp_percent')
                            ->label('Наценка % на GBP')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->helperText(fn (mixed $state) => $this->costHelperText('currency_rate_gbp', '£', $state)),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Строка вида «1 $ = 95,50 ₽ (курс 92,72 ₽ + наценка 3%)» под полем наценки.
     */
    protected function costHelperText(string $rateKey, string $symbol, mixed $markupPercent): string
    {
        $rate = Setting::get($rateKey);

        if ($rate === null) {
            return 'Курс ещё не загружен — дождитесь обновления курсов валют.';
        }

        $rate = (float) $rate;
        $markup = (float) ($markupPercent ?? 0);
        $cost = $rate * (1 + $markup / 100);

        return sprintf(
            '1 %s = %s ₽ (курс %s ₽ + наценка %s%%)',
            $symbol,
            number_format($cost, 2, ',', ' '),
            number_format($rate, 2, ',', ' '),
            number_format($markup, 2, ',', ' '),
        );
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()->title('Настройки валютной системы сохранены')->success()->send();
    }
}
