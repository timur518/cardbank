# План интеграции BitBanker (СБП → RUB→USDT)

Статус: **черновик на согласование**. Код ещё не пишется — сначала фиксируем план,
чтобы собрать замечания и принять решения по спорным местам (раздел
«Вопросы на согласование»).

Источник: `Документация API (СБП_ RUB-USDT).md` (приложен в задаче).

---

## 1. Что добавляем и зачем

Новый способ пополнения — оплата через СБП (банковским переводом по QR) с
автоматической конвертацией рублей в USDT на стороне BitBanker. В отличие от
уже подключённого ParityPay (обычный редирект на платёжную форму), у BitBanker
есть дополнительный шаг **до** первого платежа: клиента нужно
зарегистрировать/верифицировать в системе BitBanker (KYC), и платёж разрешён
только после того, как BitBanker подтвердит готовность клиента.

Коротко по шагам (happy path из документации):

1. Клиент соглашается с офертой BitBanker (один раз).
2. Клиент проходит верификацию в BitBanker (виджет SumSub, отдельная вкладка).
3. BitBanker в фоне прогоняет проверки (паспорт, IDX, санкционные списки) и
   выставляет флаги `is_verified_for_sbp` / `sbp_top_up`.
4. Как только оба флага `true` — клиенту становится доступна оплата через
   BitBanker: он создаёт инвойс, получает QR-код, платит.
5. BitBanker после оплаты сам конвертирует RUB → USDT и шлёт вебхук с
   результатом (`exchange_deal`) — из него берём реальную сумму в USDT для
   поля «Сумма в $» у «Поступления».

---

## 2. Вопросы на согласование (нужно подтверждение перед реализацией)

### 2.1. Как регистрировать клиента в BitBanker: вариант А или Б?

- **Вариант А** (`POST /api/v2/partner-clients`) — мы сами собираем и
  передаём ФИО, паспорт (серия+номер), дату выдачи, страну выдачи и т.д.
  Проблема: наш собственный KYC (Didit) не хранит эти поля в структурированном
  виде, пригодном для надёжной автоматической передачи (риск несовпадения
  форматов → `NeedCompleteKYC`/`PassportFailed`).
- **Вариант Б** (`POST /api/v1/kyc-request`, рекомендован самим BitBanker) —
  мы передаём только `external_client_ref` + `email`, BitBanker выдаёт
  одноразовую ссылку на виджет SumSub, пользователь сам проходит верификацию
  (паспорт + селфи) у них, карточка клиента создаётся автоматически.

**Предлагаю вариант Б** — меньше риска из-за несовпадения данных, это и есть
официально рекомендованный сценарий. Требует открытия `kyc_url` в новой
вкладке (не попап) — важно для iOS Safari.

→ Нужно подтверждение, что это устраивает (второй KYC-шаг именно у BitBanker,
отдельно от нашего Didit).

### 2.2. Семантика поля «Разрешённые методы оплаты» у пользователя

Предлагаю реализовать как связь многие-ко-многим с `payment_methods`
(таблица `payment_method_user`), а не просто список кодов шлюзов — это даёт
точный контроль, если когда-то заведём два способа одного шлюза (например,
две кассы ParityPay). В Filament это всё равно один виджет — мультиселект на
форме пользователя.

**Поведение по умолчанию**: если у пользователя список пуст — ограничений нет,
видны все активные способы оплаты (как сейчас, без regression для
существующих пользователей). Админ использует список только тогда, когда
хочет **сузить** набор для конкретного пользователя (например, дать доступ к
BitBanker только пилотной группе, или наоборот — кому-то запретить конкретный
способ).

→ Нужно подтверждение логики «пусто = не ограничено» (альтернатива — «пусто =
запрещено всё», тогда придётся сразу проставить всем существующим
пользователям все текущие методы при миграции, иначе все перестанут видеть
способы оплаты).

### 2.3. Доступность BitBanker — независимые условия

