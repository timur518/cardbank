# План интеграции BitBanker (СБП → RUB→USDT)

Источник: `Документация API (СБП_ RUB-USDT).md` (приложена в задаче).

Решения по спорным местам из документации BitBanker приняты и зафиксированы ниже
(см. раздел 1.1) — документ описывает финальный сценарий, реализация ведётся по
разделу 11.

---

## 1. Что добавляем и зачем

Новый способ пополнения — оплата через СБП (банковским переводом по QR) с
автоматической конвертацией рублей в USDT на стороне BitBanker. В отличие от
уже подключённого ParityPay (обычный редирект на платёжную форму), у BitBanker
есть дополнительный шаг **до** первого платежа: клиента нужно
зарегистрировать в системе BitBanker, и платёж разрешён только после того, как
BitBanker подтвердит готовность клиента (`is_verified_for_sbp=true`).

### 1.1. Ключевое решение: свой KYC вместо KYC-виджета BitBanker

У BitBanker в документации описаны два способа регистрации клиента:

* Вариант А (`POST /api/v2/partner-clients`) — партнёр сам передаёт ФИО,
  паспортные данные, дату рождения;
* Вариант Б (`POST /api/v1/kyc-request`) — BitBanker выдаёт ссылку на
  собственный виджет SumSub, пользователь проходит верификацию у них.

**Используем вариант А.** Своего KYC (Didit) достаточно: по пользователю,
прошедшему внутреннюю верификацию личности, уже есть всё необходимое для
регистрации в BitBanker (ФИО, дата рождения, паспортные данные из разбора
документа Didit). Отдельная верификация на стороне BitBanker (SumSub,
`kyc_url`, открытие сторонней вкладки) не используется — везде далее по
документу, где в исходных материалах BitBanker описан KYC-виджет/`kyc-request`,
вместо этого отправляется прямой вызов `POST /api/v2/partner-clients` с
данными, которые уже есть в нашей системе.

### 1.2. Пользовательский флоу в личном кабинете

1. Пользователь открывает способ оплаты «BitBanker» на шаге оплаты заказа.
2. Если оферта BitBanker ещё не принята — показывается окно принятия оферты.
3. Пользователь нажимает «Принять».
4. По этому нажатию бэкенд фиксирует принятие оферты и сразу регистрирует
   пользователя в BitBanker (`POST /api/v2/partner-clients`, см. раздел 3).
5. Если регистрация прошла успешно и BitBanker вернул `is_verified_for_sbp=true`
   — способ оплаты BitBanker становится доступен этому пользователю (попадает
   в его «Разрешённые методы оплаты»), окно закрывается, пользователь сразу
   может продолжить оплату.
6. Если `is_verified_for_sbp=false` (BitBanker взял данные на ручную модерацию)
   — окно сообщает, что заявка на рассмотрении; доступ появится автоматически
   (без повторных действий пользователя), как только BitBanker пришлёт
   `events_webhook` или фоновая синхронизация (раздел 7) увидит `true`.
7. Если BitBanker отклонил регистрацию (`NeedCompleteKYC` и т.п.) — окно
   показывает, что пополнение через BitBanker временно недоступно, с
   предложением обратиться в поддержку (повторно отправлять те же данные
   бессмысленно — они не редактируются пользователем, т.к. берутся из уже
   пройденного внутреннего KYC).

---

## 2. Условия доступности способа оплаты BitBanker

Способ оплаты «BitBanker» (запись `PaymentMethod` с `gateway_code=bitbanker`,
`requires_kyc=true`) показывается пользователю в `GET /v1/payment-methods`
только если выполнены оба условия:

1. Общий фильтр «Разрешённые методы оплаты» (раздел 4.2) — метод входит в
   список разрешённых пользователю методов, либо список пуст (не ограничен).
