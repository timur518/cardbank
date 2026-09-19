# Оформление заказа и выпуск карты — сквозной алгоритм

Документ описывает полный путь от выбора карты клиентом до активной карты с реальным
балансом, с точным маппингом на существующие модели/поля/сервисы
(`app/Services/Integrations/CardsPro`, `CardProviderOperation`,
`CardProviderOperationResolver`, `Income`, `Expense`, `ProviderReserveTopup`). Служит
спецификацией для контроллера оформления заказа и вебхука платёжной системы — они
пока не реализованы, всё остальное описанное ниже (модели, статусы, `applyIssue()`,
`recordIssueExpenses()`, `recordDeclinedIssue()`) уже есть в коде.

По сути запись в ресурсе **«Карты»** и есть «Заказ» в терминах интернет-магазина —
именно она объединяет продукт, покупателя, деньги и статус выполнения.

---

## Действующие модели (для справки)

| Модель | Ключевые поля, которые участвуют в этом алгоритме |
|---|---|
| `CardProduct` | `price_rub` (цена продажи клиенту, ₽), `provider_issue_cost_usd` (себестоимость выпуска, $), `provider_topup_fee_percent` (комиссия провайдера за пополнение карт этого продукта, %), `currency`, `topup_min_amount`/`topup_max_amount` (в валюте продукта), `provider_id`, `provider_product_code`, `billing_*` |
| `Card` | `user_id`, `card_product_id`, `provider_id`, `provider_card_id` (SAN, заполняется только на шаге 5), `card_number`, `expiry`, `currency`, `balance`, `price_rub`, `issue_cost_usd`, `status` (enum `CardStatus`: `Waiting`/`Pending`/`Active`/`Frozen`/`Closed`/`Cancelled`/`Failed`), `billing_*`, `issued_at` |
| `Income` | `type` (enum `IncomeType`), `amount`, `currency`, `amount_usd`, `card_id`, `user_id`, `payment_method_id`, `payment_transaction_id`, `payment_status` (enum `IncomePaymentStatus`), `comment` |
| `Expense` | `date`, `category` (enum `ExpenseCategory`: `CardIssue` = «Выпуск карты», `CardTopup` = «Пополнение карты»), `amount`, `amount_usd`, `card_id`, `provider_id`, `comment` |
| `CardProviderOperation` | `provider_id`, `card_id`, `type` (enum `CardProviderOperationType`: issue/topup/withdraw/block), `request_id`, `docid`, `status`, `payload` (json), `result`, `error`, `resolved_at` |
| `ProviderReserveTopup` | `provider_id`, `amount` ($, пополнение мастер-счёта у провайдера), `comment`, `created_by` — казначейская метрика «Резерв у провайдеров» в `ProfitStatsWidget`. Не участвует в P&L по картам — себестоимость конкретной карты ведётся отдельно через `Expense` (см. ниже) |
| `Setting` | `currency_rate_usd`, `currency_markup_usd_percent` — курс ЦБ и наша наценка (страница «Валютная система») |

Уже реализовано и переиспользуется без изменений: `CardsProService::issueCard()`,
`CardsProWebhookHandler::handleIssue()`, команда `providers:sync-pending-operations`
(страховка на случай потерянного вебхука).

---

## Шаг 1. Клиент собирает заказ (фронт/ЛК, без записи в БД)

1. Выбирает карту → `CardProduct` → показываем `price_rub` («Цена выпуска, ₽»).
2. Выбирает сумму пополнения — переключатель ₽/$:
   - Берём `Setting::getMany(['currency_rate_usd', 'currency_markup_usd_percent'])`.
   - Наш курс продажи: `rate_sell = currency_rate_usd * (1 + currency_markup_usd_percent / 100)`.
   - Если клиент ввёл сумму в $ (`topup_usd`) → показываем `topup_rub = round(topup_usd * rate_sell, 2)`.
   - Если ввёл в ₽ (`topup_rub`) → показываем `topup_usd = round(topup_rub / rate_sell, 2)`.
   - Валидация границ — `CardProduct.topup_min_amount`/`topup_max_amount` (они в валюте
     продукта, обычно USD — сверяем именно `topup_usd`).
   - Наценка на курс уже включает в себя комиссию CardsPro за пополнение карты (см.
     «Курсы» ниже) — отдельно её нигде учитывать не нужно.
