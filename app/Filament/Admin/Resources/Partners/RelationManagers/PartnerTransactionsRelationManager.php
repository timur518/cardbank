<?php

namespace App\Filament\Admin\Resources\Partners\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Начисления этому партнёру (только просмотр — полный реестр со всеми партнёрами
 * и возможностью редактирования см. в PartnerTransactionResource).
 */
class PartnerTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'partnerTransactions';

    protected static ?string $title = 'Начисления';

    protected static ?string $modelLabel = 'Начисление';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Начислений ещё не было')
            ->emptyStateDescription('Здесь появится история вознаграждений за приглашённых этим партнёром пользователей.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->columns([
                TextColumn::make('buyer.email')
                    ->label('Покупатель'),
                TextColumn::make('type')
                    ->label('За что')
                    ->badge(),
                TextColumn::make('income_id')
                    ->label('ID поступления')
                    ->placeholder('—'),
                TextColumn::make('rate')
                    ->label('Ставка')
                    ->formatStateUsing(fn ($record) => $record->type->isPercentRate() ? "{$record->rate}%" : "\${$record->rate}"),
                TextColumn::make('commission_amount')
                    ->label('Сумма вознаграждения')
                    ->money('USD'),
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
