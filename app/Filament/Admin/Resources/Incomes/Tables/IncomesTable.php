<?php

namespace App\Filament\Admin\Resources\Incomes\Tables;

use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Models\Income;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IncomesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Поступлений пока нет')
            ->emptyStateDescription('Здесь появятся все деньги, которые получает компания: оплата выпуска карт, пополнений, штрафы и прочее.')
            ->emptyStateIcon('heroicon-o-arrow-trending-up')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('type')
                    ->label('Тип поступления')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money(fn (Income $record) => $record->currency)
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Статус платежа')
                    ->badge(),
                TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->placeholder('—'),
                TextColumn::make('card.masked_number')
                    ->label('Карта')
                    ->placeholder('—'),
                TextColumn::make('created_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип поступления')
                    ->options(IncomeType::class),
                SelectFilter::make('payment_status')
                    ->label('Статус платежа')
                    ->options(IncomePaymentStatus::class),
            ])
            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()->label(''),
                DeleteAction::make()->label(''),
            ]);
    }
}
