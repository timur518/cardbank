<?php

namespace App\Filament\Admin\Resources\ComplianceRules\Tables;

use App\Enums\ComplianceRuleAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ComplianceRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Правил пока нет')
            ->emptyStateDescription('Добавьте первое правило обнаружения подозрительной активности.')
            ->emptyStateIcon('heroicon-o-shield-exclamation')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить правило'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('condition')
                    ->label('Условие срабатывания')
                    ->limit(50),
                TextColumn::make('action')
                    ->label('Действие при срабатывании')
                    ->badge(),
                IconColumn::make('active')
                    ->label('Активно')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Действие')
                    ->options(ComplianceRuleAction::class),
                TernaryFilter::make('active')
                    ->label('Активно'),
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
