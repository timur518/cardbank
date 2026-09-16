<?php

namespace App\Filament\Admin\Resources\Banners\Schemas;

use App\Enums\BannerStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Баннер')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_url')
                            ->label('Изображение баннера')
                            ->image()
                            ->directory('banners')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('link_url')
                            ->label('Ссылка, куда ведёт баннер')
                            ->url()
                            ->maxLength(255),
                        DatePicker::make('start_date')
                            ->label('Дата начала показа')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        DatePicker::make('end_date')
                            ->label('Дата окончания показа')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        TextInput::make('sort')
                            ->label('Номер по порядку')
                            ->numeric()
                            ->default(0),
                        Select::make('status')
                            ->label('Статус')
                            ->options(BannerStatus::class)
                            ->default(BannerStatus::Active)
                            ->required(),
                    ]),
            ]);
    }
}
