<?php

namespace App\Filament\Admin\Resources\Notifications\RelationManagers;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Список пользователей, просмотревших общее уведомление (без получателя, см.
 * Notification::notifyAll()) — показывается только для таких уведомлений: у личных
 * (с конкретным user_id) прочтение — это просто поле read_at на самой записи, видимое
 * прямо в форме/инфолисте, отдельная вкладка с одним пользователем не нужна.
 */
class ReadsRelationManager extends RelationManager
{
    protected static string $relationship = 'reads';

    protected static ?string $title = 'Просмотрели';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Notification && $ownerRecord->user_id === null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.email')
            ->defaultSort('read_at', 'desc')
            ->emptyStateHeading('Пока никто не просмотрел')
            ->emptyStateDescription('Здесь появятся пользователи, открывшие это уведомление в личном кабинете.')
            ->emptyStateIcon('heroicon-o-eye')
            ->columns([
                TextColumn::make('user.email')
                    ->label('Пользователь'),
                TextColumn::make('read_at')
                    ->label('Дата просмотра')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                // Записи появляются автоматически при открытии уведомления в ЛК — ручное
                // создание не предусмотрено.
            ])
            ->recordActions([]);
    }
}