2. Специфично для BitBanker — это и есть сам смысл списка разрешённых методов:
   запись в `allowedPaymentMethods` для BitBanker добавляется автоматически
   **только** тогда, когда регистрация в BitBanker прошла успешно и
   `is_verified_for_sbp=true`, `sbp_top_up=true` (раздел 5.1). Для прочих
   способов оплаты (ParityPay и т.д.) список формируется вручную в админке —
   см. раздел 4.2.

Если позже BitBanker присылает `sbp_client_permission_changed` с
`is_verified_for_sbp=false` (ручная деактивация, например при подозрении на
мошенничество) — запись пользователя для BitBanker удаляется из
`allowedPaymentMethods`, способ оплаты скрывается автоматически.

Внутренний KYC (`users.kyc_status=approved`) — обязательное предусловие ещё
раньше: без него недоступны ни паспортные данные для регистрации в BitBanker
(раздел 3.2), ни сам показ способов оплаты с `requires_kyc=true` (уже работает
в текущем коде).

---

## 3. Данные для регистрации клиента в BitBanker

### 3.1. Обязательные поля `POST /api/v2/partner-clients`

`client_id`, `email`, `phone`, `first_name`, `last_name`, `birth_date`,
`passport`, `passport_issue_date`, `country_of_passport_issue` (поддерживается
только `RUS`) + технические `timestamp`, `nonce`, `full_sign`. Необязательно:
`patronymic`, `inn`.

### 3.2. Источники данных в нашей системе

`id_verifications[]` в `KycVerification.provider_response` Didit может содержать
несколько распознанных документов разных типов (поле `document_type`:
`Identity Card`, `Passport`, `Driver's License` и т.п. — зависит от того, что
загрузил пользователь и что разрешает `workflow_id` в настройках Didit).
BitBanker требует данные именно **внутреннего паспорта гражданина РФ**, а не
загранпаспорта — у Didit это `document_type === "Identity Card"`. Загранпаспорт
(`document_type === "Passport"`, `document_subtype === "EPASSPORT"`) для
регистрации не подходит: у него другой формат номера (9 цифр вместо 10) и в
MRZ нет отчества. Поэтому `BitbankerClientService` берёт из `id_verifications[]`
не первый элемент, а первый с `document_type === "Identity Card"`.

Имя/фамилия берутся не из `User` (их пользователь сам вписывает при
регистрации, без проверки на кириллицу/соответствие паспорту — см.
`RegisterRequest`), а из распознанных Didit данных того же документа
(`extra_fields.*_non_latin`) — BitBanker прямо требует «кириллицей, согласно
паспортным данным».

| Поле BitBanker | Источник (из отчёта `Identity Card` в `id_verifications[]`) | Преобразование |
|---|---|---|
| `client_id` | `User.uuid` | без изменений, значение после первой успешной регистрации неизменяемо на стороне BitBanker |
| `email` | `User.email` | — |
| `phone` | `User.phone` | без изменений (формат `+79991234567` уже используется в проекте) |
| `first_name` | `extra_fields.first_name_non_latin` | — (кириллица, из OCR документа, не из `User`) |
| `last_name` | `extra_fields.last_name_non_latin` | — (кириллица, из OCR документа, не из `User`) |
| `patronymic` | `extra_fields.middle_name_non_latin` (точное имя поля уточнить на реальном `Identity Card`-отчёте — в примере с загранпаспортом такого поля нет, см. выше) | только если заполнено |
| `birth_date` | `date_of_birth` | `Y-m-d` → `d.m.Y` (в примере: `1995-09-18`) |
| `passport` | `document_number` | убрать пробелы/дефисы; для `Identity Card` ожидается 10 цифр (серия 4 + номер 6) — не путать с `document_number` документа типа `Passport` (9 цифр, как в примере `778447383`) |
| `passport_issue_date` | `date_of_issue` | `Y-m-d` → `d.m.Y` (в примере: `2026-03-06`) |
| `country_of_passport_issue` | `issuing_state` | передаётся как есть (в примере: `RUS`); если не `RUS` — регистрация не выполняется (см. 3.3) |
| `inn` | не передаётся | в системе не собирается |

### 3.3. Проверка полноты данных перед вызовом API