Отдельно от «Разрешённых методов оплаты» (общий список для любого способа)
BitBanker дополнительно скрыт, пока не выполнены **все** условия:

1. Внутренний KYC пользователя пройден (`users.kyc_status === approved`);
2. Оферта BitBanker принята (`users.bitbanker_offer_accepted_at` не пусто);
3. Клиент зарегистрирован и верифицирован в BitBanker
   (`bitbanker_clients.is_verified_for_sbp === true`);
4. `bitbanker_clients.sbp_top_up === true`.

Если пользователь внутри «Разрешённых методов оплаты» получил доступ к
BitBanker, но ещё не прошёл условия 1–4 — способ всё равно не показывается в
списке на оплату, т.к. платёж технически невозможен.

### 2.4. DEV/PROD и `sandbox_mode`

Как и у CardsPro/ParityPay, используем уже существующий переключатель
`PaymentMethod.sandbox_mode`: `true` → DEV-база
(`https://ext-app.dev.bitbanker.ru` или `https://api.aws.dev.bitbanker.org/latest`
— уточнить у BitBanker точный DEV-хост перед запуском, в документе
встречаются оба варианта), `false` → PROD
(`https://api.aws.bitbanker.org/latest`). Переопределяется через
`settlement_config.base_url`, как у ParityPay.

### 2.5. `getMasterBalance()` (баланс мастер-счёта)

У BitBanker нет документированного эндпоинта получения баланса (вывод USDT —
только вручную через их личный кабинет). Этот метод контракта сейчас нигде не
вызывается в коде (проверил — используется только в самих классах шлюзов),
поэтому для BitBanker просто бросаем исключение «не поддерживается», как
`ParityPayGateway::refund()`.

### 2.6. Возвраты (`refund()`)

У BitBanker нет API возврата (см. FAQ в документации — ошибочно
сконвертированные рубли остаются на балансе партнёра). Бросаем исключение
«не поддерживается», аналогично п. 2.5.

---

## 3. Новые сущности в БД

### 3.1. `users` — 2 новых колонки (миграция `add_bitbanker_fields_to_users_table`)

| Колонка | Тип | Описание |
|---|---|---|
| `bitbanker_offer_accepted_at` | `timestamp nullable` | Когда пользователь принял оферту BitBanker. `null` — не принята. Отдельного булева поля не нужно — факт принятия = наличие даты (как уже сделано для `personal_data_consent_at`). |

Итого по ТЗ «3 новых поля»: «принята/не принята» = `bitbanker_offer_accepted_at IS NOT NULL`
(без отдельной колонки-дублёра), + «Разрешённые методы оплаты» — это не
колонка, а связь (см. 3.2). Если нужна отдельная колонка-флаг
`bitbanker_offer_accepted` boolean вместо вычисляемого — скажите, это
тривиально добавить, просто сейчас в коде так же устроено `personal_data_consent_at`
без отдельного boolean, и предлагаю для единообразия.

### 3.2. `payment_method_user` — pivot-таблица (миграция `create_payment_method_user_table`)

```
id, user_id (FK users), payment_method_id (FK payment_methods), timestamps
```

`User::allowedPaymentMethods(): BelongsToMany`.

### 3.3. `bitbanker_clients` — новая таблица (миграция `create_bitbanker_clients_table`)

Аналог `KycVerification`, но для стороны BitBanker:

| Колонка | Тип | Описание |
|---|---|---|
| `id` | | |
| `user_id` | FK, unique | один клиент BitBanker на пользователя |
| `payment_method_id` | FK | к какому способу оплаты (на случай нескольких аккаунтов BitBanker) относится |
| `external_client_id` | string | то, что мы передаём как `external_client_ref`/`client_id` — предлагаю `users.uuid` |
| `partner_client_id` | string nullable | внутренний ID клиента в BitBanker из ответа `kyc-request` |
| `kyc_url` | text nullable | последняя выданная ссылка на верификацию |
| `kyc_url_issued_at` | timestamp nullable | для контроля часа жизни ссылки на своей стороне |
| `is_verified_for_sbp` | boolean default false | главный флаг готовности к оплате |
| `sbp_top_up` | boolean default false | |
| `status` | string nullable | последний `reason`/статус из ответов BitBanker (`kyc_bridge_disabled`, `manual_review_required`, `kyc_final_rejection`, `access_locked_contact_manager` и т.п.) — для показа клиенту/оператору |
| `last_error` | json nullable | сырая ошибка последнего неуспешного вызова (`NeedCompleteKYC` + `data.errors` и т.п.) |
| `last_synced_at` | timestamp nullable | когда последний раз обновляли статус (вебхуком или опросом) |
| `timestamps` | | |

