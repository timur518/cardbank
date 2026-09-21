<?php

namespace App\Filament\Admin\Resources\Notifications;

use App\Filament\Admin\Resources\Notifications\Pages\CreateNotification;
use App\Filament\Admin\Resources\Notifications\Pages\EditNotification;
use App\Filament\Admin\Resources\Notifications\Pages\ListNotifications;
use App\Filament\Admin\Resources\Notifications\Pages\ViewNotification;
use App\Filament\Admin\Resources\Notifications\Schemas\NotificationForm;
use App\Filament\Admin\Resources\Notifications\Schemas\NotificationInfolist;
use App\Filament\Admin\Resources\Notifications\Tables\NotificationsTable;
use App\Models\Notification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|\UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?string $navigationLabel = 'Уведомления';

    protected static ?string $modelLabel = 'Уведомление';

    protected static ?string $pluralModelLabel = 'Уведомления';

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return NotificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NotificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotifications::route('/'),
            'create' => CreateNotification::route('/create'),
            'view' => ViewNotification::route('/{record}'),
            'edit' => EditNotification::route('/{record}/edit'),
        ];
    }
}