`BitbankerClientService::register()` перед запросом к BitBanker проверяет:

* `User.kyc_status === KycStatus::Approved`;
* у пользователя есть `KycVerification` (type=provider, provider=didit,
  status=approved), и в её `provider_response.id_verifications[]` есть элемент
  с `document_type === "Identity Card"` (внутренний паспорт РФ) — если такого
  элемента нет (пользователь проходил верификацию только по загранпаспорту или
  другому документу), регистрация не выполняется;
* у этого элемента непустые `document_number` / `date_of_issue` /
  `issuing_state` и `issuing_state === 'RUS'`;
* `extra_fields.first_name_non_latin` / `last_name_non_latin` непустые;
* `User.date_of_birth`, `User.phone`, `User.email` заполнены.

Если хоть одно условие не выполнено — метод бросает `BitbankerException` с
понятным текстом, контроллер (раздел 6.1) возвращает `422` и фронт показывает
сообщение «обратитесь в поддержку» вместо повторной попытки — так как эти
данные не редактируются пользователем в рамках этого флоу. На практике это
означает: если у клиента в Didit верифицирован только загранпаспорт, доступ к
BitBanker будет недоступен, пока он не пройдёт верификацию ещё раз, загрузив
внутренний паспорт РФ (это уже решается на стороне фронта верификации — вне
рамок данной интеграции).

---

## 4. Новые сущности в БД

### 4.1. `users` — новая колонка

Миграция `add_bitbanker_offer_accepted_at_to_users_table`:

| Колонка | Тип | Описание |
|---|---|---|
| `bitbanker_offer_accepted_at` | `timestamp nullable` | Когда пользователь принял оферту BitBanker. `null` — не принята. По аналогии с уже существующим `personal_data_consent_at` — без отдельного boolean-дублёра. |

### 4.2. `payment_method_user` — pivot-таблица «Разрешённые методы оплаты»

Миграция `create_payment_method_user_table`:

```
id, user_id (FK users, cascadeOnDelete), payment_method_id (FK payment_methods, cascadeOnDelete), timestamps
```

`User::allowedPaymentMethods(): BelongsToMany`.

Семантика: если у пользователя список пуст — ограничений нет, видны все
активные способы оплаты (без регрессии для существующих пользователей). Для
ParityPay и прочих обычных способов админ использует список только чтобы
**сузить** набор конкретному пользователю. Для BitBanker запись в этот список
добавляется и удаляется автоматически кодом (раздел 5.1/7), а не руками в
админке.

### 4.3. `bitbanker_clients` — новая таблица

Миграция `create_bitbanker_clients_table`:

| Колонка | Тип | Описание |
|---|---|---|
| `id` | | |
| `user_id` | FK `users`, unique | один клиент BitBanker на пользователя |
| `payment_method_id` | FK `payment_methods` | к какому способу оплаты (кассе BitBanker) относится — на случай нескольких касс, как у ParityPay |
| `external_client_id` | string | = `User.uuid`, то же значение, что отправлено как `client_id` |
| `registered_at` | timestamp | когда прошла первая успешная регистрация (`POST /api/v2/partner-clients` вернул `2xx`) |
| `is_verified_for_sbp` | boolean default false | флаг готовности к оплате — определяет попадание в `payment_method_user` |
| `sbp_top_up` | boolean default false | |
| `status` | string nullable | последняя причина/код состояния от BitBanker (`manual_review_required`, `kyc_final_rejection`, `access_locked_contact_manager` и т.п.) — для отображения оператору |
| `last_error` | json nullable | сырое тело последней ошибки вызова (`NeedCompleteKYC` + `data.errors` и т.п.) |
| `last_synced_at` | timestamp nullable | когда последний раз обновляли флаги (вебхуком или опросом) |
| `timestamps` | | |

`User::bitbankerClient(): HasOne`, `PaymentMethod::bitbankerClients(): HasMany`.

### 4.4. `payment_methods` — без изменений в схеме

