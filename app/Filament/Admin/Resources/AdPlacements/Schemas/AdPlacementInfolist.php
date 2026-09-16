<?php

namespace App\Filament\Admin\Resources\AdPlacements\Schemas;

use App\Models\AdPlacement;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdPlacementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Размещение')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label('Название'),
                        TextEntry::make('channel')->label('Площадка'),
                        TextEntry::make('creator.name')->label('Кто добавил')->placeholder('—'),
                        TextEntry::make('start_date')->label('Начало показа')->date('d.m.Y')->placeholder('—'),
                        TextEntry::make('end_date')->label('Окончание показа')->date('d.m.Y')->placeholder('—'),
                        TextEntry::make('cost_amount')->label('Стоимость размещения')->money(fn (AdPlacement $record) => $record->currency),
                        TextEntry::make('tracking_link')->label('Рекламная ссылка')->copyable()->columnSpanFull(),
                        TextEntry::make('comment')->label('Комментарий')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Статистика')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('clicks_count')->label('Переходов по ссылке'),
                        TextEntry::make('registrations_count')->label('Регистраций'),
                        TextEntry::make('paid_issuances_count')->label('Оплаченных выпусков карт'),
                        TextEntry::make('revenue_amount')->label('Заработано')->money(fn (AdPlacement $record) => $record->currency),
                        TextEntry::make('roi_label')->label('Окупаемость'),
                    ]),
            ]);
    }
}
