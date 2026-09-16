<?php

namespace App\Filament\Admin\Resources\Expenses\Tables;

use App\Enums\ExpenseCategory;
use App\Models\Expense;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->emptyStateHeading('Расходов пока нет')
            ->emptyStateDescription('Здесь появятся все расходы компании: оплата провайдерам, зарплаты, реклама и прочее.')
            ->emptyStateIcon('heroicon-o-arrow-trending-down')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('date')->label('Дата')->date('d.m.Y')->sortable(),
                TextColumn::make('category')
                    ->label('Статья расходов')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money(fn (Expense $record) => $record->currency)
                    ->sortable(),
                TextColumn::make('amount_usd')
                    ->label('Сумма в $')
                    ->money('USD')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('creator.name')
                    ->label('Кто внёс')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Статья расходов')
                    ->options(ExpenseCategory::class),
            ])
            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()->label(''),
                DeleteAction::make()->label(''),
            ]);
    }
}
