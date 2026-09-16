<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ComplianceAlertStatus;
use App\Filament\Admin\Resources\ComplianceAlerts\ComplianceAlertResource;
use App\Models\ComplianceAlert;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * «Активные предупреждения» — самые важные сработавшие предупреждения системы
 * безопасности, требующие внимания, с переходом в раздел «Комплаенс».
 */
class ActiveAlertsWidget extends TableWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Активные предупреждения')
            ->query(
                ComplianceAlert::query()
                    ->with(['rule', 'user', 'card'])
                    ->where('status', ComplianceAlertStatus::NeedsReview)
                    ->latest('created_at')
            )
            ->paginationMode(PaginationMode::Simple)
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Активных предупреждений нет')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->columns([
                TextColumn::make('rule.name')
                    ->label('Правило'),
                TextColumn::make('user.email')
                    ->label('Пользователь/карта')
                    ->description(fn (ComplianceAlert $record) => $record->card?->masked_number)
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Дата срабатывания')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (ComplianceAlert $record) => ComplianceAlertResource::getUrl('view', ['record' => $record])),
            ])
            ->headerActions([
                Action::make('viewAll')
                    ->label('Все предупреждения')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn () => ComplianceAlertResource::getUrl('index')),
            ]);
    }
}
