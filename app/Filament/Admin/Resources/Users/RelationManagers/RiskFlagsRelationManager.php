<?php

namespace App\Filament\Admin\Resources\Users\RelationManagers;

use App\Enums\RiskFlagType;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RiskFlagsRelationManager extends RelationManager
{
    protected static string $relationship = 'riskFlags';

    protected static ?string $title = 'Пометки о риске';

    protected static ?string $modelLabel = 'пометка о риске';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Тип пометки')
                    ->options(RiskFlagType::class)
                    ->required(),
                Textarea::make('comment')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->emptyStateHeading('Замечаний нет')
            ->emptyStateDescription('У этого пользователя пока нет отметок о подозрительной активности.')
            ->emptyStateIcon('heroicon-o-flag')
            ->columns([
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50)
                    ->placeholder('—'),
                TextColumn::make('creator.name')
                    ->label('Кто добавил')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i'),
                IconColumn::make('is_active')
                    ->label('Активна')
                    ->state(fn ($record) => is_null($record->removed_at))
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('remove')
                    ->label('Снять пометку')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn ($record) => is_null($record->removed_at))
                    ->requiresConfirmation()
                    ->action(function (Model $record) {
                        $record->update(['removed_at' => now()]);
                        Notification::make()->title('Пометка о риске снята')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ]);
    }
}
