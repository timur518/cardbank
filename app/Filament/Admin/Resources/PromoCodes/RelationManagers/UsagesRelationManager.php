<?php

namespace App\Filament\Admin\Resources\PromoCodes\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';

    protected static ?string $title = 'Использования промокода';

    protected static ?string $modelLabel = 'использование';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Промокод ещё не применяли')
            ->emptyStateDescription('Здесь появится история применений этого промокода клиентами.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->columns([
                TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->placeholder('—'),
                TextColumn::make('card.masked_number')
                    ->label('Карта')
                    ->placeholder('—'),
                TextColumn::make('discount_amount')
                    ->label('Сумма скидки')
                    ->money('RUB'),
                TextColumn::make('created_at')
                    ->label('Дата применения')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                // Раздел ведётся автоматически, ручное создание не предусмотрено.
            ])
            ->recordActions([]);
    }
}