### 3.4. `payment_methods` — без изменений в схеме

Новый способ оплаты заводится как обычная запись «Способы оплаты» в админке с
`gateway_code = bitbanker`, `settlement_config = {api_key, api_secret,
base_url?}`. Поля `requires_kyc`, `sandbox_mode`, `fee_percent`,
`min_amount`/`max_amount` (лимиты 1000–50000 ₽ выставляются здесь же, как у
любого другого способа) уже существуют и переиспользуются.

---

## 4. Новая папка интеграции — `app/Services/Integrations/Bitbanker`

По аналогии с `app/Services/Integrations/ParityPay`:

```
app/Services/Integrations/Bitbanker/
├── BitbankerSigner.php          — canonical_json + HMAC-SHA256 (full_sign), генерация nonce
├── BitbankerClient.php          — низкоуровневый HTTP-транспорт (X-API-KEY, timestamp/nonce/full_sign, Idempotency-Key, base_url по sandbox_mode)
├── BitbankerGateway.php         — implements PaymentGatewayContract (создание инвойса + QR, разбор invoices_webhook)
├── BitbankerClientService.php   — регистрация/опрос статуса клиента (kyc-request, partner-clients GET), синхронизация в BitbankerClient
├── BitbankerEventsWebhookHandler.php — обработка Events Webhook (sbp_client_permission_changed)
└── Exceptions/
    └── BitbankerException.php
```

Назначение каждого файла:

- **`BitbankerSigner`** — общая утилита подписи, нужна и для исходящих
  запросов (`full_sign` тела), и для входящих вебхуков (проверка `full_sign`
  в payload). Отдельный класс, т.к. используется и `BitbankerClient` (запросы),
  и `BitbankerGateway`/`BitbankerEventsWebhookHandler` (проверка вебхуков) —
  логика подписи одна и та же (canonical JSON без `sign`/`sign_2`/`full_sign`,
  `sort_keys`, `separators=(',',':')`, `ensure_ascii=False`, HMAC-SHA256 hex).

- **`BitbankerClient`** — транспорт, аналог `ParityPayClient`: шлёт
  `X-API-KEY`, добавляет `timestamp`/`nonce`/`full_sign` в тело (кроме
  `kyc-request` и `prediction-sbp`, которым они не нужны — см. документацию),
  добавляет `Idempotency-Key` для `POST /api/v2/invoices` и
  `POST /api/v2/partner-clients`, бросает `BitbankerException` на
  `PredefinedError`-ответы и `401`.

- **`BitbankerGateway implements PaymentGatewayContract`**:
  - `initiate()` — вызывает `POST /api/v2/exchange_prediction` (предварительный
    расчёт, просто для логов/проверки ликвидности, не блокирует создание),
    затем `POST /api/v2/invoices` с `sbp_payment=true`, `currency=RUBR`,
    `is_convert_payments=true`, `take_currency=USDT`,
    `partner_client_external_id=user.uuid`, `Idempotency-Key = Income.idempotency_key`.
    Возвращает `transaction_id` = `id` (invoice hash) и `payment_url` =
    `sbp_info.qr_url` (ссылка НСПК) — QR-картинку (`sbp_info.sbp_qr`,
    base64) кладём во второй элемент результата (контракт
    `initiate()` придётся расширить — см. раздел 5.1).
  - `verifyWebhookSignature()` — проверка `full_sign` в теле вебхука (не в
    заголовке, в отличие от ParityPay) через `BitbankerSigner`.
  - `parseWebhookPayload()` — статус `payed=true` + непустой `exchange_deal`
    → `paid`; `declined`/`failed`/`cancelled`/`expired` (из `sbp_info.status`)
    → `failed`; иначе `unknown`/`pending` — без действий. Дополнительно
    возвращает сумму в USDT из `exchange_deal[0].volume_take_final` — см. 5.1.
  - `refund()` / `getMasterBalance()` — бросают «не поддерживается» (см. 2.5–2.6).

