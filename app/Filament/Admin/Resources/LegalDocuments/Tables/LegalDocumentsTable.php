<?php

namespace App\Filament\Admin\Resources\LegalDocuments\Tables;

use App\Enums\LegalDocumentType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegalDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('effective_at', 'desc')
            ->emptyStateHeading('Документов пока нет')
            ->emptyStateDescription('Добавьте первую версию условий использования, политики конфиденциальности или условий возврата.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить версию'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Тип документа')
                    ->badge(),
                TextColumn::make('version')
                    ->label('Версия')
                    ->searchable(),
                TextColumn::make('effective_at')
                    ->label('Дата вступления в силу')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип документа')
                    ->options(LegalDocumentType::class),
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
