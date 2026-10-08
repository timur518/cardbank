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
   пользователя в BitBanker (`POST /api/v3/partner-clients`, см. раздел 3).
5. Если регистрация прошла успешно и BitBanker вернул `check_status=completed` с
   `is_verified_for_sbp=true` — способ оплаты BitBanker становится доступен этому
   пользователю (попадает в его «Разрешённые методы оплаты»), окно
   закрывается, пользователь сразу может продолжить оплату.
6. Если `check_status=pending` (идут фоновые проверки BitBanker, `is_verified_for_sbp`
   пока ни о чём не говорит) — окно сообщает, что заявка на рассмотрении; доступ
   появится автоматически (без повторных действий пользователя), как только
   BitBanker пришлёт `events_webhook` или фоновая синхронизация (раздел 7) увидит
   `check_status=completed`.
7. Если `check_status=completed`, но `is_verified_for_sbp=false` (проверки
   завершились отказом), или сам вызов регистрации завершился ошибкой HTTP
   (у реального API нет детализированных кодов ошибок вроде `NeedCompleteKYC` — проверено
   по DEV-swagger, см. 3.1) — окно показывает, что пополнение через BitBanker временно
   недоступно, с предложением обратиться в поддержку (повторно отправлять те же
   данные бессмысленно — они не редактируются пользователем, т.к. берутся из уже
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
   **только** тогда, когда регистрация в BitBanker завершилась (`check_status=completed`,
   раздел 3.1) с `is_verified_for_sbp=true` (раздел 5.1). Для прочих
   способов оплаты (ParityPay и т.д.) список формируется вручную в админке —
   см. раздел 4.2.

Если позже BitBanker присылает `sbp_client_permission_changed` с
`is_verified_for_sbp=false` (ручная деактивация, например при подозрении на
мошенничество) — запись пользователя для BitBanker удаляется из
`allowedPaymentMethods`, способ оплаты скрывается автоматически.

`is_verified_for_sbp`/`check_status` — **реальные поля из ответа `POST`/`GET`
`/api/v3/partner-clients`**, подтверждены по DEV-swagger (раздел 3.1). Поле
`sbp_top_up`, которое фигурировало в более ранней версии плана, на самом деле относится
к другой ветке API — «калькулятору» `prediction-sbp`/старому `kyc-request` (вариант Б,
который мы не используем, см. 1.1) — в `PartnerClientUpsertResultV3`/`PartnerClientStatusResultV3`
его нет, поэтому дальше по документу оно не используется.

Внутренний KYC (`users.kyc_status=approved`) — обязательное предусловие ещё
раньше: без него недоступны ни паспортные данные для регистрации в BitBanker
(раздел 3.2), ни сам показ способов оплаты с `requires_kyc=true` (уже работает
в текущем коде).

---

## 3. Данные для регистрации клиента в BitBanker

### 3.1. Эндпойнт и поля — подтверждено по DEV-swagger (`https://ext-api.dev.bitbanker.ru/docs/public/openapi`)

Используем **`POST`/`GET /api/v3/partner-clients`** (не v2): тело запроса
у v3 то же самое, что и у v2 (схема `PartnerClientsUpsertRequestV2` общая для обеих
версий), но **ответ v3 дополнительно содержит `check_status`** (`pending`/`completed`) —
это снимает неоднозначность `is_verified_for_sbp=false`, которая у v2 может означать
как «проверки ещё идут, подожди», так и «отклонено».

**Запрос `POST /api/v3/partner-clients`** (заголовки `X-API-KEY` + `Idempotency-Key`,
тело — `PartnerClientsUpsertRequestV2`). Схема формально требует только
`client_id`, `timestamp`, `nonce`, `full_sign` (всё остальное — `nullable`), но фактически
без остальных полей `is_verified_for_sbp` никогда не станет `true`. Важное отличие от
упомянутой ранее выгруженной документации — в схеме есть отдельные поля
`first_name_native`/`last_name_native` («имя/фамилия как в документе») в дополнение к
обычным `first_name`/`last_name` — т.е. BitBanker сам разделяет «имя клиента» и
«имя для сверки с паспортом» (см. маппинг в 3.2). Также есть алиас
`passport_issued_date` → `passport_issue_date` (используем каноническое имя). Поле
`patronymic` — одно на оба имени, без `_native`-варианта.

**Запрос `GET /api/v3/partner-clients`** (опрос статуса) — без `Idempotency-Key`,
параметры `client_id`/`timestamp`/`nonce`/`full_sign` передаются **в query-строке**, а
не в теле — `full_sign` считается по canonical JSON, собранному из этих же параметров
(по той же схеме, что и для тела POST-запросов, см. `BitbankerSigner` в 5.1).

**Создание инвойса `POST /api/v2/invoices`** — валюты подтверждены через
`GET /public/currencies` (DEV): `currency="RUBR"` — это и есть фиатный рубль с
`is_invoice_enabled=true` (а не просто `"RUB"` — такие коды у BitBanker зарезервированы
под другие страновые варианты рубля с `is_invoice_enabled=false`), `take_currency="USDT"`.

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

У BitBanker в схеме `PartnerClientsUpsertRequestV2` есть **две пары полей для имени/фамилии**:
`first_name`/`last_name` (общие, просто «Имя»/«Фамилия») и отдельно `first_name_native`/
`last_name_native` («Имя/Фамилия как в документе»). С учётом этого разделения
логично использовать оба источника: `first_name`/`last_name` — из `User` (общий
профиль, как в ParityPay/других местах личного кабинета), а `first_name_native`/
`last_name_native` — из распознанных Didit данных документа (`extra_fields.*_non_latin`), т.е.
точно так, как написано в паспорте — именно это поле BitBanker сверяет с данными
документа на своём KYC. Это снимает риск, который был в предыдущей версии плана
(брать только OCR-значение в единственное поле `first_name`/`last_name`): теперь `User`-имя
и OCR-имя передаются каждое в своё поле, так и задумано API BitBanker.

У `patronymic` в схеме только одно поле, без `_native`-варианта. Отдельного поля для
отчества кириллицей в `extra_fields` у Didit нет ни у `Passport`, ни у `Identity Card`
(проверено на двух реальных отчётах — см. ниже), но так как для `patronymic` у
самого BitBanker нет отдельной «как в документе»-версии — берём его из `User.middle_name`
(отчество, введённое пользователем в профиле), как и остальные не-`_native` поля.

| Поле BitBanker | Источник | Преобразование |
|---|---|---|
| `client_id` | `User.uuid` | без изменений, значение после первой успешной регистрации неизменяемо на стороне BitBanker |
| `email` | `User.email` | — |
| `phone` | `User.phone` | без изменений (формат `+79991234567` уже используется в проекте) |
| `first_name` | `User.first_name` | — |
| `last_name` | `User.last_name` | — |
| `first_name_native` | `extra_fields.first_name_non_latin` (из элемента `Identity Card` в `id_verifications[]` Didit) | — (кириллица, точно как в паспорте) |
| `last_name_native` | `extra_fields.last_name_non_latin` (та же запись) | — (кириллица, точно как в паспорте) |
| `patronymic` | `User.middle_name` | только если заполнено |
| `birth_date` | `date_of_birth` (та же запись Didit) | `Y-m-d` → `d.m.Y` (в примере: `1995-09-18`) |
| `passport` | `document_number` (та же запись) | убрать пробелы/дефисы; для `Identity Card` ожидается 10 цифр (серия 4 + номер 6) — не путать с `document_number` документа типа `Passport` (9 цифр, как в примере `778447383`) |
| `passport_issue_date` | `date_of_issue` (та же запись) | `Y-m-d` → `d.m.Y` (в примере: `2026-03-06`) |
| `country_of_passport_issue` | `issuing_state` (та же запись) | передаётся как есть (в примере: `RUS`); если не `RUS` — регистрация не выполняется (см. 3.3) |
| `inn` | не передаётся | в системе не собирается |

**Подтверждено на реальном примере `Identity Card`** (внутренний паспорт РФ): `document_type` на верхнем уровне элемента действительно равен строке `"Identity Card"`, а `date_of_birth`/`date_of_issue`/`issuing_state` имеют тот же формат, что и у `Passport` (`Y-m-d`, `RUS`) — код из таблицы выше корректен. Важное уточнение: внутри самого `mrz` у этого же элемента `document_type` равен `"P"` (как и у загранпаспорта) — это подтверждает, что фильтровать тип документа нужно именно по верхнему `document_type`, а не по `mrz.document_type` (в плане так и сделано, дополнительных правок не требуется).

Также подтверждено, что `extra_fields` у `Identity Card` содержит только `full_name_non_latin`, `first_name_non_latin`, `last_name_non_latin`, `first_surname`, `place_of_birth_non_latin` — **отдельного поля для отчества нет**, даже когда оно у человека фактически есть (в конкретном примере латинское `first_name` было `"Natalia Valerevna"`, а `full_name_non_latin` — только `"Наталья Ластавченко"`, без отчества). Поэтому `patronymic` в запрос к BitBanker не передаётся вообще — поле у BitBanker необязательное («передаётся при наличии»), парсить его из `full_name_non_latin` по количеству слов ненадёжно (нет гарантии, что третье слово — именно отчество, а не вторая часть составного имени).

**Открытый момент, требующий допроверки:** в присланном примере JSON обрезался на поле `portrait_image`, до того как показался **верхнеуровневый** `document_number` (тот, что вне объекта `mrz`) — виден только `mrz.document_number = "642184620"` (9 цифр, без контрольного разряда MRZ). Для внутреннего паспорта РФ ожидается 10-значный номер (серия 4 + номер 6), а в предыдущем примере с загранпаспортом верхнеуровневый `document_number` совпадал с `mrz.document_number` дословно. Если эта же закономерность сохранится и для `Identity Card`, то `document_number` будет равен 9 цифрам, а не 10 — это нужно проверить при реализации на полном JSON-ответе (поле `document_number` вне `mrz`), идеально — сверить с реальным номером из физического документа на тестовом пользователе.

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
* `extra_fields.first_name_non_latin` / `last_name_non_latin` непустые (идут в
  `first_name_native`/`last_name_native`);
* `User.first_name`, `User.last_name`, `User.date_of_birth`, `User.phone`,
  `User.email` заполнены (идут в `first_name`/`last_name`/`birth_date`/`phone`/`email`,
  см. 3.2).

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
| `is_verified_for_sbp` | boolean default false | флаг готовности к оплате (из ответа `/api/v3/partner-clients`) — вместе с `check_status=completed` определяет попадание в `payment_method_user` |
| `check_status` | string (`pending`\|`completed`) | сырой `check_status` из ответа BitBanker v3 — `pending`, пока идут фоновые проверки (тогда `is_verified_for_sbp` ещё ни о чём не говорит), `completed` — проверки завершены (итог — в `is_verified_for_sbp`) |
| `last_error` | json nullable | сырое тело последнего неуспешного HTTP-ответа BitBanker (400/401 и т.п.) — у реального API нет детализированных кодов ошибок вроде `NeedCompleteKYC` (проверено по DEV-swagger — см. 3.1), поэтому храним сырое тело целиком для разбора оператором |
| `last_synced_at` | timestamp nullable | когда последний раз обновляли флаги (вебхуком или опросом) |
| `timestamps` | | |

`User::bitbankerClient(): HasOne`, `PaymentMethod::bitbankerClients(): HasMany`.

### 4.4. `payment_methods` — без изменений в схеме

Новый способ оплаты заводится как обычная запись «Способы оплаты» в админке с
`gateway_code=bitbanker`, `requires_kyc=true`, `settlement_config={api_key,
api_secret, base_url?}`. Лимиты (`min_amount`/`max_amount` — 1000–50000 ₽),
`fee_percent`, `sandbox_mode` уже существуют и переиспользуются как есть.

### 4.5. `incomes` — новая колонка `payment_extra`

Найдено при реализации, не было в первоначальной версии плана. `OrderController::issue()`/`topup()`
идемпотентны: повторный запрос с тем же `idempotency_key` возвращает ранее
сохранённые в `Income` поля (`payment_transaction_id`/`payment_url`), не вызывая
`initiate()` повторно. Для BitBanker этого недостаточно — расширенный контракт
(раздел 6.1) возвращает ещё `qr_code`/`fallback_url`, для которых отдельных колонок нет.
Чтобы не потерять их при повторном идемпотентном запросе (например, пользователь
обновил страницу с QR до оплаты), добавлена колонка `payment_extra` (`json nullable`,
после `payment_url`) — по аналогии `payment_url` (та же причина добавления в
своё время). `OrderController` сохраняет туда все ключи результата `initiate()`,
кроме `transaction_id`/`payment_url` (для ParityPay/Stub будет `null` — поведение
не меняется).

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
  `POST /api/v3/partner-clients`, сохраняет/обновляет запись в
  `bitbanker_clients` (`registered_at`, `is_verified_for_sbp`, `check_status`,
  `last_synced_at`), при `check_status=completed` вызывает
  `syncAllowedPaymentMethod()`. На неуспешный HTTP-ответ (400/401) — сохраняет
  сырое тело в `last_error`, пробрасывает `BitbankerException` с общим сообщением
  «не удалось зарегистрировать в BitBanker» (у реального API нет именованных кодов
  ошибок вроде `NeedCompleteKYC` — проверено по DEV-swagger, см. 3.1).
* `refreshStatus(BitbankerClient $client): void` — `GET /api/v3/partner-clients`
  (query `client_id` = `external_client_id`), обновляет
  `is_verified_for_sbp`/`check_status`/`last_synced_at`, вызывает
  `syncAllowedPaymentMethod()`.
* `syncAllowedPaymentMethod(BitbankerClient $client): void` — если
  `check_status === 'completed' && is_verified_for_sbp === true` — `attach()`
  способа оплаты в `user.allowedPaymentMethods` (если ещё не привязан); иначе
  (проверки ещё идут, либо завершились отказом) — `detach()` (если был привязан).
  Единая точка, которую вызывают и `register()`, и `refreshStatus()`, и обработчик
  Events Webhook.

### 5.5. `BitbankerEventsWebhookHandler`

Обрабатывает `sbp_client_permission_changed`: находит `BitbankerClient` по
`data.client_id` (= `external_client_id`), обновляет
`is_verified_for_sbp`/`check_status`/`last_synced_at` из полей вебхука (формат самого
этого события не документирован в OpenAPI — это push от BitBanker к нам, а не их API;
обработчик должен толерантно читать поля, которые фактически пришли, и не падать,
если какого-то нет, доверяя окончательный статус последующему `refreshStatus()` через
фоновую синхронизацию), вызывает
`BitbankerClientService::syncAllowedPaymentMethod()`. При переходе в доступное
состояние — уведомление пользователю через `Notification::notify()`
(`NotificationEvent::BitbankerAvailable`, новый кейс enum). При переходе в
недоступное (деактивация доступа) — отдельное уведомление не отправляется (не
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

### 6.8. `PaymentMethodResource` — новое поле `gateway_code`

Сейчас ресурс отдаёт `id`/`name`/`type`/`currency`/`min_amount`/`max_amount`,
без `gateway_code`. Фронту (раздел 10) нужно надёжно отличать способ BitBanker
среди пришедших способов оплаты, не полагаясь на `name` (как сейчас сделано
для СБП через `isSbp()` — поиск подстроки в названии). Добавляется
`'gateway_code' => $this->gateway_code->value` в `toArray()`.

---

## 7. Новые маршруты

### 7.1. ЛК (авторизованные, `routes/api.php`, группа `v1`)

| Метод | Путь | Назначение |
|---|---|---|
| `GET` | `/v1/bitbanker/status` | Текущее состояние для пользователя: оферта принята? есть ли `bitbanker_clients` запись и в каком она статусе (`is_verified_for_sbp`/`check_status`)? Фронт использует, чтобы решить — показать окно оферты, окно «на рассмотрении», окно отказа или уже доступную оплату. Форма ответа: `{ "offer_accepted": bool, "offer_text": string, "is_verified_for_sbp": bool, "check_status": "pending"\|"completed"\|null }` (`check_status=null`, пока регистрация ещё не запускалась). `offer_text` — текст оферты BitBanker для попапа принятия (раздел 10.3), берётся из настройки `bitbanker_offer_text` (раздел 9), отдаётся этим же эндпойнтом, чтобы не заводить отдельный публичный маршрут под один текст. |
| `POST` | `/v1/bitbanker/accept` | Фиксирует `bitbanker_offer_accepted_at = now()` (идемпотентно) и сразу вызывает `BitbankerClientService::register()`. Возвращает итоговое состояние (`is_verified_for_sbp`, `check_status`) или `422` с текстом ошибки при провале проверки данных/регистрации. |

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
  оператору текущий статус (`is_verified_for_sbp`, `check_status`,
  `last_error`), кнопку «Обновить статус» (дёргает
  `BitbankerClientService::refreshStatus()` вручную).
* Новая настройка `bitbanker_offer_text` (`Setting`) — полный текст оферты
  BitBanker, который пользователь видит в попапе принятия (раздел 10.3). Управляется
  через новую Filament-страницу настроек `BitbankerSettings` (по аналогии
  `ReferralSettings`/`BrandSettings`) с одним полем-textarea; отдаётся в `GET /v1/bitbanker/status`
  (раздел 7.1), без отдельного публичного эндпоинта в `SettingsController`.

---

## 10. Фронтенд (личный кабинет, `resources/cabinet`)

Сейчас блок выбора способа оплаты устроен одинаково в двух местах —
`TopupModal.tsx` (пополнение уже выпущенной карты) и `NewCardOrderPage.tsx` (выпуск
новой): оба просто рендерят `fetchPaymentMethods()` через `<PaymentMethodOption>` в цикле.
Этот блок в обоих местах заменяется на общий компонент `PaymentMethodsList`
(раздел 10.3), который умеет показывать BitBanker первым и с нужным CTA даже до
того, как он появился в списке доступных пользователю способов оплаты.

### 10.1. Почему BitBanker нельзя просто взять из `GET /v1/payment-methods`

`GET /v1/payment-methods` (раздел 6.4) отдаёт только те способы, которыми пользователь
уже может оплатить сейчас. BitBanker попадает туда только после успешной
регистрации (раздел 2) — а до этого момента, по требованию, его всё равно
нужно показывать первым элементом с призывом к KYC/оферте. Поэтому фронт
держит два независимых источника данных:

* обычные способы оплаты — из `GET /v1/payment-methods`, как сейчас;
* состояние BitBanker — из `profile.kyc_status` (уже есть в `AuthContext`, без
  доп. запроса) и нового `GET /v1/bitbanker/status` (раздел 7.1).

Если среди полученных из `GET /v1/payment-methods` способов есть с `gateway_code === 'bitbanker'`
(раздел 6.8) — он уже доступен для оплаты (состояние 4 из 10.2) и рендерится обычной
выбираемой плиткой (`PaymentMethodOption` + бейдж). Если его там нет — вместо него
всё равно первым рендерится некликабельная призывная плитка с нужным CTA (состояния 1–3).
Параллельно сверять все три бэкенд-условия самому фронту не нужно — присутствие/отсутствие
в `GET /v1/payment-methods` уже и есть итоговый флаг «доступно сейчас».

### 10.2. Состояния плитки BitBanker

BitBanker всегда первый в списке и всегда с бейджем «Без комиссии!» (не
только в состоянии 4) — отличается только то, что показано рядом с бейджем — кнопка/текст
состояния или радио-переключатель выбора.

| № | Условие | Что видит пользователь | Действие по кнопке |
|---|---|---|---|
| 1 | `profile.kyc_status !== 'approved'` | Кнопка «Пройти верификацию» вместо радио-кнопки выбора | Тот же поток, что и в «Мой профиль»: `startKycVerification()` + `KycVerificationModal` (тот же iframe Didit). По `didit:completed` — закрыть модалку, `refreshProfile()` (уже есть в `AuthContext`) + перезапросить `GET /v1/bitbanker/status`. |
| 2 | `kyc_status === 'approved'` и `offer_accepted === false` | Кнопка «Принять оферту» | Открывает `BitbankerOfferModal` (раздел 10.3). |
| 3 | `kyc_status === 'approved'`, `offer_accepted === true`, `check_status === 'pending'` (проверки BitBanker ещё идут) | Неактивная плитка, текст «Заявка на рассмотрении», без кнопки | Ничего — обновляется само при следующем монтировании `PaymentMethodsList` (перезапрос `GET /v1/bitbanker/status` при каждом открытии экрана оплаты). |
| 3a | `kyc_status === 'approved'`, `offer_accepted === true`, `check_status === 'completed'`, `is_verified_for_sbp === false` (проверки завершились отказом) | Неактивная плитка, текст «Пополнение через BitBanker недоступно, обратитесь в поддержку» (в отличие от состояния 3, это финальный исход — проверки уже завершены и поллинг сам никогда не сделает `is_verified_for_sbp` истиной) | Ничего. |
| 4 | `kyc_status === 'approved'`, `offer_accepted === true`, `check_status === 'completed' && is_verified_for_sbp === true` | Обычная выбираемая плитка (как ParityPay), первая в списке, с бейджем | Выбор радио-кнопкой, как у любого способа оплаты. |

Состояние 4 на практике совпадает с тем, что BitBanker уже есть в `GET /v1/payment-methods`
(см. 10.1) — поэтому фронту не нужно самому сверять все четыре условия — достаточно
проверить, есть ли BitBanker в списке методов; если нет — смотреть на `kyc_status`/`offer_accepted`/
`is_verified_for_sbp`+`check_status`, чтобы выбрать между 1/2/3/3a.

### 10.3. Новые и изменённые файлы

**Новые:**

* `resources/cabinet/src/api/bitbanker.ts` — `fetchBitbankerStatus(): Promise<BitbankerStatus>`,
  `acceptBitbankerOffer(): Promise<BitbankerStatus>` (оба по аналогии `api/kyc.ts`).
* `resources/cabinet/src/components/orders/BitbankerTile.tsx` — рендерит состояния 1–3
  (некликабельная плитка в том же стиле, что `apply-pay-option` у `PaymentMethodOption`, чтобы
  не выбиваться из списка визуально), с бейджем «Без комиссии!» и кнопкой/текстом
  согласно таблице в 10.2.
* `resources/cabinet/src/components/orders/BitbankerOfferModal.tsx` — попап принятия
  оферты: прокручиваемый блок с `offer_text` (из `GET /v1/bitbanker/status`, раздел 7.1/9),
  чекбокс согласия, кнопка «Принять» (задизаблена, пока чекбокс не отмечен). Три
  внутренних состояния попапа (`idle | loading | success`):
  * клик по «Принять» → `loading` (спиннер/анимированная загрузка) → вызов
    `acceptBitbankerOffer()` (= `POST /v1/bitbanker/accept`);
  * успех (независимо от `is_verified_for_sbp` — важен сам факт регистрации, а
    доступность оплаты уже определяется отдельно через состояния 3/4 плитки) → `success`:
    иконка успеха + текст «Теперь вам доступен более выгодный способ пополнения
    карты!», авто-закрытие через `window.setTimeout(..., 10000)` (с очисткой таймера в
    `useEffect`), по закрытию — колбэк `onAccepted()` наверх, чтобы `PaymentMethodsList`
    перезапросил `GET /v1/payment-methods` и `GET /v1/bitbanker/status`;
  * `422` (данные не прошли проверку — раздел 3.3) → текст ошибки внутри попапа
    с предложением обратиться в поддержку (кнопка «Принять» снова активна, но
    повторная попытка бесполезна — данные не редактируются пользователем в этом попапе).
* `resources/cabinet/src/components/orders/PaymentMethodsList.tsx` — общий компонент
  выбора способа оплаты, заменяющий текущий инлайновый `methods.map(...)` в
  `TopupModal.tsx`/`NewCardOrderPage.tsx`. Пропы: `methods: PaymentMethod[]`,
  `selectedMethodId: number \| null`, `onSelect: (id: number) => void`, `onMethodsRefresh: () => void`
  (вызывается после успешного завершения попапов верификации/оферты, чтобы
  родительский `TopupModal`/`NewCardOrderPage` перезапросил `fetchPaymentMethods()`).
  Внутри себя: через `useAuth()` берёт `profile.kyc_status`, при монтировании
  вызывает `fetchBitbankerStatus()`, выбирает состояние 1–4 по правилам 10.1/10.2,
  рендерит BitBanker первым (либо `BitbankerTile`, либо `PaymentMethodOption` с бейджем), затем
  остальные методы из `methods` (исключая BitBanker, чтобы не задвоить, если он там уже
  есть). Держит открытый `KycVerificationModal`/`BitbankerOfferModal` в своём состоянии.

**Изменённые:**

* `PaymentMethodOption.tsx` — новый необязательный проп `badge?: string`, рендерится
  как `<span className="apply-pay-badge">{badge}</span>` рядом с `apply-pay-name`.
* `api/types.ts` — `PaymentMethod.gateway_code: string` (раздел 6.8); новый интерфейс
  `BitbankerStatus` (форма — раздел 7.1); `IssueOrderResult`/`TopupOrderResult` — новые
  необязательные поля `qr_code: string \| null`, `fallback_url: string \| null` (раздел 10.4).
* `TopupModal.tsx`, `NewCardOrderPage.tsx` — блок `<div className="apply-pay-list">{methods.map(...)}</div>`
  заменяется на `<PaymentMethodsList methods={methods} selectedMethodId={selectedMethodId} onSelect={setSelectedMethodId} onMethodsRefresh={() => fetchPaymentMethods().then(setMethods)} />`;
  обработка результата отправки формы дополняется веткой для QR (раздел 10.4).

### 10.4. Экран оплаты после нажатия «Оплатить» / «Оплатить и выпустить»

Бекэнд возвращает `qr_code`/`fallback_url` только для BitBanker (раздел 6.1); для
всех остальных способов по-прежнему приходит `payment_url`. В обработчике отправки
формы (`handleSubmit` в `TopupModal.tsx`/`NewCardOrderPage.tsx`):

```ts
if (result.payment_url) {
    window.location.href = result.payment_url; // как сейчас — ParityPay, без изменений
} else if (result.qr_code) {
    setQrPayment({ qrCode: result.qr_code, fallbackUrl: result.fallback_url }); // BitBanker
} else {
    onClose();
}
```

Новый компонент `resources/cabinet/src/components/orders/BitbankerQrPaymentModal.tsx`:

* показывает QR как `<img src={`data:image/png;base64,${qrCode}`}>`;
* кнопка-дублёр со ссылкой `fallback_url` («Открыть в приложении банка»);
* визуальный обратный отсчёт 1 час — чисто фронтовый таймер (BitBanker не присылает
  срок истечения отдельно фронту — `dt_expiration` есть только внутри вебхука);
* поллинг готовности оплаты по образцу `useNotifications.ts` (`setInterval`, каждые 5 секунд):
  опрашивает `GET /v1/cards/{id}` — для выпуска карты признак оплаты — пропадание
  `pending_payment` в `null` (тот же признак, на котором сейчас держится `PendingPaymentPanel`), для
  пополнения — изменение баланса карты. По успеху — закрыть модалку, показать
  успех, перейти на страницу карты.

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
8. Фронтенд ЛК (раздел 10): `PaymentMethodsList` + `BitbankerTile` + `BitbankerOfferModal`
   вместо инлайнового списка способов оплаты в `TopupModal`/`NewCardOrderPage`, затем
   `BitbankerQrPaymentModal` на шаге оплаты.
9. Ручное тестирование на DEV по чек-листу из документации (диапазоны сумм
   1000/2000/3000/5000/6000 ₽ → `captured`/`declined`/`failed`/`expired`/`authorized`,
   плюс сценарии `check_status=pending→completed(false)` и ручной деактивации
   `is_verified_for_sbp` через Events Webhook).

---

## 12. Подтверждённые параметры подключения (по DEV-swagger)

Проверено напрямую через OpenAPI-спекификацию и тестовые запросы (401
  на неверный `X-API-KEY`, вместо 404 — значит путь/хост верны):

* **Base URL DEV** — `https://ext-api.dev.bitbanker.ru` (без суффикса — сам swagger
  доступен по корню этого хоста по `/docs/public/openapi`, без `servers` в самой
  спецификации — пути относительны к этому хосту).
* **Base URL PROD** — `https://api.bitbanker.org/latest` (сверено с
  `https://api.bitbanker.org/latest/docs/public/openapi`, данным пользователя).
  Оба базовых URL хранятся в `settlement_config.base_url` способа оплаты —
  переключение DEV→PROD делается в админке сменой URL и `api_key`/`api_secret`,
  без деплоя.
* **Эндпойнты регистрации/статуса клиента** — `POST`/`GET /api/v3/partner-clients`
  (не v2, см. 3.1).
* **Валюты инвойса** — подтверждены через `GET /public/currencies` на DEV:
  `currency="RUBR"` (фиатный рубль с `is_invoice_enabled=true`; просто `"RUB"` у
  BitBanker зарезервирован под другие страновые варианты рубля с
  `is_invoice_enabled=false`), `take_currency="USDT"`.
* **Таймаут HTTP** — `config('services.bitbanker.timeout')`, по аналогии с
  `paritypay`/`cardspro`, значение по умолчанию 20 секунд.

**Не подтверждено через swagger** (документировано только в тексто, поскольку
это push от BitBanker к нам, а не их API) — проверить на реальном тестовом вебхуке
на этапе реализации:

* точный формат тела `invoices_webhook` (поля `exchange_deal`, `payed`, `payed_amount`);
* точный формат тела Events Webhook `sbp_client_permission_changed`;
* верхнеуровневый (вне `mrz`) `document_number` у `Identity Card` в ответе Didit —
  10 цифр или 9 (см. 3.2).
