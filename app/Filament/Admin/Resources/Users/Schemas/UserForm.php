<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Enums\ActiveStatus;
use App\Enums\KycStatus;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Личные данные')
                    ->columns(2)
                    ->schema([
                        TextInput::make('last_name')
                            ->label('Фамилия')
                            ->maxLength(255),
                        TextInput::make('first_name')
                            ->label('Имя')
                            ->maxLength(255),
                        TextInput::make('middle_name')
                            ->label('Отчество')
                            ->maxLength(255),
                        DatePicker::make('date_of_birth')
                            ->label('Дата рождения')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                Section::make('Статус')
                    ->columns(2)
                    ->schema([
                        Select::make('kyc_status')
                            ->label('Статус проверки личности')
                            ->options(KycStatus::class)
                            ->required(),
                        Toggle::make('is_blocked')
                            ->label('Заблокирован')
                            ->live(),
                        Textarea::make('block_reason')
                            ->label('Причина блокировки')
                            ->visible(fn ($get) => (bool) $get('is_blocked'))
                            ->columnSpanFull(),
                    ]),

                Section::make('Данные о регистрации')
                    ->columns(2)
                    ->schema([
                        TextInput::make('utm_source')
                            ->label('Источник (utm_source)')
                            ->maxLength(255),
                        TextInput::make('utm_medium')
                            ->label('Канал (utm_medium)')
                            ->maxLength(255),
                        TextInput::make('utm_campaign')
                            ->label('Кампания (utm_campaign)')
                            ->maxLength(255),
                        TextInput::make('utm_content')
                            ->label('Метка (utm_content)')
                            ->maxLength(255),
                        TextInput::make('referral_code')
                            ->label('Код приглашения')
                            ->maxLength(255),
                        TextInput::make('invite_code')
                            ->label('Реферальный код')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                // Оферта BitBanker и разрешённые способы оплаты — в обычном состоянии оба
                // заполняются самим пользователем в личном кабинете (принятие оферты +
                // успешная регистрация в BitBanker). Здесь — возможность вручного вмешательства
                // со стороны админа (включить оферту вручную или ограничить пользователя
                // конкретными способами оплаты в обход логики в BitbankerClientService).
                Section::make('Настройки платежей')
                    ->columns(2)
                    ->schema([
                        Toggle::make('bitbanker_offer_accepted_at')
                            ->label('Оферта BitBanker принята')
                            ->helperText('Включено — пользователь уже принял оферту (или админ сделал это вручную). Выключение сбрасывает дату принятия — при следующем включении пользователю придётся принять её заново.')
                            ->formatStateUsing(fn (mixed $state) => (bool) $state)
                            ->dehydrateStateUsing(fn (bool $state, ?User $record) => $state ? ($record?->bitbanker_offer_accepted_at ?? now()) : null),
                        Select::make('allowedPaymentMethods')
                            ->label('Разрешённые способы оплаты')
                            ->relationship(
                                name: 'allowedPaymentMethods',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('status', ActiveStatus::Active),
                            )
                            ->multiple()
                            ->preload()
                            ->helperText('Ничего не выбрано — ограничений нет, пользователю доступны все активные способы оплаты, кроме BitBanker — он становится доступен только после успешной регистрации или при выборе здесь вручную.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