Новый способ оплаты заводится как обычная запись «Способы оплаты» в админке с
`gateway_code=bitbanker`, `requires_kyc=true`, `settlement_config={api_key,
api_secret, base_url?}`. Лимиты (`min_amount`/`max_amount` — 1000–50000 ₽),
`fee_percent`, `sandbox_mode` уже существуют и переиспользуются как есть.

---

## 5. Новая папка интеграции — `app/Services/Integrations/Bitbanker`

По аналогии с `app/Services/Integrations/ParityPay`:

```
app/Services/Integrations/Bitbanker/
├── BitbankerSigner.php          — canonical_json + HMAC-SHA256 (full_sign), генерация nonce
├── BitbankerClient.php          — низкоуровневый HTTP-транспорт (X-API-KEY, timestamp/nonce/full_sign, Idempotency-Key, base_url по sandbox_mode)
├── BitbankerGateway.php         — implements PaymentGatewayContract (создание инвойса + QR, разбор invoices_webhook)
├── BitbankerClientService.php   — регистрация клиента (partner-clients) + опрос статуса + синхронизация allowedPaymentMethods
├── BitbankerEventsWebhookHandler.php — обработка Events Webhook (sbp_client_permission_changed)
└── Exceptions/
    └── BitbankerException.php
```

### 5.1. `BitbankerSigner`

Общая утилита подписи — нужна и для исходящих запросов (`full_sign` тела), и
для входящих вебхуков (проверка `full_sign` в payload): canonical JSON без
`sign`/`sign_2`/`full_sign`, ключи рекурсивно отсортированы, компактный JSON
(`separators=(',',':')`, без экранирования non-ASCII), HMAC-SHA256 hex с
`api_secret` способа оплаты.

### 5.2. `BitbankerClient`

Транспорт, аналог `ParityPayClient`: заголовок `X-API-KEY`, добавляет в тело
`timestamp`/`nonce`/`full_sign` (кроме `kyc-request`/`prediction-sbp`, которым
они не нужны — но эти методы в проекте не используются, см. 1.1), добавляет
`Idempotency-Key` для `POST /api/v2/invoices` и `POST /api/v2/partner-clients`,
бросает `BitbankerException` на `PredefinedError`-ответы и `401`.

### 5.3. `BitbankerGateway implements PaymentGatewayContract`

* `initiate()` — `POST /api/v2/invoices` с `sbp_payment=true`, `currency=RUBR`,
  `is_convert_payments=true`, `take_currency=USDT`,
  `partner_client_external_id=user.uuid`, `Idempotency-Key = Income.idempotency_key`.
  Возвращает `transaction_id` = `id` инвойса и `payment_url` = `sbp_info.qr_url`
  (ссылка НСПК); QR-картинка (`sbp_info.sbp_qr`, base64) и `link` (хостед-страница
  инвойса BitBanker, резервная ссылка) кладутся в дополнительные ключи
  возвращаемого массива — контракт расширяется, см. 6.1.
* `verifyWebhookSignature()` — проверка `full_sign` в теле вебхука (в теле, не
  в заголовке, в отличие от ParityPay) через `BitbankerSigner`.
* `parseWebhookPayload()` — `payed=true` + непустой `exchange_deal` → `paid`;
  `sbp_info.status` в (`declined`/`failed`/`cancelled`/`expired`) → `failed`;
  иначе `unknown` (без действий). Дополнительно возвращает сумму в USDT из
  `exchange_deal[0].volume_take_final` через ключ `amount_usd` (раздел 6.1).
* `refund()` / `getMasterBalance()` — бросают `BitbankerException` «не
  поддерживается» (у BitBanker нет API возврата и API баланса — вывод USDT и
  спорные транзакции обрабатываются вручную через их личный кабинет/почту
  compliance@bitbanker.org).

### 5.4. `BitbankerClientService`

