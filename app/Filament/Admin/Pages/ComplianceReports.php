<?php

namespace App\Filament\Admin\Pages;

use App\Models\CardTransaction;
use App\Models\ComplianceAlert;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use UnitEnum;

class ComplianceReports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Комплаенс';

    protected static ?string $navigationLabel = 'Отчёты';

    protected static ?string $title = 'Отчёты по безопасности';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.compliance-reports';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Период')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('from')
                            ->label('С')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->live(),
                        DatePicker::make('to')
                            ->label('По')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function period(): array
    {
        $from = Carbon::parse($this->data['from'] ?? now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($this->data['to'] ?? now())->endOfDay();

        return [$from, $to];
    }

    protected function alertsInPeriod(): Collection
    {
        [$from, $to] = $this->period();

        return ComplianceAlert::query()
            ->with(['rule', 'user', 'card'])
            ->whereBetween('created_at', [$from, $to])
            ->get();
    }

    /**
     * @return array{alerts_count: int, suspicious_amount: float}
     */
    public function getStats(): array
    {
        [$from, $to] = $this->period();

        $alerts = $this->alertsInPeriod();

        $cardIds = $alerts->pluck('card_id')->filter()->unique();

        $suspiciousAmount = CardTransaction::query()
            ->whereIn('card_id', $cardIds)
            ->whereBetween('occurred_at', [$from, $to])
            ->sum('amount');

        return [
            'alerts_count' => $alerts->count(),
            'suspicious_amount' => (float) $suspiciousAmount,
        ];
    }

    /**
     * Разбивка сработавших предупреждений по правилам за выбранный период.
     *
     * @return Collection<int, array{rule: string, count: int}>
     */
    public function getRuleBreakdown(): Collection
    {
        return $this->alertsInPeriod()
            ->groupBy(fn (ComplianceAlert $alert) => $alert->rule?->name ?? 'Без правила')
            ->map(fn (Collection $group, string $ruleName) => [
                'rule' => $ruleName,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Выгрузить отчёт')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    [$from, $to] = $this->period();
                    $alerts = $this->alertsInPeriod();

                    return response()->streamDownload(function () use ($alerts) {
                        $handle = fopen('php://output', 'w');
                        fputcsv($handle, ['ID', 'Правило', 'Пользователь', 'Карта', 'Статус', 'Дата срабатывания']);

                        foreach ($alerts as $alert) {
                            fputcsv($handle, [
                                $alert->id,
                                $alert->rule?->name,
                                $alert->user?->email,
                                $alert->card?->masked_number,
                                $alert->status->getLabel(),
                                $alert->created_at?->format('d.m.Y H:i'),
                            ]);
                        }

                        fclose($handle);
                    }, "compliance-report-{$from->toDateString()}-{$to->toDateString()}.csv");
                }),
        ];
    }
}
