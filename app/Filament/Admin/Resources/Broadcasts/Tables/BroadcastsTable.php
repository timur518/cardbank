<?php

namespace App\Filament\Admin\Resources\Broadcasts\Tables;

use App\Enums\BroadcastStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BroadcastsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('started_at', 'desc')
            ->emptyStateHeading('Рассылок пока нет')
            ->emptyStateDescription('Создайте первую рассылку по клиентам, выбрав шаблон и получателей.')
            ->emptyStateIcon('heroicon-o-megaphone')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить рассылку'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label('Шаблон'),
                TextColumn::make('segment_filter')
                    ->label('Кому отправлено')
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('recipients_count')
                    ->label('Получателей')
                    ->sortable(),
                TextColumn::make('delivered_count')
                    ->label('Доставлено')
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(BroadcastStatus::class),
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