- **`BitbankerClientService`** — отдельно от `PaymentGatewayContract` (туда не
  вписывается, это не про оплату, а про подготовку клиента):
  - `startVerification(User $user, PaymentMethod $method): BitbankerClient` —
    вызывает `POST /api/v1/kyc-request` (или переиспользует ещё не
    истёкшую `kyc_url`, если она была выдана меньше часа назад — сам BitBanker
    это уже делает на своей стороне, но дублируем проверку на своей, чтобы не
    дёргать их API лишний раз), сохраняет `kyc_url`/`partner_client_id` в
    `bitbanker_clients`.
  - `refreshStatus(BitbankerClient $client): void` — `GET
    /api/v2/partner-clients?external_id=...`, обновляет
    `is_verified_for_sbp`/`sbp_top_up`/`status`/`last_error`.

- **`BitbankerEventsWebhookHandler`** — обрабатывает
  `sbp_client_permission_changed`: находит `BitbankerClient` по
  `data.client_id` (= `external_client_id`), обновляет
  `is_verified_for_sbp`/`sbp_top_up`. Если флаг стал `true` — можно отправить
  пользователю уведомление «BitBanker доступен для оплаты» (переиспользуем
  `Notification::notify()`/`NotificationEvent`, как у остальных событий).

---

## 5. Изменения в существующей архитектуре

### 5.1. `PaymentGatewayContract` — расширение контракта (обратно совместимо)

Нужно два небольших дополнения:

1. `initiate()` — добавить в возвращаемый массив необязательный ключ
   `qr_code` (base64 PNG) для способов, которые платят через QR, а не
   редирект. ParityPay/Stub его просто не возвращают (как и сейчас).
2. `parseWebhookPayload()` — добавить необязательный ключ `amount_usd`
   (float|null) — заполняется только BitBanker (из `exchange_deal`).
   `PaymentWebhookHandler::handlePaid()` при его наличии перезаписывает
   `Income.amount_usd` этим значением **после** обычной обработки оплаты,
   вместо значения, посчитанного заранее в `OrderController` по нашему
   собственному курсу.

Это не ломает `ParityPayGateway`/`StubPaymentGateway` — они просто не кладут
эти ключи, `PaymentWebhookHandler` трактует отсутствие ключа как «ничего не
менять» (нынешнее поведение).

### 5.2. `PaymentGatewayCode` — новый кейс

```php
case Bitbanker = 'bitbanker'; // label: 'BitBanker (СБП → USDT)'
```

### 5.3. `PaymentGatewayResolver` — новая ветка `match`

```php
PaymentGatewayCode::Bitbanker => BitbankerGateway::for($paymentMethod),
```

### 5.4. `PaymentMethodController::index()` — фильтрация по пользователю

Сейчас отдаёт все активные способы без учёта пользователя. Добавляем:

1. Фильтр по `allowedPaymentMethods` (если у пользователя список не пуст —
   пересечение, иначе без изменений, см. 2.2).
2. Для способов с `gateway_code = bitbanker` — дополнительно проверять
   условия 1–4 из раздела 2.3, иначе исключать из выдачи.

### 5.5. `OrderController` — серверная проверка (defense in depth)

В `issue()`/`topup()` перед вызовом `PaymentGatewayResolver::for()` — та же
проверка, что и в 5.4 (доступность метода конкретному пользователю +
условия BitBanker + принятая оферта), иначе `422` — чтобы нельзя было
оплатить в обход списка на фронте, просто угадав/подставив `payment_method_id`.

