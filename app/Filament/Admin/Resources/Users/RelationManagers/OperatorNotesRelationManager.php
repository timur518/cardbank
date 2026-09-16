<?php

namespace App\Filament\Admin\Resources\Users\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OperatorNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'operatorNotes';

    protected static ?string $title = 'Заметки оператора';

    protected static ?string $modelLabel = 'заметка';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('text')
                    ->label('Текст заметки')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->emptyStateHeading('Заметок пока нет')
            ->emptyStateDescription('Оставьте первую заметку оператора по этому пользователю.')
            ->emptyStateIcon('heroicon-o-pencil-square')
            ->columns([
                TextColumn::make('text')
                    ->label('Текст')
                    ->wrap(),
                TextColumn::make('creator.name')
                    ->label('Кто добавил')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
