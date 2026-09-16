<?php

namespace App\Filament\Admin\Resources\Staff\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Пока нет сотрудников')
            ->emptyStateDescription('Назначьте роль пользователю, чтобы дать ему доступ в панель управления.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Добавить сотрудника'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Роль')
                    ->badge(),
                IconColumn::make('two_factor_enabled')
                    ->label('Доп. проверка входа')
                    ->boolean(),
                TextColumn::make('last_login_at')
                    ->label('Последний вход')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Роль')
                    ->relationship('roles', 'name')
                    ->options(fn () => Role::query()->pluck('name', 'id')),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('resetTwoFactor')
                    ->label('Сбросить доп. проверку')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('gray')
                    ->visible(fn (User $record) => $record->two_factor_enabled)
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['two_factor_enabled' => false]);
                        Notification::make()->title('Дополнительная проверка входа сброшена')->success()->send();
                    }),

                Action::make('forceLogout')
                    ->label('Завершить сеансы')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        DB::table('sessions')->where('user_id', $record->id)->delete();
                        $record->update(['remember_token' => null]);
                        Notification::make()->title('Активные сеансы сотрудника завершены')->success()->send();
                    }),

                Action::make('removeRole')
                    ->label('Снять роль')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Пользователь потеряет доступ в панель управления.')
                    ->action(function (User $record) {
                        $record->syncRoles([]);
                        Notification::make()->title('Роль снята, доступ в панель отключён')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
