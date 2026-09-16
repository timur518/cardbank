<?php

namespace App\Filament\Admin\Resources\NotificationTemplates;

use App\Filament\Admin\Resources\NotificationTemplates\Pages\CreateNotificationTemplate;
use App\Filament\Admin\Resources\NotificationTemplates\Pages\EditNotificationTemplate;
use App\Filament\Admin\Resources\NotificationTemplates\Pages\ListNotificationTemplates;
use App\Filament\Admin\Resources\NotificationTemplates\Pages\ViewNotificationTemplate;
use App\Filament\Admin\Resources\NotificationTemplates\Schemas\NotificationTemplateForm;
use App\Filament\Admin\Resources\NotificationTemplates\Schemas\NotificationTemplateInfolist;
use App\Filament\Admin\Resources\NotificationTemplates\Tables\NotificationTemplatesTable;
use App\Models\NotificationTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?string $navigationLabel = 'Шаблоны сообщений';

    protected static ?string $modelLabel = 'Шаблон сообщения';

    protected static ?string $pluralModelLabel = 'Шаблоны сообщений';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return NotificationTemplateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NotificationTemplateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotificationTemplates::route('/'),
            'create' => CreateNotificationTemplate::route('/create'),
            'view' => ViewNotificationTemplate::route('/{record}'),
            'edit' => EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
