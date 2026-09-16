# Интеграция с CardsPro

Источник: [docs.cardspro.com/api/cards](https://docs.cardspro.com/api/cards) и
[docs.cardspro.com/api/operations-callbacks](https://docs.cardspro.com/api/operations-callbacks).

## Где лежит и почему

```
app/Services/Integrations/CardsPro/
├── CardsProService.php           — публичное API интеграции (см. таблицу ниже)
├── CardsProClient.php            — подпись запросов и HTTP-транспорт (внутренний, напрямую не вызывать)
├── CardsProWebhookHandler.php    — применяет к нашим моделям данные из вебхуков
└── Exceptions/CardsProException.php

app/Http/Controllers/Api/Webhooks/CardsProWebhookController.php   — принимает вебхуки CardsPro
app/Enums/CardsProCallbackType.php                                — типы вебхуков (X-CP-Callback-Type)
routes/api.php                                                    — маршрут вебхука
```

**Почему `app/Services/...`, а не `app/Http/Controllers/Integrations/CardsPro`:** контроллеры в Laravel —
это только точка входа для HTTP-запроса (получить запрос → вызвать логику → вернуть ответ). Сама логика
похода во внешний API (сборка подписи, запросы issue/topup/details и т.д.) не привязана к HTTP и должна
быть вызываемой откуда угодно: из Filament-действий в админке, из будущих API-контроллеров нашего
собственного клиентского API, из консольных команд, из очередей. Поэтому сама интеграция — это сервис
(`app/Services/Integrations/CardsPro`), а контроллер (`app/Http/Controllers/Api/Webhooks`) — это только
тонкий приёмник входящих вебхуков, который сохраняет сырое событие и передаёт его в
`CardsProWebhookHandler` (тоже сервис, вызываемый и переиспользуемый так же, как и остальная интеграция).

## Настройка провайдера

Все учётные данные хранятся не в `.env`, а в конкретной записи провайдера — раздел
**«Карты» → «Провайдеры карт»**, блок «Техническое подключение» (провайдеров может быть несколько —
например sandbox и production, у каждого свои ключи):

| Поле провайдера  | Соответствует                                                    |
|------------------|-------------------------------------------------------------------|
| `api_base_url`   | Хост API, например `https://api.cardspro.com` (без `/cards/v1` — префикс добавляет сервис) |
| `api_key`        | `CAP-TOKEN`                                                        |
| `api_secret`     | Secret Key для подписи `CAP-SIGN` (никогда не уходит в запрос как есть) |
| `webhook_secret` | Наш собственный токен для проверки входящих вебхуков (см. ниже, в API CardsPro не описан) |

Если `api_base_url` / `api_key` / `api_secret` не заполнены — любой вызов бросает `CardsProException`
ещё до похода в сеть, с понятным сообщением, какого поля не хватает.

Тайм-аут HTTP-запросов настраивается через `.env`: `CARDSPRO_TIMEOUT` (по умолчанию 20 секунд),
см. `config/services.php` → `cardspro.timeout`.

## Как вызывать из кода

```php
use App\Services\Integrations\CardsPro\CardsProService;

$provider = \App\Models\CardProvider::where('code', 'cardspro')->firstOrFail();
$cardsPro = CardsProService::for($provider);

$result = $cardsPro->issueCard([
    'productCode' => 'B0067bd6bf2b2604be25e9821b0',
    'amount' => 25,
    'currency' => 'USD',
]);
```

Работает одинаково в Filament-действиях, в контроллерах, в вебхуках, в консольных командах — просто
получите нужный `CardProvider` и создайте сервис через `CardsProService::for($provider)`.

Любая ошибка (неверные учётные данные, сетевая ошибка, ответ 4xx/5xx от CardsPro) выбрасывает
`App\Services\Integrations\CardsPro\Exceptions\CardsProException`, у которой есть:
- `$e->getMessage()` — человекочитаемое сообщение;
- `$e->statusCode()` — HTTP-код ответа CardsPro (0, если запрос вообще не ушёл — например, не заполнены ключи);
- `$e->responseBody()` — тело ответа CardsPro как массив (для диагностики).

## Функции интеграции (`CardsProService`)

### Мастер-счёт и продукты

| Метод | Что передавать | Что возвращает |
|---|---|---|
| `getAccountBalance(?string $currency = null, ?string $account = null)` | Ничего, либо `currency` («USD», «EUR»), либо `account` (конкретный счёт). Если передать оба — CardsPro учитывает `account`. | Массив `[['total' => float, 'currency' => string], ...]` |
| `getCardProducts()` | — | Массив продуктов: `productCode`, `currency`, `issueMinAmount`, `issueMaxAmount`, `topUpMinAmount`, `topUpMaxAmount`, `kycSort?`, `params?` |
| `getCardProductRatesByMerchant(string $merchant)` | `merchant` — название мерчанта (`github`, `google` и т.п.) | Массив `[['productCode' => string, 'rate' => float], ...]` |

### Жизненный цикл карты

| Метод | Что передавать | Что возвращает |
|---|---|---|
| `issueCard(array $data)` | `productCode` (обязательно), `amount` (обязательно), `currency` (обязательно, USD/EUR), `request_id` (необязательно — сгенерируется автоматически, 12-36 символов), `cardValidityYears` (1-4), `preferredAccount`, `cardName` (до 32 симв.), `email`, `firstName`, `lastName`, `kycUuid` | `['type' => 'ISSUE', 'status' => 'INPROCESS'\|'EXECUTED'\|'DECLINED', 'docid' => int, 'request_id' => string]` |
| `getCardDetails(string $san)` | `$san` — Secure Account Number карты | `created`, `blocked`, `san`, `productCode`, `cardSystem`, `expYear`, `expMonth`, `cvv`, `currency`, `cardNumber`, `initialBalance`, `balance`, `status`, `cardName`, `docid` |
| `getCardList(int $limit = 20, int $offset = 0)` | Пагинация, `limit` ≤ 100 | `['count' => int, 'cards' => [...]]` |
| `topUpCard(string $san, float $amount, string $currency, ?string $requestId = null)` | `amount` > 1, `currency` должна совпадать с валютой карты | `['type' => 'TOPUP', 'status' => ..., 'docid' => int, 'request_id' => string, 'topup' => [...]]` |
| `withdrawFromCard(string $san, float $amount, string $currency, ?string $requestId = null)` | `amount` > 0.01 | `['type' => 'WITHDRAWAL', 'status' => ..., 'docid' => int, 'request_id' => string, 'withdrawal' => [...]]` |
| `blockCard(string $san, ?string $requestId = null)` | Только `san` (необратимая блокировка!) | `['type' => 'BLOCK', 'status' => ..., 'docid' => int, 'request_id' => string]` |
| `getBlockedCardDetails(string $san)` | `$san` | `['san' => string, 'releasedBalance' => float, 'currency' => string]` |
| `freezeCard(string $san)` | `$san` (временная заморозка) | `['success' => bool, 'message' => string]` |
| `unfreezeCard(string $san)` | `$san` | `['success' => bool, 'message' => string]` |
| `updateCardEmail(string $san, string $email)` | `$san`, `email` | `['success' => bool, 'message' => string]` |
| `updateCardPhone(string $san, string $zoneNumber, string $phoneNumber)` | `zoneNumber` — код страны с `+` (`"+48"`), `phoneNumber` — номер без кода | `['success' => bool, 'message' => string]` |
| `setCardPin(string $san, string $pin)` | `pin` — ровно 4 цифры, первичная установка | `['success' => bool, 'message' => string]` |
| `updateCardPin(string $san, string $pin, ?string $oldPin = null)` | `pin` — новый PIN (4 цифры), `oldPin` — обязателен для части продуктов | `['success' => bool, 'message' => string]` |
| `getCardTransactions(string $san, int $page = 0, int $size = 20, ?string $from = null, ?string $to = null)` | `page` ≥ 0, `size` 1-100, `from`/`to` — ISO-8601 UTC **с миллисекундами** (`2026-01-01T00:00:00.000Z`; без них — 400 `Invalid date format`) | `['total' => int, 'list' => [...]]`, поля элементов списка — `transactionId`/`status`/`transactionValue`/`transactionCommission`/`cardCurrency`/`transactionRecipient`/`date` (см. `CardsProService::normalizePolledTransaction()`, не путать с полями вебхука `CARD_TRANSACTION`) |
| `getCardOtpCodes(string $san)` | `$san` | Массив до 10 `['code' => string, 'date' => string]` |

### Статусы асинхронных операций

CardsPro обрабатывает `issue`/`topup`/`withdraw`/`block` асинхронно: сразу приходит `INPROCESS`, а
итоговый статус (`EXECUTED`/`DECLINED`) узнаётся либо через вебхук, либо опросом:

| Метод | Что передавать | Что возвращает |
|---|---|---|
| `getRequestStatus(?string $requestId = null, ?int $docId = null)` | Один из двух: `requestId` или `docId` | Детали операции нужного типа (issue/topup/withdrawal/block), включая `status` |
| `getCardRequestsList(string $san, int $limit = 20, int $offset = 0)` | `$san`, пагинация | `['count' => int, 'requests' => [...]]` |
| `getUserRequestsList(int $limit = 20, int $offset = 0)` | Пагинация | `['count' => int, 'requests' => [...]]` |

### Вспомогательное

- `CardsProService::generateRequestId()` — генерирует уникальный `request_id` (UUID), если свой не нужен.

## Получить список уже выпущенных карт через терминал

Команда `php artisan cardspro:cards` тянет список карт напрямую из CardsPro и печатает таблицей в терминале
(в базу ничего не пишет — это чисто просмотровая команда, чтобы свериться и вручную завести
недостающие карты в админке, т.к. выбор владельца/продукта для каждой карты CardsPro не отдаёт):

```bash
# Быстро: только SAN, номер (маска), валюта, дата создания — один запрос GET /list
php artisan cardspro:cards

# Если провайдеров несколько — укажите код явно
 php artisan cardspro:cards cardspro

# + баланс, статус и продукт по каждой карте (доп. запрос GET /{san}/details на каждую карту)
php artisan cardspro:cards --details

# Сырой JSON вместо таблицы (удобно сохранить в файл)
php artisan cardspro:cards --details --json > cards.json
```

Колонка `SAN` из вывода — это значение для поля «Идентификатор карты у провайдера» (`provider_card_id`)
при создании карты вручную в разделе «Карты» → «Карты» (пользователя/продукт нужно выбрать вручную —
от CardsPro эти данные не приходят, см. «Известные ограничения» про CARD_ISSUE выше).

### Автоматическое заведение недостающих карт (`--sync`)

```bash
# Сравнивает список CardsPro с «Карты» → «Карты» и создаёт недостающие на пользователе с ID 1
php artisan cardspro:cards --sync

# С другим владельцем по умолчанию
php artisan cardspro:cards --sync --user=42
```

Сопоставление идёт по `provider_id` + `provider_card_id` (SAN). Для каждой недостающей карты
запрашиваются `GET /{san}/details` и создаётся запись с балансом/статусом/валютой/сроком из
ответа CardsPro. Два важных ограничения:

- **Владелец всегда берётся из `--user`** (по умолчанию 1), потому что CardsPro нигде не возвращает,
  какой из ваших пользователей владеет картой. После синхронизации владельца стоит
  скорректировать вручную.
- **Карта пропускается, если не найден подходящий «Карточный продукт»**: `card_product_id`
  в базе обязателен, а CardsPro возвращает только свой `productCode` — его нужно заранее указать
  в поле «Код продукта у провайдера» (`provider_product_code`) нужного «Карточного продукта». Такие карты
  команда выводит списком с причиной пропуска — после добавления продукта просто повторите `--sync`.

## Вебхуки (входящие колбэки CardsPro)

**URL для настройки в личном кабинете CardsPro** (свой на каждого провайдера, код провайдера — поле
«Код провайдера» в разделе «Карты» → «Провайдеры карт»):

```
POST {APP_URL}/api/webhooks/cardspro/{код_провайдера}?token={webhook_secret этого провайдера}
```

Если поле `webhook_secret` у провайдера пустое — проверка токена отключена (в документации CardsPro
подпись входящих колбэков не описана вообще, поэтому `?token=` — это **наша собственная**
дополнительная защита, а не требование CardsPro).

Любой входящий вебхук (независимо от типа и от результата обработки) сохраняется целиком в таблицу
`provider_messages` (`ProviderMessage`) с типом события (`X-CP-Callback-Type`) и статусом
`pending` → `processed`/`failed`. Так ни одно событие не теряется, даже если обработчик ниже его
не понимает.

Что происходит с каждым типом события дальше:

| `X-CP-Callback-Type` | Автоматическое действие |
|---|---|
| `CARD_TOPUP` (status=EXECUTED) | Находит карту по `san` (поле `provider_card_id`) и увеличивает `balance` на `params.amount` |
| `CARD_WITHDRAWAL` (status=EXECUTED) | Находит карту, уменьшает `balance` на `params.amount` |
| `CARD_BLOCK` (status=EXECUTED) | Находит карту, ставит статус «Закрыта», `closed_at = now()`, пишет запись в историю статусов |
| `CARD_FREEZE` | Находит карту, ставит статус «Заморожена», пишет историю статусов |
| `CARD_UNFREEZE` | Находит карту, ставит статус «Активна», пишет историю статусов |
| `CARD_TRANSACTION` | Создаёт/обновляет запись в «Транзакции по картам» (сумма = `billAmount`, комиссия = `fee`, тип/статус — по `txType`, см. таблицу ниже) и меняет баланс карты. Идемпотентно: повторная доставка того же `txId` не создаёт вторую запись и не списывает баланс дважды |
| `CARD_ISSUE`, `EXTRA_FEE_CARD`, `EXTRA_FEE_CAP`, `OTP_CODE`, `KYC_CHANGE` | **Только логируются** в `ProviderMessage` — см. «Известные ограничения» ниже |

Соответствие `txType` → тип/статус транзакции в нашей базе:

| `txType` CardsPro | `type` | `status` | Баланс |
|---|---|---|---|
| `expense` | Покупка | Успешно | −`billAmount` |
| `authorization` | Покупка | В обработке | не меняется (это только холд) |
| `authorization_decline` | Отклонённый платёж | Отклонена | не меняется |
| `verification` | Покупка | В обработке | не меняется |
| `verification_decline` | Отклонённый платёж | Отклонена | не меняется |
| `verification_expense` | Покупка | Успешно | −`billAmount` |
| `refund` | Возврат | Успешно | +`billAmount` |
| `reversal` | Возврат | Возвращена | +`billAmount` |
| `maintenance_fee` | Комиссия | Успешно | −`billAmount` |

### Известные ограничения

- **`CARD_ISSUE` создаёт карту, только если выпуск был инициирован через `card_provider_operations`.**
  В теле этого колбэка нет ни `user_id`, ни кода продукта — только `san`, `docid`, `request_id`,
  `amount`, `currency`. Поэтому та часть админки, которая вызывает `issueCard()`, должна до вызова
  завести `CardProviderOperation` (`type=issue`, `request_id`, `payload=['user_id' => ...,
  'card_product_id' => ...]`) — именно там хранится будущий владелец и карточный продукт,
  которых нет в самом колбэке. Самого такого админ-действия «Выпустить карту» в панели пока
  нет — это следующий шаг. Без такой записи событие по-прежнему только логируется.
  См. раздел «Фоновая синхронизация» ниже.
- **`CARD_TOPUP` / `CARD_WITHDRAWAL` / `CARD_BLOCK`** пока намеренно не связаны с
  `card_provider_operations` — сегодня их никто туда не пишет (нет админки-действия
  «Пополнить/вывести/заблокировать через API»). Когда оно появится, эти ветки
  вебхук-обработчика тоже нужно будет перевести на `CardProviderOperationResolver`, иначе
  возможен двойной учёт баланса с `providers:sync-pending-operations`.
- **`EXTRA_FEE_CARD` / `EXTRA_FEE_CAP`** (дополнительные комиссии/расхождения) и **`KYC_CHANGE`**
  (смена статуса KYC) тоже только логируются — в текущей схеме нет однозначного способа связать их с
  существующими записями (для KYC потребовался бы внешний `kycExternalUserId` на пользователе, для
  дополнительных комиссий — решение, куда именно их относить: в расходы, в `ProviderDiscrepancy` и т.д.).
  Это осознанные точки расширения, а не забытые куски.
- **`OTP_CODE`** — коды одноразовые и короткоживущие, отдельного хранилища под них в схеме нет,
  событие просто остаётся в `ProviderMessage` (там же есть `payload.otpCode`, если понадобится).

## Как устроена подпись запроса (если понадобится отладка)

```
GET:  CAP-SIGN = sha256(CAP-NONCE + query_string + api_secret)
POST: CAP-SIGN = sha256(CAP-NONCE + json_тело_запроса + query_string + api_secret)
```

`CAP-NONCE` — текущее время в миллисекундах. `query_string` — без вопросительного знака
(`limit=10&offset=0`), пустая строка, если параметров нет. Тело JSON для подписи и для самой отправки
формируется один раз и переиспользуется — так подписывается ровно тот текст, который уходит на сервер
(см. `CardsProClient::post()`).

## Фоновая синхронизация

Данные о картах/провайдерах живут не только по вебхукам — вебхук может потеряться,
не дойти или прийти с задержкой. Поэтому есть пять универсальных (не привязанных к
конкретному провайдеру — работают со всеми `CardProvider::where('status', 'active')`)
фоновых команд-подстраховок. Зарегистрированы в `routes/console.php` через
`Schedule::command(...)`; на сервере нужен один крон-entry:

```
* * * * * cd /path/to/cardbank && php artisan schedule:run >> /dev/null 2>&1
```

| Команда | Что делает | Частота |
|---|---|---|
| `providers:sync-card-balances` | Баланс и статус активных/замороженных карт (`GET /{san}/details`), с историей в `CardStatusHistory` при смене статуса | 15 мин |
| `providers:sync-card-transactions` | Докачивает операции по картам (`/{san}/transactions`) начиная с `history_checked_at`, создаёт `CardTransaction` идемпотентно по `provider_tx_id` | 15 мин |
| `providers:sync-pending-operations` | Опрашивает `GET /request/status` по всем незавершённым `CardProviderOperation` и применяет результат | 2 мин |
| `providers:sync-account-balances` | Перезаписывает `CardProvider::reserve_balance_usd` реальным балансом мастер-счёта (`GET /account/balance`) | 1 час |
| `providers:sync-card-catalog` | Сверяет `GET /products` с «Карточными продуктами», заводит расхождения на новые/пропавшие коды продуктов | 1 раз в сутки |

### Архитектура: как это остаётся универсальным

- `App\Services\Integrations\Contracts\CardProviderIntegration` — интерфейс с уже
  нормализованными методами (`fetchCardSnapshot`, `fetchCardTransactions`,
  `fetchMasterBalanceUsd`, `fetchProductCatalog`, `fetchOperationStatus`). Вся
  провайдер-специфичная разборка сырого JSON и словарь статусов остаются внутри
  `CardsProService` (единственной реализации на сегодня).
- `App\Services\Integrations\ProviderIntegrationResolver::for($provider)` — единственное
  место, которое решает, какая интеграция обслуживает провайдера. Подключение второго
  реального провайдера — это одна новая ветка `match` здесь, команды менять не нужно.
- `card_provider_operations` — таблица асинхронных операций (`issue`/`topup`/`withdraw`/
  `block`), которые мы инициировали и ждём финального статуса. Для `issue` в `payload`
  хранится `user_id`/`card_product_id` — то, чего провайдер о нас не знает, но без чего
  нельзя завести `Card` по одному ответу CardsPro. Закрывается либо вебхуком (`CARD_ISSUE`
  — см. «Известные ограничения»), либо `providers:sync-pending-operations`.
- `App\Services\CardProviderOperationResolver` — применяет результат операции к моделям
  (создаёт карту / двигает баланс / закрывает карту) одинаково для обоих источников
  результата. `resolve()` атомарно «забирает» операцию (`UPDATE ... WHERE status = pending`),
  так что если и вебхук, и `providers:sync-pending-operations` узнают результат почти
  одновременно — эффект применится только один раз.

### Провайдерские расхождения — не то же самое, что комплаенс-алерты

Проблемы уровня «у нас с провайдером что-то не сходится» (просевший баланс мастер-счёта,
новый/пропавший продукт в каталоге, зависшая без ответа операция) пишутся в
`ProviderDiscrepancy` (поле `type` — `App\Enums\DiscrepancyType`, плюс текстовое `note` с
подробностями), а не в `ComplianceAlert`. `ComplianceAlert` заточен под нарушения
конкретного пользователя/карты (обязательные `rule_id`, действия «заморозить
карту»/«заблокировать пользователя») — для провайдерских проблем это не подходит.
Каждая из трёх «поисковых» команд (`sync-pending-operations`, `sync-account-balances`,
`sync-card-catalog`) избегает дублей: перед созданием новой `ProviderDiscrepancy`
проверяет, нет ли уже открытой такой же по тексту `note`.

`CardProvider::reserve_balance_usd` теперь считается реальным балансом (обновляется
`providers:sync-account-balances`), поэтому в форме провайдера это поле недоступно для
ручного редактирования у уже существующих провайдеров (только при создании — как
стартовое значение).
