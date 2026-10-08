<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Личные данные')
                    ->columns(6)
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('ФИО')
                            ->state(fn (User $record) => trim("{$record->last_name} {$record->first_name} {$record->middle_name}") ?: '—'),
                        TextEntry::make('date_of_birth')
                            ->label('Дата рождения')
                            ->date('d.m.Y'),
                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('phone')
                            ->label('Телефон')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Дата регистрации')
                            ->dateTime('d.m.Y H:i'),
                        TextEntry::make('last_login_at')
                            ->label('Последний вход')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                    ])
                ->columnSpanFull(),

                Section::make('Статус')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('kyc_status')
                            ->label('KYC')
                            ->badge(),
                        IconEntry::make('is_blocked')
                            ->label('Заблокирован')
                            ->boolean(),
                        TextEntry::make('block_reason')
                            ->label('Причина блокировки')
                            ->placeholder('—')
                            ->visible(fn (User $record) => $record->is_blocked),
                        IconEntry::make('has_active_risk_flag')
                            ->label('Есть пометка о риске')
                            ->state(fn (User $record) => $record->hasActiveRiskFlag())
                            ->boolean(),
                    ]),

                Section::make('Данные о регистрации')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('utm_source')->label('Источник')->placeholder('—'),
                        TextEntry::make('utm_medium')->label('Канал')->placeholder('—'),
                        TextEntry::make('utm_campaign')->label('Кампания')->placeholder('—'),
                        TextEntry::make('utm_content')->label('Метка')->placeholder('—'),
                        TextEntry::make('referral_code')->label('Код партнёра')->placeholder('—'),
                        TextEntry::make('invite_code')->label('Собственный код приглашения')->placeholder('—'),
                    ]),

                // Подробности о самой регистрации в BitBanker (is_verified_for_sbp, check_status,
                // last_error) — в BitbankerClientRelationManager, здесь только факт принятия
                // оферты и итоговый список разрешённых способов оплаты (см. BITBANKER_INTEGRATION_PLAN.md раздел 9).
                Section::make('BitBanker')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('bitbanker_offer_accepted')
                            ->label('Оферта BitBanker принята')
                            ->state(fn (User $record) => (bool) $record->bitbanker_offer_accepted_at)
                            ->boolean(),
                        TextEntry::make('bitbanker_offer_accepted_at')
                            ->label('Дата принятия оферты')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                        RepeatableEntry::make('allowedPaymentMethods')
                            ->label('Разрешённые методы оплаты')
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('name')->label('')->inlineLabel(false),
                            ])
                            ->placeholder('Нет ограничений — доступны все активные способы оплаты.'),
                    ]),
            ]);
    }
}
