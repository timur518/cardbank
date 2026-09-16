<?php

namespace App\Filament\Admin\Resources\ComplianceRules\Schemas;

use App\Enums\ComplianceRuleAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComplianceRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Правило')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название правила')
                            ->required()
                            ->maxLength(255),
                        Select::make('action')
                            ->label('Действие при срабатывании')
                            ->options(ComplianceRuleAction::class)
                            ->default(ComplianceRuleAction::Warn)
                            ->required(),
                        TextInput::make('condition')
                            ->label('Условие срабатывания')
                            ->helperText('Например: слишком много операций за короткое время, крупная сумма операции, несколько карт на один документ, операция из подозрительной страны.')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('threshold')
                            ->label('Пороговое значение')
                            ->numeric(),
                        Toggle::make('active')
                            ->label('Правило активно')
                            ->default(true),
                    ]),
            ]);
    }
}