* `register(User $user, PaymentMethod $method): BitbankerClient` — проверяет
  полноту данных (раздел 3.3), собирает payload (раздел 3.2), вызывает
  `POST /api/v2/partner-clients`, сохраняет/обновляет запись в
  `bitbanker_clients` (`registered_at`, `is_verified_for_sbp`, `sbp_top_up`,
  `status`, `last_error`, `last_synced_at`), при `is_verified_for_sbp=true`
  вызывает `syncAllowedPaymentMethod()`. На `NeedCompleteKYC`/прочие ошибки —
  сохраняет `last_error`/`status`, пробрасывает `BitbankerException` вызывающему
  коду с понятным сообщением.
* `refreshStatus(BitbankerClient $client): void` — `GET /api/v2/partner-clients`
  (query `client_id` = `external_client_id`), обновляет
  `is_verified_for_sbp`/`sbp_top_up`/`status`/`last_synced_at`, вызывает
  `syncAllowedPaymentMethod()`.
* `syncAllowedPaymentMethod(BitbankerClient $client): void` — если
  `is_verified_for_sbp && sbp_top_up` — `attach()` способа оплаты в
  `user.allowedPaymentMethods` (если ещё не привязан); иначе — `detach()`
  (если был привязан). Единая точка, которую вызывают и `register()`, и
  `refreshStatus()`, и обработчик Events Webhook.

### 5.5. `BitbankerEventsWebhookHandler`

Обрабатывает `sbp_client_permission_changed`: находит `BitbankerClient` по
`data.client_id` (= `external_client_id`), обновляет
`is_verified_for_sbp`/`sbp_top_up`/`last_synced_at`, вызывает
`BitbankerClientService::syncAllowedPaymentMethod()`. При переходе в
`true` — уведомление пользователю через `Notification::notify()`
(`NotificationEvent::BitbankerAvailable`, новый кейс enum). При переходе в
`false` (деактивация доступа) — отдельное уведомление не отправляется (не
путать пользователя), событие просто логируется через стандартный механизм
`PaymentMethodMessage`.

---

## 6. Изменения в существующей архитектуре

### 6.1. `PaymentGatewayContract` — расширение контракта (обратно совместимо)

1. `initiate()` — в возвращаемый массив добавляются необязательные ключи
   `qr_code` (base64 PNG) и `fallback_url` (хостед-страница инвойса) — для
   способов с оплатой по QR. ParityPay/Stub их не возвращают, как и сейчас.
2. `parseWebhookPayload()` — добавляется необязательный ключ `amount_usd`
   (`float|null`). `PaymentWebhookHandler::handlePaid()` при его наличии
   перезаписывает `Income.amount_usd` этим значением (реальная конвертация
   BitBanker) вместо значения, посчитанного заранее в `OrderController` по
   внутреннему курсу. Отсутствие ключа — поведение не меняется (ParityPay,
   Stub).

### 6.2. `PaymentGatewayCode` — новый кейс

```php
case Bitbanker = 'bitbanker'; // label: 'BitBanker (СБП → USDT)'
```

### 6.3. `PaymentGatewayResolver` — новая ветка `match`

```php
PaymentGatewayCode::Bitbanker => BitbankerGateway::for($paymentMethod),
```

### 6.4. `PaymentMethodController::index()` — фильтрация по пользователю

Добавляется фильтр: если `user.allowedPaymentMethods` не пуст — пересекать с
ним список активных способов, иначе отдавать все активные как сейчас (раздел
4.2). Отдельной BitBanker-специфичной ветки не требуется — доступность
BitBanker уже выражена через присутствие/отсутствие записи в этом же списке
(раздел 2).

### 6.5. `OrderController` — серверная проверка (defense in depth)

В `issue()`/`topup()` перед вызовом `PaymentGatewayResolver::for()` —
проверка, что выбранный `payment_method_id` входит в список доступных
пользователю методов (та же логика, что и 6.4), иначе `422` — чтобы нельзя
было оплатить в обход списка на фронте, подставив `payment_method_id` напрямую.

### 6.6. `NotificationEvent` — новый кейс

`BitbankerAvailable` — «Пополнение через BitBanker теперь доступно» (раздел 5.5).

