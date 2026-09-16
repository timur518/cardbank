<?php

namespace App\Filament\Admin\Resources\ComplianceAlerts\Tables;

use App\Enums\CardStatus;
use App\Enums\ComplianceAlertStatus;
use App\Models\CardStatusHistory;
use App\Models\ComplianceAlert;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ComplianceAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Предупреждений пока нет')
            ->emptyStateDescription('Здесь появятся сработавшие правила обнаружения подозрительной активности.')
            ->emptyStateIcon('heroicon-o-bell-alert')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('rule.name')->label('Правило'),
                TextColumn::make('user.email')
                    ->label('Пользователь/карта')
                    ->description(fn (ComplianceAlert $record) => $record->card?->masked_number)
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Дата срабатывания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ComplianceAlertStatus::class),
                SelectFilter::make('rule_id')
                    ->label('Правило')
                    ->relationship('rule', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('confirm')
                    ->label('Подтвердить нарушение')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->visible(fn (ComplianceAlert $record) => $record->status === ComplianceAlertStatus::NeedsReview)
                    ->requiresConfirmation()
                    ->schema([
                        Toggle::make('freeze_card')
                            ->label('Заморозить карту')
                            ->visible(fn (ComplianceAlert $record) => $record->card?->status === CardStatus::Active),
                        Toggle::make('block_user')
                            ->label('Заблокировать пользователя')
                            ->visible(fn (ComplianceAlert $record) => $record->user && ! $record->user->is_blocked),
                    ])
                    ->action(function (ComplianceAlert $record, array $data) {
                        $record->update([
                            'status' => ComplianceAlertStatus::Confirmed,
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);

                        if (($data['freeze_card'] ?? false) && $record->card) {
                            CardStatusHistory::create([
                                'card_id' => $record->card->id,
                                'old_status' => $record->card->status,
                                'new_status' => CardStatus::Frozen,
                                'changed_by' => auth()->id(),
                                'reason' => "Заморожена из предупреждения #{$record->id}",
                            ]);

                            $record->card->update(['status' => CardStatus::Frozen]);
                        }

                        if (($data['block_user'] ?? false) && $record->user) {
                            $record->user->update([
                                'is_blocked' => true,
                                'block_reason' => "Заблокирован из предупреждения #{$record->id}",
                            ]);
                        }

                        Notification::make()->title('Нарушение подтверждено')->success()->send();
                    }),

                Action::make('falsePositive')
                    ->label('Ложное срабатывание')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (ComplianceAlert $record) => $record->status === ComplianceAlertStatus::NeedsReview)
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('comment')->label('Комментарий'),
                    ])
                    ->action(function (ComplianceAlert $record) {
                        $record->update([
                            'status' => ComplianceAlertStatus::FalsePositive,
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);

                        Notification::make()->title('Отмечено как ложное срабатывание')->success()->send();
                    }),
            ]);
    }
}
