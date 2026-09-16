<?php

namespace App\Filament\Admin\Resources\Cards\Pages;

use App\Filament\Admin\Resources\Cards\CardResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCard extends ViewRecord
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('revealSensitiveData')
                ->label('Показать реквизиты')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn () => auth()->user()?->can('view_card_sensitive_data'))
                ->schema([
                    Textarea::make('reason')
                        ->label('Причина просмотра реквизитов')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();

                    // TODO: когда будет подключён плагин журнала действий, здесь также нужно
                    // зафиксировать факт просмотра реквизитов (кто, когда, по какой причине).
                    Notification::make()
                        ->title('Реквизиты карты')
                        ->body(
                            "Номер: {$record->card_number}\n".
                            "Срок действия: {$record->expiry}\n".
                            "CVV: {$record->cvv}\n\n".
                            "Причина просмотра: {$data['reason']}"
                        )
                        ->persistent()
                        ->warning()
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
