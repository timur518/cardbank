<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Models\CardProduct;
use App\Models\CardProvider;
use App\Models\User;
use App\Services\Integrations\CardsPro\CardsProService;
use App\Services\Integrations\CardsPro\Exceptions\CardsProException;
use Illuminate\Console\Command;

/**
 * Получить список выпущенных карт
 * php artisan cardspro:cards [код_провайдера] [--details] [--json] [--sync] [--user=1]
 *
 * Без --sync команда ничего не пишет в базу, чистый просмотр.
 * С --sync команда сравнивает список CardsPro (GET /list) с тем, что уже заведено в
 * «Карты» → «Карты» (по provider_id + provider_card_id), и для каждой недостающей
 * карты запрашивает GET /{san}/details и создаёт запись. CardsPro не возвращает
 * владельца карты — поэтому новым картам присваивается пользователь из --user (по
 * умолчанию 1) — это временная заглушка, владельца стоит скорректировать вручную после
 * синхронизации. Карта, для которой не найден «Картовый продукт» этого провайдера
 * с таким же кодом (`provider_product_code`), пропускается и не создаётся —
 * `card_product_id` обязателен, угадывать его нельзя.
 */
class CardsProListCards extends Command
{
    protected $signature = 'cardspro:cards
        {provider? : Код провайдера из «Карты» → «Провайдеры карт» (если провайдер один — можно не указывать)}
        {--details : Дополнительно запросить баланс, статус и продукт по каждой карте (по одному запросу на карту, может быть медленно)}
        {--json : Вывести сырой JSON вместо таблицы}
        {--sync : Создать в админке карты, которые есть в CardsPro, но ещё не заведены у нас}
        {--user=1 : ID пользователя, на которого оформить новые карты при --sync}';

    protected $description = 'Получить из CardsPro список выпущенных карт и при --sync завести недостающие в админку';