### 5.6. `PaymentMethodMessage` — переиспользуется как есть

И для `invoices_webhook` (через общий `PaymentWebhookController`, без
изменений в нём), и для Events Webhook (новый контроллер, см. 6.2) — тот же
тип лога сырых вебхуков, просто разные `event_type`.

---

## 6. Новые маршруты

### 6.1. ЛК (авторизованные, `routes/api.php`, группа `v1`)

| Метод | Путь | Назначение |
|---|---|---|
| `GET` | `/v1/bitbanker/status` | Текущее состояние для пользователя: оферта принята? `kyc_url` (если верификация начата и не завершена)? `is_verified_for_sbp`/`sbp_top_up`? Фронт использует, чтобы решить — показать чекбокс оферты, кнопку «Пройти верификацию» или уже доступную оплату. |
| `POST` | `/v1/bitbanker/offer/accept` | Проставляет `bitbanker_offer_accepted_at = now()` (идемпотентно — повторный вызов ничего не ломает). |
| `POST` | `/v1/bitbanker/kyc` | Запускает/перезапускает верификацию (`BitbankerClientService::startVerification()`), возвращает свежий `kyc_url`. |

### 6.2. Вебхуки (без авторизации, подпись в теле, `routes/api.php` верхний уровень)

| Метод | Путь | Назначение |
|---|---|---|
| `POST` | `/webhooks/payment/{paymentMethod}` | **Уже существует**, ничего менять не нужно — `invoices_webhook` (оплата) идёт через общий `PaymentWebhookController`, т.к. `BitbankerGateway` реализует тот же контракт. |
| `POST` | `/webhooks/bitbanker/{paymentMethod}/events` | Новый — Events Webhook (`sbp_client_permission_changed`). Отдельный контроллер `BitbankerEventsWebhookController`, т.к. это не про `Income`/оплату, а про статус клиента. |

URL для вебхуков задаётся в личном кабинете BitBanker (Профиль → API) —
аналогично тому, как сейчас для CardsPro URL прописывается в их ЛК.

---

## 7. Фоновая синхронизация (резервный канал, как `providers:sync-card-balances`)

Новая команда `bitbanker:sync-client-status` — опрашивает
`GET /api/v2/partner-clients` для всех `bitbanker_clients`, у которых
`is_verified_for_sbp = false` и верификация запускалась (на случай, если
Events Webhook не дошёл — у BitBanker есть ретраи до 7 дней, но лучше не
полагаться только на вебхук, сам документ прямо рекомендует второй канал).
В `routes/console.php`:

```php
Schedule::command('bitbanker:sync-client-status')->everyFiveMinutes()->withoutOverlapping();
```

Отмену зависших неоплаченных заказов (`payments:cancel-expired-orders`) трогать
не нужно — она уже работает универсально для любого шлюза по возрасту
`Income`. Отдельно стоит учесть: QR у BitBanker живёт 1 час, а команда по
умолчанию отменяет через 30 минут — это нормально (просто наш заказ
закроется раньше технического истечения QR), но стоит проговорить, не нужно
ли поднять `--minutes` именно для BitBanker-заказов. Если нужно — добавлю
отдельный запуск команды с фильтром по `gateway_code=bitbanker` и своим
таймаутом.

---

## 8. Админка (Filament)

- **«Способы оплаты»** (`PaymentMethodForm`) — без структурных изменений,
  только обновить подсказку у `settlement_config`: для BitBanker нужны
  `api_key`, `api_secret`, опционально `base_url`.
- **Пользователь** (`UserForm`/`UserInfolist`):
  - Новое поле `Select::make('allowedPaymentMethods')->relationship()->multiple()`
    — «Разрешённые методы оплаты».
  - В `UserInfolist` (только просмотр) — «Оферта BitBanker принята»
    (булево от `bitbanker_offer_accepted_at`) + сама дата.
  - Новый relation-manager `BitbankerClientRelationManager` (по аналогии с
    `KycVerificationsRelationManager`) — показывает оператору текущий статус
    верификации в BitBanker, `last_error`, кнопку «Обновить статус»
    (дёргает `BitbankerClientService::refreshStatus()` вручную).

