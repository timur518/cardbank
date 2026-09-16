<?php

namespace App\Filament\Admin\Resources\Cards\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'История изменения статуса';

    protected static ?string $modelLabel = 'запись истории';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('new_status')
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Изменений статуса ещё не было')
            ->emptyStateDescription('Здесь появится история заморозки, разморозки и закрытия этой карты.')
            ->emptyStateIcon('heroicon-o-clock')
            ->columns([
                TextColumn::make('old_status')
                    ->label('Было')
                    ->placeholder('—'),
                TextColumn::make('new_status')
                    ->label('Стало')
                    ->badge(),
                TextColumn::make('changer.name')
                    ->label('Кто изменил')
                    ->placeholder('—'),
                TextColumn::make('reason')
                    ->label('Причина')
                    ->placeholder('—')
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                // Раздел ведётся автоматически при изменении статуса карты, ручное создание не предусмотрено.
            ])
            ->recordActions([]);
    }
}
