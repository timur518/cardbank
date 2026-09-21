<?php

namespace App\Filament\Admin\Resources\Notifications\Tables;

use App\Enums\NotificationType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Уведомлений пока нет')
            ->emptyStateDescription('Здесь появится история уведомлений, показанных пользователям в личном кабинете.')
            ->emptyStateIcon('heroicon-o-bell-alert')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить уведомление'),
            ])
            ->columns([
                TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Категория')
                    ->badge(),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->limit(50),
                IconColumn::make('read_at')
                    ->label('Прочитано')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->read_at),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Категория')
                    ->options(NotificationType::class),
                TernaryFilter::make('read_at')
                    ->label('Прочитано')
                    ->nullable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
