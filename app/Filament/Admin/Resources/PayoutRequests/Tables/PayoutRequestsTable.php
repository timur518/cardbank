<?php

namespace App\Filament\Admin\Resources\PayoutRequests\Tables;

use App\Enums\PayoutDestination;
use App\Enums\PayoutRequestStatus;
use App\Models\PayoutRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayoutRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Заявок на выплату пока нет')
            ->emptyStateDescription('Здесь появятся заявки партнёров на вывод заработанных денег.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('partner.user.email')->label('Партнёр'),
                TextColumn::make('amount_usd')
                    ->label('Сумма')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('destination')
                    ->label('Куда вывести')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(PayoutRequestStatus::class),
                SelectFilter::make('destination')
                    ->label('Куда вывести')
                    ->options(PayoutDestination::class),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PayoutRequest $record) => $record->status === PayoutRequestStatus::Pending)
                    ->requiresConfirmation()
                    ->action(function (PayoutRequest $record) {
                        $record->update(['status' => PayoutRequestStatus::Approved]);

                        Notification::make()->title('Заявка одобрена')->success()->send();
                    }),

                Action::make('markPaid')
                    ->label('Выплачено')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (PayoutRequest $record) => $record->status === PayoutRequestStatus::Approved)
                    ->requiresConfirmation()
                    ->modalDescription('Подтвердите, что выплата партнёру уже произведена вручную (например, банковским переводом).')
                    ->action(function (PayoutRequest $record) {
                        $record->update([
                            'status' => PayoutRequestStatus::Paid,
                            'resolved_at' => now(),
                        ]);

                        Notification::make()->title('Заявка отмечена как выплаченная')->success()->send();
                    }),

                Action::make('decline')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PayoutRequest $record) => $record->status === PayoutRequestStatus::Pending)
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('admin_note')
                            ->label('Комментарий')
                            ->required(),
                    ])
                    ->action(function (PayoutRequest $record, array $data) {
                        $record->update([
                            'status' => PayoutRequestStatus::Declined,
                            'admin_note' => $data['admin_note'],
                            'resolved_at' => now(),
                        ]);

                        Notification::make()->title('Заявка отклонена')->success()->send();
                    }),

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
