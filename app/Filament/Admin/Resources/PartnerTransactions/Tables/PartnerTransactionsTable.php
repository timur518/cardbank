<?php

namespace App\Filament\Admin\Resources\PartnerTransactions\Tables;

use App\Enums\PartnerTransactionType;
use App\Models\PartnerTransaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PartnerTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Начислений пока нет')
            ->emptyStateDescription('Здесь появится реестр вознаграждений партнёрам за приглашённых пользователей: регистрацию, выпуск карты и пополнения.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить начисление'),
            ])
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('partner.email')
                    ->label('Партнёр')
                    ->searchable(),
                TextColumn::make('buyer.email')
                    ->label('Покупатель')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('За что')
                    ->badge(),
                TextColumn::make('income_id')
                    ->label('ID операции')
                    ->placeholder('—'),
                TextColumn::make('rate')
                    ->label('Ставка')
                    ->formatStateUsing(fn (PartnerTransaction $record) => $record->type->isPercentRate() ? "{$record->rate}%" : "\${$record->rate}"),
                TextColumn::make('commission_amount')
                    ->label('Сумма вознаграждения')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('За что')
                    ->options(PartnerTransactionType::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