3. Итог к оплате: `total_rub = price_rub + topup_rub`. `topup_usd` с этого момента —
   зафиксированное число: именно оно позже уйдёт в CardsPro как сумма выпуска (шаг 4),
   пересчитывать его по курсу заново нельзя.

Ничего из этого пока не пишется в БД — это калькулятор на фронте/в API-методе расчёта цены.

---

## Шаг 2. Оформление заказа (backend, один API-запрос)

### 0. Создать карту

Заводим `Card` сразу, ещё до какой-либо оплаты — это и есть «создание заказа», а не
побочный эффект успешного выпуска:

```
user_id            = $userId
card_product_id    = $cardProductId
provider_id        = CardProduct.provider_id
provider_card_id   = null                          // появится только на шаге 5 (SAN от CardsPro)
currency           = CardProduct.currency           // USD
status             = CardStatus::Waiting            // «Ожидает оплаты»
price_rub          = CardProduct.price_rub          // снепшот, прямое копирование
issue_cost_usd     = CardProduct.provider_issue_cost_usd  // снепшот, прямое копирование, БЕЗ конвертации
billing_country    = CardProduct.billing_country    // снепшот
billing_city       = CardProduct.billing_city
billing_region     = CardProduct.billing_region
billing_address    = CardProduct.billing_address
billing_post_code  = CardProduct.billing_post_code
```

`price_rub` и `issue_cost_usd` — два независимых готовых поля (₽ и $ соответственно),
никакой конвертации между ними не требуется: `price_rub` — это то, что платит клиент,
`issue_cost_usd` — наша себестоимость выпуска у провайдера. Оба просто копируются с
`CardProduct` как есть.

### 1. Создать поступление

```
type                    = IncomeType::CardIssue
amount                  = total_rub                              // price_rub + topup_rub
currency                = 'RUB'
amount_usd              = round(total_rub / currency_rate_usd, 2) // курс ЦБ, БЕЗ наценки — см. «Курсы»
card_id                 = $card->id                                // карта уже существует (шаг 0)
user_id                 = $userId
payment_method_id       = $paymentMethodId
payment_transaction_id  = null                                    // придёт на шаге 2 ниже
payment_status          = IncomePaymentStatus::Pending
comment                 = 'Выпуск карты и пополнение баланса'
```

Выпуск и пополнение — одно «поступление», это упрощает связку с одним платежом. Если
позже понадобится точный разрез «доход от выпуска» / «доход от пополнений» в графике
`IncomeExpenseProfitChartWidget` (сейчас он умеет строить эти две линии только по
`IncomeType::CardIssue`/`::CardTopup` раздельно) — этот `Income` придётся либо делить
на два, либо разбирать по `comment`.

### 2. Перенаправить на платёжную систему

Создаём платёж в платёжной системе на `total_rub`. Получаем `payment_transaction_id`,
записываем в `Income`, отдаём клиенту ссылку на оплату (редирект).

На этом API-метод оформления заказа заканчивается. Дальше — вебхуки.

**Идемпотентность:** метод должен принимать idempotency-key от клиента (или
генерировать его сам и отдавать в ответе), чтобы повторный вызов с тем же ключом не
создавал вторую `Card`/`Income` при повторной отправке формы или ретрае сети.

---

## Шаг 3. Вебхук платёжной системы подтверждает оплату

*(Провайдер платежей в коде пока не подключён — этот вебхук ещё предстоит
реализовать. Ниже — контракт: по каким полям искать `Income` и что делать дальше.)*

1. Находим `Income` по `payment_transaction_id`.
2. Оплата прошла → `payment_status = Paid`. Передаём заказ (по сути — `$card->id`,
   `topup_usd`) в интеграционный модуль (шаг 4).
3. Оплата не прошла → `payment_status = Failed`. `Card` переходит в статус
   `CardStatus::Cancelled`, без движения дальше.

---

## Шаг 4. Инициируем выпуск у CardsPro

1. `CardsProService::for($provider)->issueCard(['productCode' => CardProduct.provider_product_code, 'amount' => $topup_usd, 'currency' => CardProduct.currency])`.
   Это один вызов сразу на выпуск и на начальное пополнение — у CardsPro `amount` в
   `issueCard()` это и есть желаемый стартовый баланс, отдельный `topUpCard()` сразу
   после выпуска не нужен.
