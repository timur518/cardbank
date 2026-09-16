<?php

namespace App\Console\Commands;

use App\Models\CardProvider;
use App\Services\Integrations\CardsPro\CardsProService;
use App\Services\Integrations\CardsPro\Exceptions\CardsProException;
use Illuminate\Console\Command;

/**
 * php artisan cardspro:cards [код_провайдера] [--details] [--json]
 *
 * Тянет список выпущенных карт напрямую из CardsPro (GET /list, при --details ещё
 * и GET /{san}/details на каждую карту) и печатает в терминал — чтобы можно было
 * свериться со списком и вручную завести недостающие карты в «Карты» → «Карты».
 * Ничего не пишет в базу — это чисто просмотровая команда.
 */
class CardsProListCards extends Command
{
    protected $signature = 'cardspro:cards
        {provider? : Код провайдера из «Карты» → «Провайдеры карт» (если провайдер один — можно не указывать)}
        {--details : Дополнительно запросить баланс, статус и продукт по каждой карте (по одному запросу на карту, может быть медленно)}
        {--json : Вывести сырой JSON вместо таблицы}';

    protected $description = 'Получить из CardsPro список выпущенных карт для сверки/переноса в админку';

    public function handle(): int
    {
        $provider = $this->resolveProvider();

        if (! $provider) {
            return self::FAILURE;
        }

        $service = CardsProService::for($provider);

        try {
            $cards = $this->fetchAllCards($service);

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
