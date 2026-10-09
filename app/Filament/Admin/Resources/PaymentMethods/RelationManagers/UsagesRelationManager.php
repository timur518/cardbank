<?php

namespace App\Filament\Admin\Resources\PaymentMethods\RelationManagers;

use App\Models\Income;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'incomes';

    protected static ?string $title = 'История применений способа оплаты';

    protected static ?string $modelLabel = 'применение';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('id')
            ->emptyStateHeading('Пока нет платежей')
            ->emptyStateDescription('Здесь появится история платежей, прошедших через этот способ оплаты.')
            ->emptyStateIcon('heroicon-o-arrow-path')
            ->columns([
                TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->placeholder('—'),
                TextColumn::make('card.masked_number')
                    ->label('Карта')
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money(fn (Income $record) => $record->currency),
                TextColumn::make('payment_status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                // Раздел ведётся автоматически, ручное создание не предусмотрено.
            ])
            ->recordActions([]);
    }
}
