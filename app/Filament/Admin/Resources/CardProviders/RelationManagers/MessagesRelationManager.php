<?php

namespace App\Filament\Admin\Resources\CardProviders\RelationManagers;

use App\Enums\MessageProcessingStatus;
use App\Models\ProviderMessage;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Журнал сообщений от провайдера';

    protected static ?string $modelLabel = 'сообщение';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event_type')
            ->emptyStateHeading('Сообщений пока нет')
            ->emptyStateDescription('Здесь будут появляться автоматические сообщения от провайдера о событиях: выпуск карты, зачисление денег и т.п.')
            ->emptyStateIcon('heroicon-o-inbox')
            ->columns([
                TextColumn::make('event_type')
                    ->label('Тип события'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('received_at')
                    ->label('Дата получения')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('processed_at')
                    ->label('Дата обработки')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(MessageProcessingStatus::class),
            ])
            ->headerActions([
                // Раздел ведётся автоматически по данным от провайдера, ручное создание не предусмотрено.
            ])
            ->recordActions([
                Action::make('viewEvent')
                    ->label('Посмотреть событие')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (ProviderMessage $record): string => 'Событие: '.$record->event_type)
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть')
                    ->fillForm(function (ProviderMessage $record): array {
                        $raw = (string) ($record->getRawOriginal('payload') ?? '');
                        $decoded = json_decode($raw);

                        return ['payload' => json_last_error() === JSON_ERROR_NONE
                            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION)
                            : $raw];
                    })
                    ->schema([
                        CodeEditor::make('payload')
                            ->label('Тело уведомления')
                            ->language(Language::Json)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
