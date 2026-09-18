# Оформление заказа и выпуск карты — сквозной алгоритм

Документ описывает полный путь от выбора карты клиентом до активной карты с реальным
балансом, с точным маппингом на существующие модели/поля/сервисы
(`app/Services/Integrations/CardsPro`, `CardProviderOperation`,
`CardProviderOperationResolver`, `Income`, `Expense`, `ProviderReserveTopup`). Нужен как
спецификация для будущего API-метода оформления заказа.

По сути запись в ресурсе **«Карты»** и есть «Заказ» в терминах интернет-магазина —
именно она объединяет продукт, покупателя, деньги и статус выполнения.

## Итог по главному вопросу

**При описанном ниже порядке P&L будет сходиться и отражать реальную картину**, при
трёх условиях:

1. `CardProviderOperationResolver::applyIssue()` нужно **переделать** — сегодня он
   *создаёт* запись в `Card`, а должен *обновлять* уже существующую (она заводится раньше, на
   шаге 0, ещё до платежа) — см. шаг 5.
2. Тот же `applyIssue()` должен **автоматически заводить `Expense`** на себестоимость
   выпуска и на сумму пополнения с комиссией провайдера за пополнение карты. Без этого
   «Валовая прибыль» показывает выручку без единой копейки затрат — см. раздел
   «Расходы: авто-Expense на выпуск и пополнение».
3. На `CardProduct` нужно завести новое поле — комиссию провайдера за пополнение
   конкретного продукта (`provider_topup_fee_percent`), она сейчас нигде не хранится —
   см. тот же раздел.
4. Для `Card` надо добавить новый статус "Ожидает оплаты" (waiting) и "Отменен" (cancelled) и "Ошибка" (failed).

---

## Действующие модели (для справки)

| Модель | Ключевые поля, которые участвуют в этом алгоритме |
|---|---|
| `CardProduct` | `price_rub` (цена продажи клиенту, ₽), `provider_issue_cost_usd` (себестоимость выпуска, $ — предлагается переименовать label в «Цена выпуска, $», см. ниже), `provider_topup_fee_percent` (**новое поле** — комиссия провайдера за пополнение карт этого продукта, %, см. «Расходы» ниже), `currency`, `topup_min_amount`/`topup_max_amount` (в валюте продукта), `provider_id`, `provider_product_code`, `billing_*` |
| `Card` | `user_id`, `card_product_id`, `provider_id`, `provider_card_id` (SAN, заполняется только на шаге 5), `card_number`, `expiry`, `currency`, `balance`, `price_rub`, `issue_cost_usd` (то же переименование label, что и у `CardProduct.provider_issue_cost_usd`), `status` (enum `CardStatus`), `billing_*`, `issued_at` |
| `Income` | `type` (enum `IncomeType`), `amount`, `currency`, `amount_usd`, `card_id`, `user_id`, `payment_method_id`, `payment_transaction_id`, `payment_status` (enum `IncomePaymentStatus`), `comment` |
| `Expense` | `date`, `category` (enum `ExpenseCategory`: в т.ч. `CardIssue` = «Выпуск карты», `CardTopup` = «Пополнение карты» — обе категории уже есть, их и используем), `amount`, `amount_usd`, `card_id`, `provider_id`, `comment` |
| `CardProviderOperation` | `provider_id`, `card_id`, `type` (enum `CardProviderOperationType`: issue/topup/withdraw/block), `request_id`, `docid`, `status`, `payload` (json), `result`, `error`, `resolved_at` |
| `ProviderReserveTopup` | `provider_id`, `amount` ($, пополнение мастер-счёта у провайдера), `comment`, `created_by` — учёт того, сколько мы завели денег провайдеру (казначейская метрика, «Резерв у провайдеров» в `ProfitStatsWidget`). Не участвует в P&L по картам — это отдельная метрика ликвидности, не путать с себестоимостью конкретной карты (см. «Расходы» ниже) |
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
   - Наценка на курс уже включает в себя комиссию CardsPro за пополнение карты (3.5%,
     см. «Курсы» ниже) — отдельно её нигде учитывать не нужно.
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
status             = CardStatus::Waiting            // «Ожидает оплаты» - новый статус.
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