2. `INPROCESS`/`EXECUTED` в ответе → создаём `CardProviderOperation`:
   ```
   provider_id = CardProduct.provider_id
   card_id     = $card->id            // уже известен — карта создана на шаге 0
   type        = CardProviderOperationType::Issue
   request_id  = <из ответа issueCard()>
   docid       = <из ответа issueCard()>
   status      = CardProviderOperationStatus::Pending
   payload     = ['topup_usd' => $topup_usd]   // нужен на шаге 5 для расчёта комиссии за пополнение
   ```
   Дальше ничего делать не нужно — итог узнаём либо по вебхуку `CARD_ISSUE`, либо
   подстрахуется `providers:sync-pending-operations`, если вебхук потеряется.
3. `DECLINED` сразу в ответе — сценарий «оплата прошла, а выпуск с ходу отклонён».
   Вызываем `CardProviderOperationResolver::recordDeclinedIssue($card, $provider, $requestId,
   $docid, $raw)` — метод создаёт `CardProviderOperation`:
   ```
   provider_id = CardProduct.provider_id
   card_id     = $card->id
   type        = CardProviderOperationType::Issue
   request_id  = <из ответа issueCard()>
   docid       = <из ответа issueCard()>
   status      = CardProviderOperationStatus::Failed
   result      = $raw                                  // сырой ответ issueCard() целиком
   error       = $raw['declineReason'] ?? $raw['message'] ?? 'Провайдер отклонил выпуск карты'
   resolved_at = now()
   ```
   и переводит `Card` в статус `CardStatus::Failed` (с записью в `CardStatusHistory`).
   Дальше ничего делать не нужно.

`CardProviderOperation.card_id` заполняется сразу при создании операции (карта уже
существует с шага 0), а `payload` для `issue` хранит только `topup_usd` — сумму
начального пополнения, нужную на шаге 5 для расчёта комиссии провайдера.

---

## Шаг 5. CardsPro подтверждает выпуск — карта активируется

Обрабатывается в `CardProviderOperationResolver::applyIssue()`: метод находит уже
существующую карту по `operation->card_id` и обновляет её (карта была создана на
шаге 0, до оплаты):

```php
protected function applyIssue(CardProviderOperation $operation, array $raw): void
{
    $san = (string) ($raw['san'] ?? '');

    if ($san === '' || ! $operation->card_id) {
        return;
    }

    $card = Card::find($operation->card_id);
    $snapshot = ProviderIntegrationResolver::for($operation->provider)->fetchCardSnapshot($san);

    $card->update([
        'provider_card_id' => $san,
        'card_number'      => $snapshot['card_number'],
        'expiry'           => $snapshot['expiry'],
        'currency'         => $snapshot['currency'] ?: $card->currency, // не затирать снепшот с продукта, если провайдер не прислал
        'balance'          => $snapshot['balance'],
        'status'           => $snapshot['status'],           // обычно сразу Active
        'issued_at'        => now(),
    ]);

    $this->recordIssueExpenses($card, $operation);
}
```

Задваивания баланса при этом не будет, даже если CardsPro параллельно пришлёт ещё и
`CARD_TOPUP`-вебхук на ту же стартовую сумму: `applyIssue()` выше выставляет `balance`
абсолютным значением из `fetchCardSnapshot()` (реальный баланс у провайдера на этот
момент), а не прибавляет к чему-либо. Периодический `providers:sync-card-balances`
тоже всегда пишет баланс абсолютным значением из снапшота — так что даже временный
дрейф от инкрементов в вебхуках самокорректируется.

После этого шага у нас: `Card.status = Active`, `Card.balance` — реальный баланс,
`Card.provider_card_id` заполнен, `Income.payment_status = Paid` (был выставлен на
шаге 3), и два `Expense` (себестоимость выпуска + сумма пополнения с комиссией за
пополнение) заведены — заказ полностью выполнен и полностью учтён в P&L.

---

## Курсы: где какой брать

