<?php

namespace App\Filament\Admin\Resources\Users\RelationManagers;

use App\Services\Integrations\Bitbanker\BitbankerClientService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Состояние регистрации пользователя в BitBanker — у пользователя не больше
 * одной записи (см. User::bitbankerClient(), HasOne), поэтому таблица всегда
 * показывает 0 или 1 строку. Запись создаётся/обновляется кодом
 * (BitbankerController::accept(), вебхук событий BitBanker, фоновая команда
 * bitbanker:sync-client-status) — админ здесь ничего не создаёт и не
 * редактирует вручную, только опрашивает актуальный статус кнопкой
 * «Обновить статус» (BitbankerClientService::refreshStatus()).
 */
class BitbankerClientRelationManager extends RelationManager
{
    protected static string $relationship = 'bitbankerClient';

    protected static ?string $title = 'BitBanker';

    protected static ?string $modelLabel = 'клиент BitBanker';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('external_client_id')
            ->emptyStateHeading('Клиент не зарегистрирован в BitBanker')
            ->emptyStateDescription('Появится после того, как пользователь примет оферту и пройдёт регистрацию в BitBanker.')
            ->emptyStateIcon('heroicon-o-qr-code')
            ->columns([
                TextColumn::make('external_client_id')
                    ->label('client_id в BitBanker'),
                IconColumn::make('is_verified_for_sbp')
                    ->label('Разрешена оплата по СБП')
                    ->boolean(),
                TextColumn::make('check_status')
                    ->label('Статус проверки')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('last_error')
                    ->label('Последняя ошибка')
                    ->formatStateUsing(fn (?array $state) => $state ? json_encode($state, JSON_UNESCAPED_UNICODE) : null)
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('registered_at')
                    ->label('Зарегистрирован')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),
                TextColumn::make('last_synced_at')
                    ->label('Последняя синхронизация')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('refresh_status')
                    ->label('Обновить статус')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Model $record) {
                        app(BitbankerClientService::class)->refreshStatus($record);

                        Notification::make()->title('Статус BitBanker обновлён')->success()->send();
                    }),
            ]);
    }
}
