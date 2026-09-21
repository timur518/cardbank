<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * php artisan currency:sync-rates
 *
 * Курсы RUB->USD/EUR/GBP для страницы «Настройки» → «Валютная система».
 * Источник — официальный курс
 * ЦБ РФ (cbr.ru), с резервным зеркалом cbr-xml-daily.ru на случай его недоступности.
 *
 * ЦБ РФ публикует новый курс сам не чаще раза в рабочий день (около 15:30–16:00 МСК,
 * действует со следующего дня; в выходные и праздники курс не меняется), поэтому
 * само значение между запусками команды в течение дня почти всегда одинаковое. Тем
 * не менее в routes/console.php команда запланирована чаще одного раза в сутки —
 * это не ради самого курса, а ради устойчивости: если в момент планового запуска
 * оба источника недоступны, следующий запуск в течение того же дня всё равно
 * подхватит курс. Штамп `currency_rates_updated_at` обновляется при каждом успешном
 * запуске (даже если цифра не изменилась) — так виджет «Обновлено N мин назад»
 * показывает, что сама синхронизация жива, а не только что курс не устарел.
 */
class SyncCurrencyRates extends Command
{
    protected $signature = 'currency:sync-rates';

    protected $description = 'Обновить курсы RUB->USD/EUR/GBP из ЦБ РФ и сохранить в настройки';

    /**
     * Код валюты => ключ настройки, в который сохраняется курс.
     */
    private const CODE_TO_SETTING = [
        'USD' => 'currency_rate_usd',
        'EUR' => 'currency_rate_eur',
        'GBP' => 'currency_rate_gbp',
    ];

    public function handle(): int
    {
        try {
            $rates = $this->fetchFromCbr();
        } catch (Throwable $e) {
            Log::warning("currency:sync-rates: ЦБ РФ недоступен, пробуем резервное зеркало ({$e->getMessage()})");

            try {
                $rates = $this->fetchFromMirror();
            } catch (Throwable $e2) {
                Log::error("currency:sync-rates: оба источника курсов недоступны ({$e2->getMessage()})");
                $this->error("Не удалось получить курсы валют ни у ЦБ РФ, ни у резервного зеркала: {$e2->getMessage()}");

                return self::FAILURE;
            }
        }

        $toSave = ['currency_rates_updated_at' => now()->toDateTimeString()];

        foreach (self::CODE_TO_SETTING as $code => $settingKey) {
            $toSave[$settingKey] = $rates[$code];
        }

        Setting::setMany($toSave);

        $this->info(sprintf(
            'Курсы валют обновлены: USD %.4f ₽, EUR %.4f ₽, GBP %.4f ₽',
            $rates['USD'],
            $rates['EUR'],
            $rates['GBP'],
        ));

        return self::SUCCESS;
    }

    /**
     * @return array<string, float>
     */
    private function fetchFromCbr(): array
    {
        $response = Http::timeout(10)->get('https://www.cbr.ru/scripts/XML_daily.asp');

        if (! $response->successful()) {
            throw new RuntimeException("ЦБ РФ вернул статус {$response->status()}");
        }

        $body = mb_convert_encoding($response->body(), 'UTF-8', 'windows-1251');
        $xml = simplexml_load_string($body);

        if ($xml === false) {
            throw new RuntimeException('Не удалось разобрать XML-ответ ЦБ РФ');
        }

        $rates = [];

        foreach ($xml->Valute as $valute) {
            $code = (string) $valute->CharCode;

            if (! isset(self::CODE_TO_SETTING[$code])) {
                continue;
            }

            $nominal = (float) str_replace(',', '.', (string) $valute->Nominal);
            $value = (float) str_replace(',', '.', (string) $valute->Value);

            $rates[$code] = $value / ($nominal ?: 1);
        }

        return $this->requireAllCodes($rates, 'ЦБ РФ');
    }

    /**
     * @return array<string, float>
     */
    private function fetchFromMirror(): array
    {
        $response = Http::timeout(10)->get('https://www.cbr-xml-daily.ru/daily_json.js');

        if (! $response->successful()) {
            throw new RuntimeException("Резервное зеркало вернуло статус {$response->status()}");
        }

        $valute = $response->json('Valute') ?? [];
        $rates = [];

        foreach (self::CODE_TO_SETTING as $code => $settingKey) {
            if (! isset($valute[$code]['Value'])) {
                continue;
            }

            $nominal = (float) ($valute[$code]['Nominal'] ?? 1);
            $rates[$code] = ((float) $valute[$code]['Value']) / ($nominal ?: 1);
        }

        return $this->requireAllCodes($rates, 'резервное зеркало');
    }

    /**
     * @param  array<string, float>  $rates
     * @return array<string, float>
     */
    private function requireAllCodes(array $rates, string $source): array
    {
        $missing = array_diff(array_keys(self::CODE_TO_SETTING), array_keys($rates));

        if ($missing !== []) {
            throw new RuntimeException("Источник «{$source}» не вернул курсы: " . implode(', ', $missing));
        }

        return $rates;
    }
}
