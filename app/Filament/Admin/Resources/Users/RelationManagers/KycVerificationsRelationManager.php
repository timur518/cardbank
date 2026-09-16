<?php

namespace App\Filament\Admin\Resources\Users\RelationManagers;

use App\Enums\DecisionStatus;
use App\Enums\KycVerificationType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KycVerificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'kycVerifications';

    protected static ?string $title = 'KYC';

    protected static ?string $modelLabel = 'KYC';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Тип проверки')
                    ->options(KycVerificationType::class)
                    ->required(),
                Select::make('status')
                    ->label('Статус')
                    ->options(DecisionStatus::class)
                    ->required()
                    ->live(),
                Textarea::make('decline_reason')
                    ->label('Причина отказа')
                    ->visible(fn ($get) => $get('status') === DecisionStatus::Declined->value)
                    ->columnSpanFull(),
                Repeater::make('documents')
                    ->label('Документы')
                    ->visible(fn () => auth()->user()?->can('view_kyc_documents'))
                    ->schema([
                        TextInput::make('url')
                            ->label('Ссылка на документ')
                            ->url()
                            ->required(),
                    ])
                    ->columnSpanFull(),
                DateTimePicker::make('submitted_at')
                    ->label('Дата отправки на проверку'),
                DateTimePicker::make('resolved_at')
                    ->label('Дата решения'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->emptyStateHeading('Проверок ещё не было')
            ->emptyStateDescription('Здесь появится история проверки личности этого пользователя.')
            ->emptyStateIcon('heroicon-o-identification')
            ->columns([
                TextColumn::make('type')
                    ->label('Тип'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('decline_reason')
                    ->label('Причина отказа')
                    ->placeholder('—')
                    ->limit(40),
                TextColumn::make('submitted_at')
                    ->label('Дата отправки')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('resolved_at')
                    ->label('Дата решения')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
