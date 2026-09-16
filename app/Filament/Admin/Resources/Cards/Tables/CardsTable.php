<?php

namespace App\Filament\Admin\Resources\Cards\Tables;

use App\Enums\CardStatus;
use App\Models\Card;
use App\Models\CardStatusHistory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Карт пока нет')
            ->emptyStateDescription('Здесь появятся карты, как только клиенты начнут их выпускать.')
            ->emptyStateIcon('heroicon-o-credit-card')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('provider.name')
                    ->label('Провайдер'),
                TextColumn::make('user.email')
                    ->label('Владелец')
                    ->description(fn (Card $record) => trim("{$record->user?->last_name} {$record->user?->first_name}"))
                    ->searchable(['user.email']),
                TextColumn::make('masked_number')
                    ->label('Номер карты')
                    ->description(fn (Card $record) => $record->expiry),
                TextColumn::make('balance')
                    ->label('Баланс')
                    ->money(fn (Card $record) => $record->currency)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(CardStatus::class),
                SelectFilter::make('card_product_id')
                    ->label('Продукт')
                    ->relationship('cardProduct', 'name'),
                SelectFilter::make('provider_id')
                    ->label('Провайдер')
                    ->relationship('provider', 'name'),
                Filter::make('balance')
                    ->label('Баланс')
                    ->schema([
                        TextInput::make('balance_from')->label('От')->numeric(),
                        TextInput::make('balance_to')->label('До')->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['balance_from'] ?? null, fn (Builder $q, $v) => $q->where('balance', '>=', $v))
                            ->when($data['balance_to'] ?? null, fn (Builder $q, $v) => $q->where('balance', '<=', $v));
                    }),
                Filter::make('issued_at')
                    ->label('Дата выпуска')
                    ->schema([
                        DatePicker::make('issued_from')->label('С'),
                        DatePicker::make('issued_to')->label('По'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['issued_from'] ?? null, fn (Builder $q, $v) => $q->whereDate('issued_at', '>=', $v))
                            ->when($data['issued_to'] ?? null, fn (Builder $q, $v) => $q->whereDate('issued_at', '<=', $v));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('freeze')
                    ->label('Заморозить')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (Card $record) => $record->status === CardStatus::Active)
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')->label('Причина')->required(),
                    ])
                    ->action(fn (Card $record, array $data) => self::changeStatus($record, CardStatus::Frozen, $data['reason'])),

                Action::make('unfreeze')
                    ->label('Разморозить')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(fn (Card $record) => $record->status === CardStatus::Frozen)
                    ->requiresConfirmation()
                    ->action(fn (Card $record) => self::changeStatus($record, CardStatus::Active, 'Разморожена вручную')),

                Action::make('close')
                    ->label('Закрыть')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Card $record) => $record->status !== CardStatus::Closed)
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')->label('Причина закрытия')->required(),
                    ])
                    ->action(function (Card $record, array $data) {
                        self::changeStatus($record, CardStatus::Closed, $data['reason']);
                        $record->update(['closed_at' => now()]);
                    }),

                Action::make('reissue')
                    ->label('Перевыпустить')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Будет создан запрос провайдеру на перевыпуск карты.')
                    ->action(function (Card $record) {
                        self::changeStatus($record, CardStatus::Pending, 'Запрошен перевыпуск карты');
                        Notification::make()->title('Запрос на перевыпуск карты отправлен провайдеру')->success()->send();
                    }),

                Action::make('adjustBalance')
                    ->label('Поправить баланс')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->schema([
                        TextInput::make('new_balance')
                            ->label('Новый баланс')
                            ->numeric()
                            ->required(),
                        Textarea::make('comment')
                            ->label('Комментарий (обязательно)')
                            ->required(),
                    ])
                    ->action(function (Card $record, array $data) {
                        $record->update(['balance' => $data['new_balance']]);
                        Notification::make()
                            ->title('Баланс карты обновлён вручную')
                            ->body($data['comment'])
                            ->warning()
                            ->send();
                    }),

                Action::make('requestProviderSync')
                    ->label('Запросить обновление у провайдера')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('gray')
                    ->visible(fn (Card $record) => $record->status === CardStatus::Pending)
                    ->requiresConfirmation()
                    ->action(function (Card $record) {
                        Notification::make()->title('Запрос на обновление данных отправлен провайдеру')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function changeStatus(Card $record, CardStatus $newStatus, ?string $reason = null): void
    {
        CardStatusHistory::create([
            'card_id' => $record->id,
            'old_status' => $record->status,
            'new_status' => $newStatus,
            'changed_by' => auth()->id(),
            'reason' => $reason,
        ]);

        $record->update(['status' => $newStatus]);

        Notification::make()->title('Статус карты изменён')->success()->send();
    }
}
