<?php

namespace App\Filament\Admin\Resources\Merchants\Schemas;

use App\Enums\MerchantCategory;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MerchantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Мерчант')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Код мерчанта')
                            ->required()
                            ->maxLength(191)
                            ->unique(ignoreRecord: true)
                            ->placeholder('AUGMENT CODE')
                            ->helperText('Подстрока в описании операции у провайдера. Например, для "AUGMENT CODE           PALO ALTO     USA" — "AUGMENT CODE".'),
                        TextInput::make('name')
                            ->label('Название мерчанта')
                            ->required()
                            ->maxLength(191)
                            ->placeholder('Augment AI'),
                        Select::make('category')
                            ->label('Категория')
                            ->options(MerchantCategory::class)
                            ->required()
                            ->searchable(),
                        ColorPicker::make('color')
                            ->label('Цвет'),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->helperText('Отключённые мерчанты не участвуют в определении магазина по транзакции.')
                            ->default(true),
                        CodeEditor::make('logo_svg')
                            ->label('Логотип (SVG-код)')
                            ->language(Language::Html)
                            ->helperText('Только содержимое <svg> — теги <path> и т.п., без обёртки <svg>. Квадрат 24×24, заливка текущим цветом (fill="currentColor").')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
