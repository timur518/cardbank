<?php

namespace App\Filament\Admin\Resources\Cards\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IncomesRelationManager extends RelationManager
{
    protected static string $relationship = 'incomes';

    protected static ?string $title = 'Связанные поступления';

    protected static ?string $modelLabel = 'поступление';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Поступлений по этой карте нет')
            ->emptyStateIcon('heroicon-o-arrow-trending-up')
            ->columns([
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money(fn ($record) => $record->currency),
                TextColumn::make('payment_status')
                    ->label('Статус платежа')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([]);
    }
}