| Что считаем | Какой курс | Почему |
|---|---|---|
| Сколько ₽ берём с клиента за пополнение (`topup_rub`, шаг 1) | `rate_sell = currency_rate_usd * (1 + currency_markup_usd_percent/100)` | Цена продажи с наценкой. Наценка — источник выручки, из которой мы покрываем комиссию CardsPro за пополнение и зарабатываем маржу — но сама комиссия всё равно отдельно ведётся через `Expense` (см. ниже), а не просто неявно «съедается» внутри наценки — иначе реальная себестоимость пополнения нигде не видна |
| `Income.amount_usd` | голый `currency_rate_usd` (без наценки) | «Справедливая» долларовая оценка того, что клиент заплатил в рублях — нужна для сопоставления с расходами в долларах. Если взять курс с наценкой, наценка (по сути, вся наша прибыль на конвертации) исчезнет из отчётов |
| `amount`, отправляемый в `issueCard()` | не курс, а сама сумма `topup_usd`, зафиксированная на шаге 1 | Операционный параметр («сколько долларов положить на карту»), а не бухгалтерская оценка |

---

## Расходы: авто-Expense на выпуск и на комиссию за пополнение

Себестоимость выпуска и комиссию провайдера за пополнение ведём как отдельные `Expense`
на каждую карту — это даёт точную юнит-экономику по каждому выпуску и по каждому
продукту, а не только общую картину по движению денег.

(`ProviderReserveTopup`/`reserve_balance_usd` — отдельная казначейская метрика
«сколько денег лежит у провайдера» для контроля ликвидности, она не участвует в P&L
по картам.)

Комиссия провайдера за пополнение хранится на `CardProduct.provider_topup_fee_percent`
(%, задаётся отдельно на каждый карточный продукт, т.к. может отличаться от продукта
к продукту).

В `CardProviderOperationResolver::applyIssue()` (шаг 5), сразу после активации карты,
создаются две записи `Expense`:

```php
protected function recordIssueExpenses(Card $card, CardProviderOperation $operation): void
{
    $product = $card->cardProduct;
    $rate = (float) Setting::get('currency_rate_usd', 0);

    // 1. Себестоимость выпуска у провайдера.
    $issueCostUsd = (float) $card->issue_cost_usd; // снят на шаге 0 с CardProduct.provider_issue_cost_usd

    Expense::create([
        'date'        => now(),
        'category'    => ExpenseCategory::CardIssue,
        'amount'      => round($issueCostUsd * $rate, 2),
        'amount_usd'  => $issueCostUsd,
        'card_id'     => $card->id,
        'provider_id' => $operation->provider_id,
        'comment'     => "Себестоимость выпуска у провайдера (авто, операция #{$operation->id})",
    ]);

    // 2. Сумма пополнения+комиссия провайдера за начальное пополнение (то, что ушло в issueCard()
    // как $topup_usd на шаге 4 — оно же лежит в payload этой операции).
    $topupUsd = (float) ($operation->payload['topup_usd'] ?? 0);
    $feePercent = (float) ($product?->provider_topup_fee_percent ?? 0);
    $feeUsd = round($topupUsd * $feePercent / 100, 2);
    $topupTotalUsd = $topupUsd + $feeUsd;

    if ($feeUsd > 0) {
        Expense::create([
            'date'        => now(),
            'category'    => ExpenseCategory::CardTopup,
            'amount'      => round($topupTotalUsd * $rate, 2),
            'amount_usd'  => $topupTotalUsd,
            'card_id'     => $card->id,
            'provider_id' => $operation->provider_id,
            'comment'     => "Пополнение карты с комиссией провайдера (авто, операция #{$operation->id})",
        ]);
    }
}
```

`amount_usd` в обоих записях — то, что видит `grossProfitStat()`; `amount` (в ₽ по курсу ЦБ
на момент активации) — для единообразия с остальными `Expense`, где `amount` всегда в ₽.

Та же логика (`recordTopupExpense()`) переиспользуется в
`CardProviderOperationResolver::applyTopup()` — для пополнений уже выпущенной карты.
`CardsProWebhookHandler::handleTopup()` (сырой вебхук `CARD_TOPUP` без привязки к
`CardProviderOperation`) намеренно не заводит `Expense` — туда могут прилетать события
вне нашего трекинга операций, и без гарантии «ровно один раз» есть риск задвоить
расход по комиссии (в отличие от баланса, который всегда пишется абсолютным
значением и потому не задваивается).
