<?php

namespace App\Filament\Admin\Resources\CardProviders\RelationManagers;

use App\Enums\DiscrepancyStatus;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DiscrepanciesRelationManager extends RelationManager
{
    protected static string $relationship = 'discrepancies';

    protected static ?string $title = 'Расхождения при сверке';

    protected static ?string $modelLabel = 'расхождение';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->emptyStateHeading('Расхождений нет')
            ->emptyStateDescription('Здесь появятся случаи, когда данные нашей системы не совпадают с данными провайдера.')
            ->emptyStateIcon('heroicon-o-exclamation-triangle')
            ->columns([
                TextColumn::make('type')
                    ->label('Тип расхождения'),
                TextColumn::make('expected_amount')
                    ->label('Ожидаемая сумма')
                    ->money('USD')
                    ->placeholder('—'),
                TextColumn::make('actual_amount')
                    ->label('Фактическая сумма')
                    ->money('USD')
                    ->placeholder('—'),
                TextColumn::make('card.masked_number')
                    ->label('Карта')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('resolver.name')
                    ->label('Кто разобрал')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(DiscrepancyStatus::class),
            ])
            ->recordActions([
                Action::make('resolve')
                    ->label('Отметить разобранным')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Model $record) => $record->status === DiscrepancyStatus::Open)
                    ->requiresConfirmation()
                    ->action(function (Model $record) {
                        $record->update([
                            'status' => DiscrepancyStatus::Resolved,
                            'resolved_by' => auth()->id(),
                        ]);

                        Notification::make()->title('Расхождение отмечено как разобранное')->success()->send();
                    }),
            ]);
    }
}