Выпуск и пополнение остаются одним «поступлением», как в исходном плане — это
упрощает связку с одним платежом. Если позже понадобится точный разрез «доход от
выпуска» / «доход от пополнений» в графике `IncomeExpenseProfitChartWidget`
(сейчас он умеет строить эти две линии только по `IncomeType::CardIssue`/`::CardTopup`
раздельно) — этот `Income` придётся либо делить на два, либо разбирать по `comment`.
Не критично сейчас, можно отложить.

### 2. Перенаправить на платёжную систему

Создаём платёж в платёжной системе на `total_rub`. Получаем `payment_transaction_id`,
записываем в `Income`, отдаём клиенту ссылку на оплату (редирект).

На этом API-метод оформления заказа заканчивается. Дальше — вебхуки.

---

## Шаг 3. Вебхук платёжной системы подтверждает оплату

1. Находим `Income` по `payment_transaction_id`.
2. Оплата прошла → `payment_status = Paid`. Передаём заказ (по сути — `$card->id`,
   `topup_usd`) в интеграционный модуль.
3. Оплата не прошла → `payment_status = Failed`. `Card` переходит в статус `cancelled`
   без движения дальше.

*(Провайдера платежей в коде пока нет — этот вебхук ещё предстоит реализовать. Пункт
описан на уровне контракта: по каким полям искать `Income` и что делать дальше.)*

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
3. `DECLINED` сразу в ответе — сценарий «оплата прошла, а выпуск с ходу отклонён» - `Card` переходит в статус `Failed` и
   → создаём `CardProviderOperation`:
   ```
   provider_id = CardProduct.provider_id
   card_id     = $card->id            // уже известен — карта создана на шаге 0
   type        = CardProviderOperationType::Issue
   request_id  = <из ответа issueCard()>
   docid       = <из ответа issueCard()>
   status      = //ТУТ НАДО УКАЗАТЬ СТАТУС ЗАПИСИ ОПЕРАЦИИ ПРОВАЙДЕРА. Например Failed если такое есть. или Error
   payload     = []   //ТУТ НАДО УКАЗАТЬ ЧТОБЫ БЫЛА ЗАПИСАНА КОНКРЕТНАЯ ПРИЧИНА ПОЧЕМУ ПРОБЛЕМА С ВЫПУСКОМ КАРТЫ
   ```
   Дальше ничего делать не нужно. 

**Нужна правка кода:** сейчас в `CardProviderOperation.payload` по договорённости
(миграция, `docs/integrations/cardspro.md`) для `issue` снепшотились `user_id` и
`card_product_id`, потому что карты ещё не было и взять владельца было неоткуда. При
новом порядке (карта создаётся на шаге 0) это больше не нужно: `card_id` заполняется
сразу при создании операции. Вместо этого теперь в `payload` кладём `topup_usd` — он
стал обязательным, потому что от него зависит расчёт комиссии за пополнение на
шаге 5 (раздел «Расходы»). Комментарий в миграции `card_provider_operations` и докблок
`CardProviderOperationResolver::applyIssue()` стоит поправить соответственно.

---

## Шаг 5. CardsPro подтверждает выпуск — карта активируется

Это `CardProviderOperationResolver::applyIssue()`. **Требуется переделать логику**:
сегодня метод делает `Card::create(...)` (потому что раньше карты действительно не
было). Теперь карта уже существует с шага 0 — нужно её найти по `operation->card_id` и
обновить, а не создавать заново:

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
        'currency'         => $snapshot['currency'] ?: null, // не затирать снепшот с продукта, если провайдер не прислал
        'balance'          => $snapshot['balance'],
        'status'           => $snapshot['status'],           // обычно сразу Active
        'issued_at'        => now(),
    ]);

    // Новое: авто-Expense на себестоимость выпуска и на комиссию провайдера за
    // начальное пополнение — см. раздел «Расходы» ниже.
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
шаге 3), и два `Expense` (себестоимость выпуска + сумма пополнеия с комиссией за пополнение) заведены —
заказ полностью выполнен и полностью учтён в P&L.

---

## Курсы: где какой брать

