<?php

namespace App\Filament\Admin\Resources\CardProviders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReserveTopupsRelationManager extends RelationManager
{
    protected static string $relationship = 'reserveTopups';

    protected static ?string $title = 'История пополнения резерва';

    protected static ?string $modelLabel = 'пополнение резерва';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->label('Сумма пополнения, $')
                    ->numeric()
                    ->required()
                    ->prefix('$'),
                Textarea::make('comment')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->emptyStateHeading('Пополнений ещё не было')
            ->emptyStateDescription('Здесь будет история пополнения резерва этого провайдера.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->columns([
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('USD'),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->placeholder('—')
                    ->limit(50),
                TextColumn::make('creator.name')
                    ->label('Кто внёс')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    })
                    ->after(function ($record) {
                        $record->provider->increment('reserve_balance_usd', $record->amount);
                    }),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