### 6.7. `PaymentMethodMessage` — переиспользуется как есть

И для `invoices_webhook` (через общий `PaymentWebhookController`, без
изменений в нём), и для Events Webhook (новый контроллер, раздел 7.2) — тот же
тип лога сырых вебхуков, просто разный `event_type`.

---

## 7. Новые маршруты

### 7.1. ЛК (авторизованные, `routes/api.php`, группа `v1`)

| Метод | Путь | Назначение |
|---|---|---|
| `GET` | `/v1/bitbanker/status` | Текущее состояние для пользователя: оферта принята? есть ли `bitbanker_clients` запись и в каком она статусе (`is_verified_for_sbp`/`status`/`last_error`)? Фронт использует, чтобы решить — показать окно оферты, окно «на рассмотрении», окно ошибки или уже доступную оплату. |
| `POST` | `/v1/bitbanker/accept` | Фиксирует `bitbanker_offer_accepted_at = now()` (идемпотентно) и сразу вызывает `BitbankerClientService::register()`. Возвращает итоговое состояние (`is_verified_for_sbp`, `sbp_top_up`, `status`) или `422` с текстом ошибки при провале проверки данных/регистрации. |

### 7.2. Вебхуки (без авторизации, подпись в теле, `routes/api.php` верхний уровень)

| Метод | Путь | Назначение |
|---|---|---|
| `POST` | `/webhooks/payment/{paymentMethod}` | **Уже существует**, не меняется — `invoices_webhook` (оплата) идёт через общий `PaymentWebhookController`, т.к. `BitbankerGateway` реализует тот же контракт. |
| `POST` | `/webhooks/bitbanker/{paymentMethod}/events` | Новый — Events Webhook (`sbp_client_permission_changed`). Отдельный контроллер `BitbankerEventsWebhookController`, т.к. это не про `Income`/оплату, а про статус клиента. |

URL для вебхуков задаётся в личном кабинете BitBanker (Профиль → API) —
аналогично тому, как сейчас для CardsPro URL прописывается в их ЛК.

---

## 8. Фоновая синхронизация

Новая команда `bitbanker:sync-client-status` — опрашивает
`GET /api/v2/partner-clients` для всех `bitbanker_clients`, у которых
`last_synced_at` давно не обновлялся:

* записи с `is_verified_for_sbp=false` — на случай, если решение по ручной
  модерации было принято, а `events_webhook` не дошёл (ретраев у BitBanker нет
  — см. документацию, сама она рекомендует `GET /api/v2/partner-clients` как
  source of truth);
* записи с `is_verified_for_sbp=true` — реже (например, раз в сутки), на
  случай отзыва доступа без вебхука.

В `routes/console.php`:

```php
Schedule::command('bitbanker:sync-client-status')->everyFiveMinutes()->withoutOverlapping();
```

Отмену зависших неоплаченных заказов (`payments:cancel-expired-orders`)
трогать не нужно — она уже работает универсально для любого шлюза по возрасту
`Income`.

---

## 9. Админка (Filament)

* **«Способы оплаты»** (`PaymentMethodForm`) — без структурных изменений,
  только обновить подсказку у `settlement_config`: для BitBanker нужны
  `api_key`, `api_secret`, опционально `base_url`.
* **Пользователь** (`UserInfolist`) — «Оферта BitBanker принята» (булево от
  `bitbanker_offer_accepted_at`) + дата. «Разрешённые методы оплаты»
  (`allowedPaymentMethods`) показываются как обычный список — для BitBanker он
  управляется кодом автоматически, админ может только снять доступ вручную
  (это равнозначно ручной деактивации — при следующей синхронизации код не
  восстановит привязку сам, т.к. источник истины для восстановления —
  `bitbanker_clients.is_verified_for_sbp`, так что ручное снятие носит
  временный характер до следующего успешного `refreshStatus()`; если нужно
  снять доступ окончательно — это делается на стороне BitBanker).
