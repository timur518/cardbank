<?php

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Enums\DecisionStatus;
use App\Enums\KycStatus;
use App\Enums\KycVerificationType;
use App\Enums\RiskFlagType;
use App\Models\KycVerification;
use App\Models\RiskFlag;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Пока нет пользователей')
            ->emptyStateDescription('Здесь появятся клиенты, как только они зарегистрируются в сервисе.')
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Добавить пользователя'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Пользователь')
                    ->state(fn (User $record) => trim("{$record->last_name} {$record->first_name}") ?: $record->email)
                    ->description(fn (User $record) => $record->phone ?? $record->email, position: 'above')
                    ->description(fn (User $record) => $record->created_at?->format('d.m.Y H:i'), position: 'bellow')
                    ->searchable(['first_name', 'last_name', 'email', 'phone'])
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('created_at', $direction)),
                TextColumn::make('cards_count')
                    ->label('Карты')
                    ->state(fn () => 0)
                    // TODO: подключить реальное количество карт, когда появится модуль «Карты».
                    ->alignCenter(),
                TextColumn::make('total_operations_sum')
                    ->label('Сумма операций')
                    ->state(fn () => '—')
                    // TODO: подключить реальную сумму операций, когда появится модуль «Финансы».
                    ->alignEnd(),
                TextColumn::make('kyc_status')
                    ->label('KYC')
                    ->badge(),
                IconColumn::make('has_active_risk_flag')
                    ->label('Риски')
                    ->state(fn (User $record) => $record->hasActiveRiskFlag())
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray'),
                IconColumn::make('is_blocked')
                    ->label('Заблокирован')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('kyc_status')
                    ->label('Статус проверки личности')
                    ->options(KycStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('toggleBlock')
                    ->label(fn (User $record) => $record->is_blocked ? 'Разбанить' : 'Бан')
                    ->icon(fn (User $record) => $record->is_blocked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn (User $record) => $record->is_blocked ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->schema(fn (User $record) => $record->is_blocked ? [] : [
                        Textarea::make('block_reason')
                            ->label('Причина блокировки')
                            ->required(),
                    ])
                    ->action(function (User $record, array $data) {
                        if ($record->is_blocked) {
                            $record->update(['is_blocked' => false, 'block_reason' => null]);
                            Notification::make()->title('Пользователь разбанен')->success()->send();
                        } else {
                            $record->update(['is_blocked' => true, 'block_reason' => $data['block_reason']]);
                            Notification::make()->title('Пользователь забанен')->success()->send();
                        }
                    }),

                Action::make('kycDecision')
                    ->label('KYC')
                    ->icon('heroicon-o-identification')
                    ->color('gray')
                    ->schema([
                        Select::make('status')
                            ->label('Решение')
                            ->options([
                                KycStatus::Approved->value => 'Одобрить',
                                KycStatus::Declined->value => 'Отклонить',
                            ])
                            ->required()
                            ->live(),
                        Textarea::make('decline_reason')
                            ->label('Причина отказа')
                            ->visible(fn ($get) => $get('status') === KycStatus::Declined->value)
                            ->required(fn ($get) => $get('status') === KycStatus::Declined->value),
                    ])
                    ->action(function (User $record, array $data) {
                        KycVerification::create([
                            'user_id' => $record->id,
                            'type' => KycVerificationType::Internal,
                            'status' => $data['status'] === KycStatus::Approved->value
                                ? DecisionStatus::Approved
                                : DecisionStatus::Declined,
                            'decline_reason' => $data['decline_reason'] ?? null,
                            'submitted_at' => now(),
                            'resolved_at' => now(),
                        ]);

                        $record->update(['kyc_status' => $data['status']]);

                        Notification::make()->title('Решение по KYC сохранено')->success()->send();
                    }),

                Action::make('addRiskFlag')
                    ->label('Замечание')
                    ->icon('heroicon-o-flag')
                    ->color('warning')
                    ->schema([
                        Select::make('type')
                            ->label('Тип пометки')
                            ->options(RiskFlagType::class)
                            ->required(),
                        Textarea::make('comment')
                            ->label('Комментарий'),
                    ])
                    ->action(function (User $record, array $data) {
                        RiskFlag::create([
                            'user_id' => $record->id,
                            'type' => $data['type'],
                            'comment' => $data['comment'] ?? null,
                            'created_by' => auth()->id(),
                        ]);

                        Notification::make()->title('Пометка о риске добавлена')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
