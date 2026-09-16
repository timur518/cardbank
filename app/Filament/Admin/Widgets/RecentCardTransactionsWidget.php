<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\CardTransactions\CardTransactionResource;
use App\Models\CardTransaction;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * «Последние 20 операций по картам» — та же история операций, что и в разделе
 * «Финансы» → «Транзакции по картам», но самые свежие записи прямо на главной.
 */
class RecentCardTransactionsWidget extends TableWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Последние 20 операций по картам')
            ->query(
                CardTransaction::query()
                    ->with(['card', 'card.user'])
                    ->latest('occurred_at')
                    ->limit(20)
            )
            ->paginated(false)
            ->emptyStateHeading('Операций по картам пока нет')
            ->emptyStateIcon('heroicon-o-arrows-right-left')
            ->columns([
                TextColumn::make('card.masked_number')
                    ->label('Карта'),
                TextColumn::make('card.user.email')
                    ->label('Пользователь'),
                TextColumn::make('type')
                    ->label('Тип операции')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money(fn (CardTransaction $record) => $record->currency),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('occurred_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (CardTransaction $record) => CardTransactionResource::getUrl('view', ['record' => $record])),
            ])
            ->headerActions([
                Action::make('viewAll')
                    ->label('Все транзакции')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn () => CardTransactionResource::getUrl('index')),
            ]);
    }
}
