<?php

namespace App\Filament\Admin\Resources\Staff\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Учётные данные сотрудника')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->revealable()
                            ->required(fn (?User $record) => $record === null)
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->maxLength(255),
                        Select::make('roles')
                            ->label('Роль')
                            ->relationship('roles', 'name')
                            ->options(fn () => Role::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Toggle::make('two_factor_enabled')
                            ->label('Дополнительная проверка входа'),
                    ]),
            ]);
    }
}
