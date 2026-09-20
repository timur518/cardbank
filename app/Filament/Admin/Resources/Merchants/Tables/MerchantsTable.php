<?php

namespace App\Filament\Admin\Resources\Merchants\Tables;

use App\Enums\MerchantCategory;
use App\Models\Merchant;
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

class MerchantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->emptyStateHeading('Мерчантов пока нет')
            ->emptyStateDescription('Добавьте мерчанта, чтобы транзакции по нему определялись автоматически по коду в описании операции.')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить мерчанта'),
            ])
            ->columns([
                TextColumn::make('logo')
                    ->label('')
                    ->state(fn (Merchant $record) => (string) $record->getKey())
                    ->formatStateUsing(fn (Merchant $record) => self::formatLogo($record))
                    ->html(),
                TextColumn::make('name')
                    ->label('Название')
                    ->description(fn (Merchant $record) => $record->code)
                    ->searchable(['name', 'code']),
                TextColumn::make('category')
                    ->label('Категория')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Добавлен')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(MerchantCategory::class),
                TernaryFilter::make('is_active')
                    ->label('Активность'),
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

    private static function formatLogo(Merchant $record): string
    {
        if (! $record->logo_svg) {
            return '<div style="width:36px;height:36px;border-radius:10px;background:#e5e7eb"></div>';
        }

        $color = $record->color ?: '#111827';

        return sprintf(
            '<div style="width:36px;height:36px;border-radius:10px;background:%s;display:flex;align-items:center;justify-content:center"><svg viewBox="0 0 24 24" width="20" height="20" fill="#fff">%s</svg></div>',
            e($color),
            $record->logo_svg
        );
    }
}
