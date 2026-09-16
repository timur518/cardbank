<?php

namespace App\Filament\Admin\Resources\Banners\Tables;

use App\Enums\BannerStatus;
use App\Models\Banner;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->emptyStateHeading('Баннеров пока нет')
            ->emptyStateDescription('Добавьте первый баннер для показа на сайте.')
            ->emptyStateIcon('heroicon-o-photo')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить баннер'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                ImageColumn::make('image_url')
                    ->label('Изображение')
                    ->square(),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable(),
                TextColumn::make('period')
                    ->label('Период показа')
                    ->state(fn (Banner $record) => match (true) {
                        $record->start_date && $record->end_date => $record->start_date->format('d.m.Y') . ' — ' . $record->end_date->format('d.m.Y'),
                        (bool) $record->start_date => 'С ' . $record->start_date->format('d.m.Y'),
                        (bool) $record->end_date => 'До ' . $record->end_date->format('d.m.Y'),
                        default => 'Бессрочно',
                    }),
                TextColumn::make('sort')
                    ->label('Порядок')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(BannerStatus::class),
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
