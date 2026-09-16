<?php

namespace App\Filament\Admin\Resources\Partners\Tables;

use App\Enums\PartnerStatus;
use App\Models\Partner;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Партнёров пока нет')
            ->emptyStateDescription('Здесь появятся партнёры, приглашающие новых клиентов по реферальной программе.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить партнёра'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Партнёр')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Код приглашения')
                    ->searchable(),
                TextColumn::make('referrals_count')
                    ->label('Приглашено')
                    ->sortable(),
                TextColumn::make('paying_count')
                    ->label('Из них платит')
                    ->sortable(),
                TextColumn::make('lifetime_usd')
                    ->label('Заработано всего')
                    ->money(fn (Partner $record) => 'USD')
                    ->sortable(),
                TextColumn::make('available_usd')
                    ->label('Доступно к выводу')
                    ->money(fn (Partner $record) => 'USD')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(PartnerStatus::class),
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