| Что считаем | Какой курс | Почему |
|---|---|---|
| Сколько ₽ берём с клиента за пополнение (`topup_rub`, шаг 1) | `rate_sell = currency_rate_usd * (1 + currency_markup_usd_percent/100)` | Цена продажи с наценкой. Наценка — источник выручки, из которой мы покрываем комиссию CardsPro за пополнение (≈ 3.5%) и зарабатываем маржу — но сама комиссия всё равно отдельно ведётся через `Expense` (см. ниже), а не просто неявно «съедается» внутри наценки — иначе реальная себестоимость пополнения нигде не видна |
| `Income.amount_usd` | голый `currency_rate_usd` (без наценки) | «Справедливая» долларовая оценка того, что клиент заплатил в рублях — нужна для сопоставления с расходами в долларах. Если взять курс с наценкой, наценка (по сути, вся наша прибыль на конвертации) исчезнет из отчётов |
| `amount`, отправляемый в `issueCard()` | не курс, а сама сумма `topup_usd`, зафиксированная на шаге 1 | Операционный параметр («сколько долларов положить на карту»), а не бухгалтерская оценка |

---

## Расходы: авто-Expense на выпуск и на комиссию за пополнение

Себестоимость выпуска и комиссию провайдера за пополнение ведём как отдельные `Expense`
на каждую карту — это даёт точную юнит-экономику по каждому выпуску и по каждому
продукту, а не только общую картину по движению денег. Без этого «Валовая прибыль»
(`ProfitStatsWidget::grossProfitStat()`) показывает выручку без вычета затрат.

(`ProviderReserveTopup`/`reserve_balance_usd` остаются как есть — это отдельная казначейская
метрика «сколько денег лежит у провайдера» для контроля ликвидности, она не участвует
в P&L по картам и не требует изменений.)

### Новое поле на `CardProduct`: `provider_topup_fee_percent`

Сейчас комиссия провайдера за пополнение (у CardsPro — 3.5%) нигде не хранится: поле
`topup_fee_percent` было на `CardProvider`, его убрали миграцией
`2026_09_18_120001_drop_fee_columns_from_card_providers_table` с комментарием «экономика
считается на уровне карточных продуктов» — но замену на `CardProduct` тогда так и не
завели. Нужно довести это до конца:

- Миграция: `card_products.provider_topup_fee_percent` (`decimal(5,2)`, `default(0)`).
- `CardProduct::$fillable` + `casts()` — добавить поле аналогично `provider_issue_cost_usd`.
- `CardProductForm` (блок «Провайдер и стоимость») — новое поле «Комиссия провайдера за
  пополнение, %».

### Когда и какие расходы создаются

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
Такая же логика по аналогии пригодится для будущих пополнений уже выпущенной карты
(`CardProviderOperationResolver::applyTopup()` и/или `CardsProWebhookHandler::handleTopup()`) —
это отдельный сценарий (пополнение уже активной карты, а не выпуск), в этот документ
не входит, но стоит сделать там же самое — отмечено в открытых вопросах в конце документа.

---

## Проверка по шагам исходного алгоритма

| #                                                                                          | Пункт из исходного плана | Вердикт |
|--------------------------------------------------------------------------------------------|---|---|
| 1–3 (расчёт цены)                                                                          | Корректно, наценка на курс покрывает и маржу, и комиссию CardsPro за пополнение |
| 0 (создать карту сразу, до поступления)                                                    | Корректно — порядок «сначала карта, потом поступление» подтверждён и оставлен как в исходном плане |
| 0, поле «Цена выпуска... переделать под доллар»                                            | Не нужно — `Card.price_rub` и `Card.issue_cost_usd` уже существуют как два отдельных поля, просто снепшотятся с `CardProduct` без конвертации. Стоит переименовать label `provider_issue_cost_usd`/`issue_cost_usd` в «Цена выпуска, $» для ясности |
| 1 (создать поступление)                                                                    | Корректно, одним `Income` на выпуск+пополнение — как и предполагалось |
| 2 (редирект на платёжную систему)                                                          | Корректно |
| 3 (вебхук оплаты → статус поступления, статус карты)                                       | Корректно; статус карты на этом шаге не меняется (остаётся `Pending`, менять пока не на что) |
| 4 (вызов CardsPro)                                                                         | Корректно — один вызов `issueCard()` и на выпуск, и на пополнение. `CardProviderOperation.card_id` теперь заполняется сразу, а не после |
| 5 (вебхук CardsPro → карта активна)                                                        | Корректно по смыслу; требуется правка `applyIssue()` — обновлять существующую карту, а не создавать новую (см. шаг 5 выше) |
| Новое: авто-`Expense` на себестоимость выпуска и сумму пополнеия с комиссией | Согласовано — создаются в `applyIssue()` сразу после активации карты; требует нового поля `CardProduct.provider_topup_fee_percent` (см. раздел «Расходы») |