    public function handle(): int
    {
        $provider = $this->resolveProvider();

        if (! $provider) {
            return self::FAILURE;
        }

        $owner = null;

        if ($this->option('sync')) {
            $owner = User::find((int) $this->option('user'));

            if (! $owner) {
                $this->error('Пользователь с ID ' . $this->option('user') . ' не найден. Укажите существующего: --user=ID');

                return self::FAILURE;
            }
        }

        $service = CardsProService::for($provider);

        try {
            $cards = $this->fetchAllCards($service);

            if ($this->option('sync')) {
                return $this->syncCards($service, $provider, $cards, $owner);
            }

            if ($this->option('details')) {
                $cards = $this->attachDetails($service, $cards);
            }
        } catch (CardsProException $e) {
            $this->error("Ошибка CardsPro ({$e->statusCode()}): {$e->getMessage()}");

            if ($e->responseBody() !== []) {
                $this->line(json_encode($e->responseBody(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line(json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->renderTable($cards, $provider);

        return self::SUCCESS;
    }

    protected function resolveProvider(): ?CardProvider
    {
        $code = $this->argument('provider');

        if ($code) {
            $provider = CardProvider::where('code', $code)->first();

            if (! $provider) {
                $this->error("Провайдер с кодом «{$code}» не найден.");
            }

            return $provider;
        }

        $providers = CardProvider::query()->where('status', 'active')->get();

        if ($providers->isEmpty()) {
            $this->error('Нет ни одного активного провайдера. Укажите код явно: php artisan cardspro:cards <код>');

            return null;
        }

        if ($providers->count() > 1) {
            $this->error('Активных провайдеров несколько, укажите код явно: ' . $providers->pluck('code')->implode(', '));

            return null;
        }

        return $providers->first();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAllCards(CardsProService $service): array
    {
        $cards = [];
        $offset = 0;
        $limit = 100;

        do {
            $page = $service->getCardList($limit, $offset);
            $batch = $page['cards'] ?? [];
            $cards = array_merge($cards, $batch);
            $total = $page['count'] ?? count($cards);
            $offset += $limit;
        } while ($batch !== [] && count($cards) < $total);

        return $cards;
    }

    /**
     * @param  array<int, array<string, mixed>>  $cards
     * @return array<int, array<string, mixed>>
     */
    protected function attachDetails(CardsProService $service, array $cards): array
    {
        if ($cards === []) {
            return [];
        }

        $result = [];
        $bar = $this->output->createProgressBar(count($cards));
        $bar->start();

        foreach ($cards as $card) {
            try {
                $details = $service->getCardDetails((string) $card['san']);
                $result[] = array_merge($card, $details);
            } catch (CardsProException $e) {
                $result[] = $card + ['_error' => $e->getMessage()];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return $result;
    }

    /**
     * @param  array<int, array<string, mixed>>  $cards
     */
    protected function syncCards(CardsProService $service, CardProvider $provider, array $cards, User $owner): int
    {
        $existingSans = Card::where('provider_id', $provider->id)
            ->whereNotNull('provider_card_id')
            ->pluck('provider_card_id')
            ->all();

        $missing = collect($cards)->reject(fn (array $card) => in_array((string) ($card['san'] ?? ''), $existingSans, true));

        if ($missing->isEmpty()) {
            $this->info('В CardsPro нет карт, которых ещё нет в админке — синхронизировать нечего.');

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = [];
        $bar = $this->output->createProgressBar($missing->count());
        $bar->start();

        foreach ($missing as $card) {
            $san = (string) $card['san'];

            try {
                $details = $service->getCardDetails($san);
            } catch (CardsProException $e) {
                $skipped[] = $san . ': не удалось получить детали (' . $e->getMessage() . ')';
                $bar->advance();

                continue;
            }

            $productCode = $details['productCode'] ?? $details['product'] ?? null;
            $product = $productCode
                ? CardProduct::where('provider_id', $provider->id)->where('provider_product_code', $productCode)->first()
                : null;

            if (! $product) {
                $skipped[] = $san . ': не найден «Карточный продукт» с кодом «' . $productCode . '» у провайдера «' . $provider->name . '»';
                $bar->advance();

                continue;
            }

            Card::create([
                'user_id' => $owner->id,
                'card_product_id' => $product->id,
                'provider_id' => $provider->id,
                'provider_card_id' => $san,
                'card_number' => $details['cardNumber'] ?? $card['pan'] ?? null,
                'expiry' => CardsProService::formatExpiry($details),
                'currency' => $details['currency'] ?? $card['currency'] ?? $product->currency,
                'balance' => $details['balance'] ?? 0,
                'price_rub' => $product->price_rub,
                'issue_cost_usd' => $product->provider_issue_cost_usd,
                'status' => CardsProService::mapCardStatus((string) ($details['status'] ?? '')),
                'issued_at' => $details['created'] ?? $card['created'] ?? now(),
            ]);

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Создано карт: ' . $created . ' (владелец — ' . $owner->email . ', ID ' . $owner->id . ').');

        if ($skipped !== []) {
            $this->warn('Пропущено: ' . count($skipped));

            foreach ($skipped as $line) {
                $this->line('  - ' . $line);
            }

            $this->line('Для пропущенных карт сначала заведите «Карточный продукт» с нужным кодом провайдера, затем повторите --sync.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<string, mixed>>  $cards
     */
    protected function renderTable(array $cards, CardProvider $provider): void
    {
        if ($cards === []) {
            $this->info('У провайдера «' . $provider->name . '» пока нет ни одной карты.');

            return;
        }

        $rows = collect($cards)->map(fn (array $card) => [
            $card['san'] ?? '—',
            $card['pan'] ?? $card['cardNumber'] ?? '—',
            $card['cardName'] ?? $card['cardname'] ?? '—',
            $card['currency'] ?? '—',
            $card['status'] ?? (($card['blocked'] ?? null) ? 'blocked' : ($this->option('details') ? '—' : 'запросите с --details')),
            array_key_exists('balance', $card) ? $card['balance'] : ($this->option('details') ? '—' : 'запросите с --details'),
            $card['product'] ?? $card['productCode'] ?? '—',
            isset($card['created']) ? substr((string) $card['created'], 0, 10) : '—',
        ]);

        $this->table(
            ['SAN', 'Номер (маска)', 'Название', 'Валюта', 'Статус', 'Баланс', 'Продукт', 'Создана'],
            $rows,
        );

        $this->newLine();
        $this->info(
            'Всего карт: ' . count($cards) . '. Заведите нужные вручную в «Карты» → «Карты»: '
            . 'SAN → поле «Идентификатор карты у провайдера» (provider_card_id).'
        );
    }
}
