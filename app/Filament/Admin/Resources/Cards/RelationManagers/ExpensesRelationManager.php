<?php

namespace App\Filament\Admin\Resources\Cards\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    protected static ?string $title = 'Связанные расходы';

    protected static ?string $modelLabel = 'расход';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('category')
            ->defaultSort('date', 'desc')
            ->emptyStateHeading('Расходов по этой карте нет')
            ->emptyStateIcon('heroicon-o-arrow-trending-down')
            ->columns([
                TextColumn::make('category')
                    ->label('Статья')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money(fn ($record) => $record->currency),
                TextColumn::make('date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([]);
    }
}
