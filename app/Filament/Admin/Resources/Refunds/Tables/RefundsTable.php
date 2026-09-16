<?php

namespace App\Filament\Admin\Resources\Refunds\Tables;

use App\Enums\DecisionStatus;
use App\Enums\ExpenseCategory;
use App\Models\Expense;
use App\Models\Refund;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RefundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Заявок на возврат пока нет')
            ->emptyStateDescription('Здесь появятся заявки на возврат остатка при закрытии карты и оспариваемые платежи.')
            ->emptyStateIcon('heroicon-o-receipt-refund')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('user.email')->label('Пользователь'),
                TextColumn::make('card.masked_number')->label('Карта'),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money(fn (Refund $record) => $record->currency)
                    ->sortable(),
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
                    ->options(DecisionStatus::class),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Refund $record) => $record->status === DecisionStatus::Pending)
                    ->requiresConfirmation()
                    ->schema([
                        Toggle::make('is_paid_for_company')
                            ->label('Возврат платный для компании')
                            ->helperText('Если включено, сумма будет добавлена в «Расходы» отдельной строкой со статьёй «Платный возврат».')
                            ->default(true),
                    ])
                    ->action(function (Refund $record, array $data) {
                        $record->update([
                            'status' => DecisionStatus::Approved,
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);

                        if ($data['is_paid_for_company'] ?? false) {
                            Expense::create([
                                'date' => now(),
                                'category' => ExpenseCategory::PaidRefund,
                                'amount' => $record->amount,
                                'card_id' => $record->card_id,
                                'comment' => "Платный возврат по заявке #{$record->id}",
                                'created_by' => auth()->id(),
                            ]);
                        }

                        Notification::make()->title('Возврат одобрен')->success()->send();
                    }),

                Action::make('decline')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Refund $record) => $record->status === DecisionStatus::Pending)
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')
                            ->label('Причина отказа')
                            ->required(),
                    ])
                    ->action(function (Refund $record, array $data) {
                        $record->update([
                            'status' => DecisionStatus::Declined,
                            'reason' => $data['reason'],
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);

                        Notification::make()->title('Возврат отклонён')->success()->send();
                    }),
            ]);
    }
}
