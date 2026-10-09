<?php

namespace App\Filament\Admin\Forms\Components;

use App\Models\Card;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class CardSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Карта')
            ->relationship('card', 'card_number', modifyQueryUsing: fn (Builder $query) => $query->with('user'))
            ->getOptionLabelFromRecordUsing(fn (Card $record): string => self::cardLabel($record))
            ->searchable()
            ->searchPrompt('Введите полный номер карты или последние 4 цифры')
            ->preload()
            ->options(fn (): array => Card::query()
                ->with('user')
                ->latest('created_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->mapWithKeys(fn (Card $card): array => [$card->id => self::cardLabel($card)])
                ->all())
            ->getSearchResultsUsing(function (string $search): array {
                $number = preg_replace('/[\s\x{00A0}\x{202F}\-]+/u', '', $search);

                if (! preg_match('/^\d{4,19}$/', $number)) {
                    return [];
                }

                return Card::query()
                    ->with('user')
                    ->when(strlen($number) === 4,
                        fn (Builder $query) => $query->where('card_number', 'like', '%'.$number),
                        fn (Builder $query) => $query->where('card_number', $number))
                    ->latest('created_at')
                    ->orderByDesc('id')
                    ->limit(50)
                    ->get()
                    ->mapWithKeys(fn (Card $card): array => [$card->id => self::cardLabel($card)])
                    ->all();
            });
    }

    private static function cardLabel(Card $card): string
    {
        $user = $card->user;
        $name = $user ? trim("{$user->last_name} {$user->first_name} {$user->middle_name}") : '';
        $owner = $name !== '' ? $name : ($user?->name ?: $user?->email);

        return $card->masked_number.($owner ? ' — '.$owner : '');
    }
}
