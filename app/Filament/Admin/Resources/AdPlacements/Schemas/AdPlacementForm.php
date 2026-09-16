<?php

namespace App\Filament\Admin\Resources\AdPlacements\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdPlacementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Размещение')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название размещения')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('channel')
                            ->label('Площадка или канал')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('start_date')
                            ->label('Дата начала показа')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        DatePicker::make('end_date')
                            ->label('Дата окончания показа')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        TextInput::make('cost_amount')
                            ->label('Стоимость размещения, ₽')
                            ->numeric()
                            ->prefix('₽')
                            ->required(),
                        Textarea::make('comment')
                            ->label('Комментарий / описание рекламного материала')
                            ->columnSpanFull(),
                    ]),

                Section::make('Метки отслеживания (UTM)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('utm_source')
                            ->label('Метка источника перехода')
                            ->maxLength(255),
                        TextInput::make('utm_medium')
                            ->label('Метка канала перехода')
                            ->maxLength(255),
                        TextInput::make('utm_campaign')
                            ->label('Метка рекламной кампании')
                            ->maxLength(255),
                        TextInput::make('utm_content')
                            ->label('Дополнительная метка')
                            ->maxLength(255),
                    ])
                    ->footer('Рекламная ссылка с этими метками будет сгенерирована автоматически после сохранения.'),
            ]);
    }
}