---

## 9. Фронтенд (личный кабинет, `resources/cabinet`)

Детальный список компонентов уточним на этапе реализации (после согласования
бэкенда), ориентировочно:

- На шаге выбора способа оплаты (`TopupModal`/`NewCardOrderPage`) — если
  выбран BitBanker и `GET /bitbanker/status` говорит, что оферта не принята —
  чекбокс согласия перед кнопкой «Оплатить» (запрашивается один раз).
- Если верификация не пройдена — кнопка «Пройти верификацию» → открывает
  `kyc_url` в новой вкладке (`target="_blank"`, обязательно по доке — iOS
  Safari блокирует попапы) → после возврата поллинг `GET /bitbanker/status`
  до `is_verified_for_sbp=true`.
- Экран оплаты — показ QR (`sbp_qr`, base64 PNG) + ссылка-дублёр
  (`qr_url`) + обратный отсчёт 1 час (таймер только на фронте, BitBanker его
  не присылает) + поллинг статуса заказа, как уже сделано для ParityPay (если
  там есть поллинг — переиспользуем тот же компонент).

---

## 10. Конфиг/ENV

`config/services.php`, блок `bitbanker` — только таймаут HTTP, по аналогии с
`paritypay`/`cardspro` (ключи — в `settlement_config`, не в `.env`):

```php
'bitbanker' => [
    'timeout' => (int) env('BITBANKER_TIMEOUT', 20),
    // DEV/PROD base_url по умолчанию — через sandbox_mode способа оплаты,
    // см. BitbankerClient::baseUrl(). Переопределяется settlement_config.base_url.
],
```

---

## 11. Порядок реализации (этапы)

1. Миграции + модели (`bitbanker_clients`, `payment_method_user`, поле
   `users.bitbanker_offer_accepted_at`) + `User::allowedPaymentMethods()`.
2. `BitbankerSigner` + `BitbankerClient` (подпись и транспорт) — можно
   проверить вручную на DEV-стенде (нужны DEV-ключи BitBanker).
3. `BitbankerClientService` (регистрация/опрос статуса) + Events Webhook +
   команда `bitbanker:sync-client-status`.
4. `BitbankerGateway` (контракт `PaymentGatewayContract`) + расширение
   контракта (`qr_code`, `amount_usd`) + правка `PaymentWebhookHandler`.
5. `PaymentGatewayCode`/`PaymentGatewayResolver` + фильтрация в
   `PaymentMethodController`/`OrderController`.
6. Админка (поле у пользователя, relation-manager, подсказки в форме способа
   оплаты).
7. Фронтенд ЛК (оферта, кнопка верификации, QR-экран).
8. Ручное тестирование на DEV по чек-листу из документации (диапазоны сумм
   1000/2000/3000/5000/6000 ₽ → `captured`/`declined`/`failed`/`expired`/`authorized`).

---

## 12. Открытые технические детали, которые уточним по ходу

- Точный DEV base_url (в документе фигурируют и
  `https://ext-app.dev.bitbanker.ru`, и `https://api.aws.dev.bitbanker.org/latest`
  — похоже, первый для ЛК/виджета, второй для API; нужно свериться с
  Swagger DEV при получении доступов).
- Нужно ли показывать клиенту ссылку `link` на страницу инвойса BitBanker как
  запасной вариант, если свой QR-экран не отрендерился (документ это
  предлагает как fallback).
- Нужна ли отдельная Telegram/email-нотификация оператору при
  `NeedCompleteKYC`/`SecurityFailed`-отказах (как уже сделано для других
  событий через `AdminTelegramNotifier`).

---

Жду правок/подтверждения по разделу 2 — после этого перехожу к реализации по
пунктам раздела 11.