* Новый relation-manager `BitbankerClientRelationManager` (по аналогии с
  `KycVerificationsRelationManager`) на странице пользователя — показывает
  оператору текущий статус (`is_verified_for_sbp`, `sbp_top_up`, `status`,
  `last_error`), кнопку «Обновить статус» (дёргает
  `BitbankerClientService::refreshStatus()` вручную).

---

## 10. Фронтенд (личный кабинет, `resources/cabinet`)

* На шаге выбора способа оплаты, если в ответе `GET /v1/bitbanker/status`
  `offer_accepted=false` — модальное окно оферты с кнопкой «Принять»,
  вызывающей `POST /v1/bitbanker/accept`.
* Пока запрос выполняется — кнопка в состоянии загрузки (вызов синхронный,
  отдельного экрана ожидания не требуется).
* Результат `POST /v1/bitbanker/accept`:
  * `is_verified_for_sbp=true` — окно закрывается, способ оплаты BitBanker
    сразу появляется в списке (обновить `GET /v1/payment-methods`).
  * `is_verified_for_sbp=false` без ошибки — окно сообщает «заявка на
    рассмотрении», закрывается; способ появится в списке автоматически позже
    (поллинг `GET /v1/bitbanker/status` или просто обновление списка способов
    при следующем заходе на экран оплаты).
  * `422` (данные не прошли проверку) — окно показывает сообщение с
    предложением обратиться в поддержку.
* Экран оплаты (после того, как BitBanker выбран и `initiate()` вызван) —
  показ QR (`qr_code`, base64 PNG) + ссылка-дублёр (`fallback_url`) + обратный
  отсчёт 1 час (таймер только на фронте, BitBanker его не присылает) +
  поллинг статуса заказа, как уже сделано для ParityPay.

---

## 11. Порядок реализации (этапы)

1. Миграции + модели (`bitbanker_clients`, `payment_method_user`, поле
   `users.bitbanker_offer_accepted_at`) + `User::allowedPaymentMethods()`,
   `User::bitbankerClient()`.
2. `BitbankerSigner` + `BitbankerClient` (подпись и транспорт) — проверить на
   DEV-стенде BitBanker (нужны DEV-ключи).
3. `BitbankerClientService::register()`/`refreshStatus()`/
   `syncAllowedPaymentMethod()` + маршруты `GET/POST /v1/bitbanker/*`.
4. Events Webhook (`BitbankerEventsWebhookController` + `BitbankerEventsWebhookHandler`)
   + команда `bitbanker:sync-client-status`.
5. `BitbankerGateway` (контракт `PaymentGatewayContract`) + расширение
   контракта (`qr_code`, `fallback_url`, `amount_usd`) + правка
   `PaymentWebhookHandler::handlePaid()`.
6. `PaymentGatewayCode`/`PaymentGatewayResolver` + фильтрация в
   `PaymentMethodController`/`OrderController` по `allowedPaymentMethods`.
7. Админка (поле/relation-manager у пользователя, подсказки в форме способа
   оплаты).
8. Фронтенд ЛК (окно оферты/статуса, QR-экран оплаты).
9. Ручное тестирование на DEV по чек-листу из документации (диапазоны сумм
   1000/2000/3000/5000/6000 ₽ → `captured`/`declined`/`failed`/`expired`/`authorized`,
   плюс сценарии `NeedCompleteKYC` и ручной деактивации `is_verified_for_sbp`).

---

## 12. Технические детали, уточняемые на этапе реализации

Не блокируют начало разработки — по каждому пункту ниже в коде заложен
конкретный вариант по умолчанию, финальное значение подставляется при
получении DEV/PROD-доступов BitBanker:

* **Base URL DEV** — используется `https://api.aws.dev.bitbanker.org/latest`
  по умолчанию (переопределяется `settlement_config.base_url`), сверяется с
  Swagger DEV при получении доступов.
* **Таймаут HTTP** — `config('services.bitbanker.timeout')`, по аналогии с
  `paritypay`/`cardspro`, значение по умолчанию 20 секунд.