---

## Список задач для полной реализации сценария

Всё, что по ходу документа было зафиксировано как «надо сделать» / «нужна правка
кода» / «желательно учесть в других местах», собрано в одном месте.

### Блокируют этот сценарий (без них шаги 0–5 не заработают как описано)

1. **`CardStatus`: добавить статусы `Waiting`** («Ожидает оплаты»), **`Cancelled`**
   («Отменён»), **`Failed`** («Ошибка») — сейчас в `app/Enums/CardStatus.php` только
   `Pending`, `Active`, `Frozen`, `Closed`. Прописать `getLabel()`/`getColor()` для новых
   случаев. Отдельно проверить все места, где код матчится на `CardStatus::Pending`
   (например, действие `CardsTable` с `->visible(fn (Card $record) => $record->status ===
   CardStatus::Pending)`) — после разделения статусов эта логика может относиться
   уже не к тому состоянию карты, как раньше.
2. **`CardProviderOperationResolver::applyIssue()`: переписать** с `Card::create(...)` на
   поиск существующей карты по `operation->card_id` + `update()` — карта теперь
   создаётся на шаге 0, а не тут. Убрать чтение `user_id`/`card_product_id` из `payload`
   (это больше не нужно).
3. **Добавить метод `recordIssueExpenses()`** в `CardProviderOperationResolver`, вызвать его
   в конце `applyIssue()` — создаёт два `Expense` (себестоимость выпуска + сумма
   пополнения с комиссией провайдера).
4. **Новое поле `CardProduct.provider_topup_fee_percent`**: миграция (`decimal(5,2)`,
   `default(0)`), `CardProduct::$fillable`+`casts()`, поле в `CardProductForm`
   («Комиссия провайдера за пополнение, %»). Заполнить значением ≈ 3.5% для
   всех действующих продуктов CardsPro — без этого `recordIssueExpenses()` посчитает
   комиссию нулёвой.
5. **Payload операции `issue`**: класть туда `topup_usd` вместо `user_id`/`card_product_id`.
   Поправить комментарий в миграции `card_provider_operations`, докблок
   `CardProviderOperationResolver::applyIssue()` и описание конвенции payload в
   `docs/integrations/cardspro.md` (строки ≈210–213 и ≈273–274 — там дважды
   зафиксировано старое поведение).
6. **Синхронный `DECLINED` от `issueCard()`** (шаг 4, п. 3): зафиксировать —
   `CardProviderOperation.status = CardProviderOperationStatus::Failed` (кейс уже есть в
   enum), причина отказа CardsPro — в колонку `error` (уже есть в модели и
   используется точно так же в `CardProviderOperationResolver::claim()`), а не в
   `payload`. В документе (шаг 4) пока стоят плейсхолдеры `//ТУТ НАДО...`.
7. **Вебхук платёжной системы** (шаг 3): в коде пока нет интеграции ни с одной
   платёжной системой — этот вебхук предстоит реализовать с нуля (поиск `Income` по
   `payment_transaction_id`, обновление `payment_status`, запуск шага 4).

### Не блокируют этот сценарий, но желательно учесть для полной картины

8. **Распространить логику `recordIssueExpenses()` на послевыпускные пополнения**
   (когда клиент пополняет уже активную карту, а не выпускает новую) —
   `CardProviderOperationResolver::applyTopup()` и/или `CardsProWebhookHandler::handleTopup()`.
9. **Идемпотентность оформления заказа.** Двойной клик/повтор запроса с фронта не
   должен создавать два независимых `Card`+`Income` на один и тот же заказ — стоит
   добавить idempotency-key на API-метод оформления заказа.
10. **(косметика)** Переименовать label `provider_issue_cost_usd`/`issue_cost_usd` в
    «Цена выпуска, $» в формах/инфолистах — для ясности, что поле в долларах, а
    не в рублях.
