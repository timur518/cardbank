<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class NotificationSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Настройки уведомлений';

    protected static ?string $title = 'Настройки уведомлений';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.admin.pages.notification-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public const KEYS = [
        'notifications_sender_email',
        'notifications_sender_name',
        'notifications_smtp_host',
        'notifications_smtp_port',
        'notifications_smtp_username',
        'notifications_smtp_password',
        'notifications_bot_token',
        'notifications_notify_chat_id',
        'notifications_push_public_key',
        'notifications_push_private_key',
    ];

    public function mount(): void
    {
        $this->form->fill(Setting::getMany(self::KEYS));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Каналы')
                    ->tabs([
                        Tabs\Tab::make('Email')
                            ->schema([
                                TextInput::make('notifications_sender_email')
                                    ->label('Адрес отправителя')
                                    ->email(),
                                TextInput::make('notifications_sender_name')
                                    ->label('Имя отправителя'),
                                TextInput::make('notifications_smtp_host')
                                    ->label('SMTP-сервер'),
                                TextInput::make('notifications_smtp_port')
                                    ->label('SMTP-порт')
                                    ->numeric(),
                                TextInput::make('notifications_smtp_username')
                                    ->label('SMTP-логин'),
                                TextInput::make('notifications_smtp_password')
                                    ->label('SMTP-пароль')
                                    ->password()
                                    ->revealable(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Telegram')
                            ->schema([
                                TextInput::make('notifications_bot_token')
                                    ->label('Токен бота')
                                    ->password()
                                    ->revealable(),
                                TextInput::make('notifications_notify_chat_id')
                                    ->label('Чат или канал для системных оповещений'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Push-уведомления')
                            ->schema([
                                TextInput::make('notifications_push_public_key')
                                    ->label('Публичный ключ'),
                                TextInput::make('notifications_push_private_key')
                                    ->label('Приватный ключ')
                                    ->password()
                                    ->revealable(),
                            ])
                            ->columns(2),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()->title('Настройки уведомлений сохранены')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Тестовое письмо')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->schema([
                    TextInput::make('to')->label('Email получателя')->email()->required(),
                ])
                ->action(function (array $data) {
                    Notification::make()
                        ->title("Тестовое письмо отправлено на {$data['to']}")
                        ->success()
                        ->send();
                }),

            Action::make('testTelegram')
                ->label('Тестовое сообщение в Telegram')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->schema([
                    TextInput::make('chat_id')->label('Чат или канал')->required(),
                ])
                ->action(function (array $data) {
                    Notification::make()
                        ->title("Тестовое сообщение отправлено в чат {$data['chat_id']}")
                        ->success()
                        ->send();
                }),

            Action::make('testPush')
                ->label('Тестовый push')
                ->icon('heroicon-o-device-phone-mobile')
                ->color('gray')
                ->action(function () {
                    Notification::make()
                        ->title('Тестовое push-уведомление отправлено')
                        ->success()
                        ->send();
                }),
        ];
    }
}
