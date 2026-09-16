<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ActiveStatus;
use App\Enums\DiscrepancyStatus;
use App\Enums\MessageProcessingStatus;
use App\Models\CardProvider;
use App\Models\ProviderDiscrepancy;
use App\Models\ProviderMessage;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * «Состояние провайдеров карт» — по каждому подключённому провайдеру видно, нет ли
 * сбоев или задержек за последние сутки, по данным журнала сообщений от провайдера
 * и расхождений при сверке.
 */
class ProviderHealthWidget extends TableWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Состояние провайдеров карт')
            ->query(CardProvider::query())
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Провайдер'),
                TextColumn::make('status')
                    ->label('Подключение')
                    ->badge(),
                TextColumn::make('reserve_balance_usd')
                    ->label('Резерв')
                    ->money('USD'),
                TextColumn::make('health')
                    ->label('Состояние за сутки')
                    ->state(fn (CardProvider $record) => $this->health($record))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Стабильно' => 'success',
                        'Есть сбои' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Открыть')
                    ->url(fn (CardProvider $record) => route('filament.admin.resources.card-providers.view', $record))
                    ->icon('heroicon-o-arrow-top-right-on-square'),
            ]);
    }

    protected function health(CardProvider $record): string
    {
        if ($record->status === ActiveStatus::Inactive) {
            return 'Отключён';
        }

        $failedMessages = ProviderMessage::query()
            ->where('provider_id', $record->id)
            ->where('status', MessageProcessingStatus::Failed)
            ->where('received_at', '>=', now()->subDay())
            ->count();

        $openDiscrepancies = ProviderDiscrepancy::query()
            ->where('provider_id', $record->id)
            ->where('status', DiscrepancyStatus::Open)
            ->count();

        return ($failedMessages > 0 || $openDiscrepancies > 0) ? 'Есть сбои' : 'Стабильно';
    }
}
