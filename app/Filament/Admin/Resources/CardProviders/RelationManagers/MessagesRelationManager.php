<?php

namespace App\Filament\Admin\Resources\CardProviders\RelationManagers;

use App\Enums\MessageProcessingStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Журнал сообщений от провайдера';

    protected static ?string $modelLabel = 'сообщение';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event_type')
            ->emptyStateHeading('Сообщений пока нет')
            ->emptyStateDescription('Здесь будут появляться автоматические сообщения от провайдера о событиях: выпуск карты, зачисление денег и т.п.')
            ->emptyStateIcon('heroicon-o-inbox')
            ->columns([
                TextColumn::make('event_type')
                    ->label('Тип события'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('received_at')
                    ->label('Дата получения')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('processed_at')
                    ->label('Дата обработки')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(MessageProcessingStatus::class),
            ])
            ->headerActions([
                // Раздел ведётся автоматически по данным от провайдера, ручное создание не предусмотрено.
            ])
            ->recordActions([]);
    }
}
