# API для личного кабинета (ЛК) — спецификация методов

Документ фиксирует список API-методов для клиентского личного кабинета (React SPA на
поддомене `app.<domain>`, см. решение — ЛК собирается отдельно от Blade-лендинга и
Filament-админки, ходит в общий Laravel-бэкенд по REST API). Служит основанием для
реализации контроллеров — методы ниже пока не реализованы, кроме уже существующих
моделей/сервисов, на которые есть прямые ссылки.

---

## Общие принципы

- **Базовый путь:** `/api/v1/...` (существующий `/api/webhooks/...` — отдельный,
  серверный, к ЛК не относится).
- **Аутентификация:** Laravel Sanctum, SPA-режим (сессионная кука на `.<domain>`,
  клиент сначала берёт CSRF-токен через `GET /sanctum/csrf-cookie`, дальше все запросы
  с `credentials: include`). Sanctum пока не установлен — нужно
  `composer require laravel/sanctum` и настройка `SANCTUM_STATEFUL_DOMAINS`.
- **Формат ошибок:** стандартный Laravel — `422` с `{"message": "...", "errors": {"field": ["..."]}}`
  для валидации, `401` для неавторизованного запроса, `403` для запрета доступа
  (чужая карта, заблокированный аккаунт), `404` для отсутствующей записи.
- **Формат ответа:** одиночный ресурс — `{"data": {...}}`; список — стандартная
  Laravel-пагинация: `{"data": [...], "meta": {...}, "links": {...}}`.
- **Деньги:** суммы в ответах — числа с двумя знаками после запятой, валюта — отдельным
  полем (`currency: "USD"|"RUB"`), без форматирования (форматирует фронт).
- **Все методы ниже, кроме раздела 1 (частично) и раздела 2, требуют авторизации.**

---

## Раздел 1. Аутентификация и профиль

### 1. `POST /api/v1/auth/login` — вход по телефону/email + паролю

Не требует авторизации.

**Запрос:**
```json
{ "login": "+7(999)123-45-67", "password": "..." }
```
`login` — телефон (`users.phone`) или email (`users.email`), определяется по формату. Фронт присылает телефон с маской ввода
(`+7(999)123-45-67`) — сервер нормализует его перед поиском по `users.phone` (убирает
всё, кроме цифр и ведущего `+`, т.е. приводит к `+79991234567`) — в `users.phone` хранится
и ищется только нормализованный вид.

**Ответ `200`:** `{"data": {...профиль, см. п.3...}}`, сессионная кука выставляется.

**Ошибки:** `422` неверный формат, `401` неверные телефон/email или пароль,
`403` аккаунт заблокирован (`users.is_blocked = true`).

---

### 2. `POST /api/v1/auth/register` — регистрация

Не требует авторизации.

**Запрос:**
```json
{
  "first_name": "Тимур",
  "last_name": "Халяпов",
  "middle_name": "Рамилевич",
  "phone": "+7(999)123-45-67",
  "email": "you@example.com",
  "date_of_birth": "01.01.1990",
  "password": "...",
  "password_confirmation": "...",
  "personal_data_consent": true,
  "referral_code": "AB12CD",
  "utm_source": "yandex",
  "utm_medium": "cpc",
  "utm_campaign": "spring2026",
  "utm_content": "banner1"
}
```
`personal_data_consent` — обязательно `true`, иначе `422`. `phone` приходит с маски
(`+7(999)123-45-67`) — нормализуется так же, как в п. 1 (только цифры и ведущий `+`). `date_of_birth`
приходит с маски в формате `дд.мм.гггг` (`01.01.1990`) — сервер парсит её и хранит
в `users.date_of_birth` как `Y-m-d`. `referral_code` — необязательный, код партнёра, по которому
пришёл клиент — пишется как есть в `users.referral_code` (поле уже есть, используется
в `ProfitStatsWidget` для расчёта LTV рефералов — без валидации против `Partner.code`, так же, как
сейчас реализовано в `UserForm` в админке, чтобы опечатка в коде не блокировала регистрацию).

**Побочный эффект:** создаётся `User` (`kyc_status = KycStatus::NotStarted`,
`is_blocked = false`, `personal_data_consent_at = now()`), поля `utm_*` и `referral_code`
копируются как есть на модель. Сразу после создания контроллер
вызывает `$user->assignRole('customer')` (Spatie `HasRoles`, уже подключён к `User`) — все
пользователи, зарегистрировавшиеся через этот метод, всегда получают роль `customer` по
умолчанию — это единственная точка входа для клиентских аккаунтов, никакие другие роли
через API ЛК никогда не выдаются.

**Ответ `201`:** `{"data": {...профиль...}}`, сессионная кука выставляется (автологин
после регистрации).

**Ошибки:** `422` — занятый `phone`/`email`, несовпадение паролей, отсутствие согласия.

---

### 3. `GET /api/v1/profile` — получить данные профиля

**Ответ `200`:**
```json
{
  "data": {
    "id": "018f2c9e-3d7a-7b52-9c76-1a2b3c4d5e6f",
    "first_name": "Тимур",
    "last_name": "Халяпов",
    "middle_name": "Рамилевич",
    "phone": "+79991234567",
    "email": "you@example.com",
    "date_of_birth": "1990-01-01",
    "kyc_status": "not_started",
    "two_factor_enabled": false,
    "referral_code": "AB12CD",
    "created_at": "2026-01-01T00:00:00Z"
  }
}
```
`id` — это `users.uuid`, не сквозной автоинкрементный `users.id`: сквозной PK по API нигде не
отдаётся (чтобы по номеру нельзя было оценить общее число пользователей и не было соблазна перебирать
чужие аккаунты подстановкой соседнего id). `users.uuid` генерируется автоматически
при создании `User` (`User::booted()`).

Поля `password`, `is_blocked`, `block_reason` (модерационные) в ответ не включаются.

---

### 4. `PATCH /api/v1/profile` — изменить данные профиля (кроме email)

**Запрос:** любое подмножество `first_name`, `last_name`, `middle_name`, `phone`,
`date_of_birth`. Поле `email` в списке разрешённых отсутствует — при попытке
передать игнорируется (или `422`, если строго валидировать через `prohibited`).

**Ответ `200`:** `{"data": {...обновлённый профиль...}}`.

---

### 5. `POST /api/v1/auth/password/forgot` — запрос восстановления пароля

Не требует авторизации.

**Запрос:** `{"login": "+79991234567"}` (телефон или email).

**Побочный эффект:** генерируется новый случайный пароль, сохраняется
(`users.password`, хэшируется), отправляется письмом на `users.email` (у пользователя
`email` всегда есть — обязательное поле при регистрации, даже если вход был по
телефону).

**Ответ `200`:** `{"message": "Новый пароль отправлен на почту"}` — не палим,
существует ли аккаунт с таким телефоном/email (одинаковый ответ в обоих случаях).

---

### 6. `POST /api/v1/auth/logout` — выйти из аккаунта

**Побочный эффект:** завершает текущую сессию Sanctum.

**Ответ:** `204 No Content`.

---

### 7. `POST /api/v1/profile/password` — сменить пароль из профиля

**Запрос:**
```json
{ "current_password": "...", "password": "...", "password_confirmation": "..." }
```

**Ответ `200`:** `{"message": "Пароль изменён"}`.

**Ошибки:** `422` — `current_password` не совпадает с текущим, либо пароли не
совпадают.

---

## Раздел 2. Настройки (только чтение для ЛК)

Значения хранятся в `Setting` (ключ-значение), редактируются только в
Filament-админке (`BrandSettings`, `ReferralSettings`, `CurrencySettings`). ЛК их
только читает.

### 8. `GET /api/v1/settings/brand` — бренд и сайт

**Ответ `200`:**
```json
{
  "data": {
    "brand_domain": "cardbank.ru",
    "brand_site_name": "CardBank",
    "brand_support_email": "support@cardbank.ru",
    "brand_logo": "https://.../storage/....png",
    "brand_theme_color": "#f37338"
  }
}
```
Источник — `Setting::getMany(BrandSettings::KEYS)`.

---

### 9. `GET /api/v1/settings/referral` — реферальная система

**Ответ `200`:**
```json
{
  "data": {
    "referral_issue_rate": "10.00",
    "referral_topup_rate": "5.00",
    "referral_hold_days": "14",
    "referral_min_wallet_rub": "500.00",
    "referral_min_bank_rub": "1000.00"
  }
}
```
Источник — `Setting::getMany(ReferralSettings::KEYS)`.

---

### 10. `GET /api/v1/settings/currency-rates` — курсы продажи валют

Отдаётся только итоговый курс продажи (с нашей наценкой) — сырой курс ЦБ и процент
наценки клиенту не нужны (на фронте используется только итоговая цифра для калькулятора
суммы пополнения ₽ ⇄ $ и цены выпуска, а сама наценка — внутренняя экономика, как и
`provider_issue_cost_usd`/`fee_percent` в п. 11–12). Расчёт на бэкенде остаётся тем же, что и в
`CurrencySettings::costHelperText()` (`sell_rate = rate * (1 + markup_percent / 100)`),
просто в ответ попадает только итоговое число.

**Ответ `200`:**
```json
{
  "data": {
    "usd": "95.50",
    "eur": "103.10",
    "gbp": "121.95"
  }
}
```
Источник — `Setting::getMany(['currency_rate_usd', 'currency_markup_usd_percent', ...])` для
всех трёх валют, но в ответ попадает только вычисленный `sell_rate`.

---

## Раздел 3. Каталог и оформление заказа

### 11. `GET /api/v1/card-products` — карточные продукты

Список активных продуктов (`CardProduct.active = true`), отсортированных по `sort`.
Себестоимостные поля (`provider_issue_cost_usd`, `provider_topup_fee_percent`,
`provider_id`, `provider_product_code`, `billing_*`) клиенту не отдаются — это
внутренняя себестоимость, не имеет отношения к ЛК.

`skin` хранится как относительный путь на диске `public` (загружается через
`FileUpload` в `CardProductForm`, директория `card-skins`) — в ответе отдаётся полным
URL (`Storage::disk('public')->url($path)`), аналогично `brand_logo` в п. 8.

**Ответ `200`:**
```json
{
  "data": [
    {
      "id": 1,
      "key": "black",
      "name": "Black",
      "description": "Для онлайн платежей, подписок и сервисов.",
      "skin": "https://.../storage/card-skins/....png",
      "currency": "USD",
      "price_rub": "990.00",
      "provider_kyc_required": false,
      "apple_pay_enabled": false,
      "google_pay_enabled": false,
      "issue_min_amount": "10.00",
      "issue_max_amount": "5000.00",
      "topup_min_amount": "10.00",
      "topup_max_amount": "5000.00",
      "coming_soon": false
    }
  ]
}
```

---

### 12. `GET /api/v1/payment-methods` — способы приёма платежей

Список активных методов (`PaymentMethod.status = ActiveStatus::Active`) для выпуска и
пополнения карт. `fee_percent` (наша себестоимость приёма платежа) клиенту не
отдаётся — как и с `CardProduct`, это внутренняя экономика.

**Ответ `200`:**
```json
{
  "data": [
    { "id": 1, "name": "СБП", "type": "gateway", "currency": "RUB", "min_amount": "10.00", "max_amount": "150000.00" },
    { "id": 2, "name": "Банковская карта", "type": "gateway", "currency": "RUB", "min_amount": "10.00", "max_amount": "150000.00" }
  ]
}
```

---

### 13. `POST /api/v1/orders/issue` — создать заказ на выпуск карты

Реализует «Шаг 1» и «Шаг 2» из `CARD_ORDER_AND_ISSUANCE_FLOW.md`: создаёт `Card`
(`status = CardStatus::Waiting`) и `Income` (`payment_status = IncomePaymentStatus::Pending`),
инициирует платёж, возвращает ссылку на оплату. Дальнейшая обработка — по вебхуку
платёжной системы (шаг 3) и вебхуку CardsPro (шаги 4–5), в этом методе не участвует.

**Запрос:**
```json
{
  "card_product_id": 1,
  "topup_amount": 100,
  "topup_currency": "USD",
  "payment_method_id": 1,
  "idempotency_key": "c3f1e2a0-...-uuid"
}
```
`topup_currency` — `"USD"` или `"RUB"`, сумма пересчитывается по `rate_sell` (п. 10) в
недостающую валюту на сервере — фронтовый расчёт из шага 1 алгоритма только для
отображения, авторитетный пересчёт всегда серверный. `idempotency_key` — опциональный
UUID от клиента; если не передан, сервер генерирует и возвращает свой (повторный
запрос с тем же ключом не создаёт вторую `Card`/`Income`).

**Валидация:** `topup_amount` (в USD) — в границах `CardProduct.topup_min_amount`/
`topup_max_amount`.

**Ответ `201`:**
```json
{
  "data": {
    "card_id": 42,
    "status": "waiting",
    "price_rub": "990.00",
    "topup_usd": "100.00",
    "topup_rub": "9550.00",
    "total_rub": "10540.00",
    "payment_transaction_id": "pt_...",
    "payment_url": "https://payment-gateway.example/pay/pt_...",
    "idempotency_key": "c3f1e2a0-...-uuid"
  }
}
```

---

### 14. `POST /api/v1/orders/topup` — создать заказ на пополнение карты

Пополнение уже выпущенной карты (`Card.status = CardStatus::Active`). Создаёт `Income`
(`type = IncomeType::CardTopup`, `payment_status = Pending`), привязанный к
существующей карте, инициирует платёж. Подтверждение — по вебхуку платёжной системы,
затем `CardsProService::topUpCard()` → `CardProviderOperation` (`type = Topup`) →
`CardProviderOperationResolver::applyTopup()` (уже реализован).

**Запрос:**
```json
{
  "card_id": 42,
  "amount": 50,
  "currency": "USD",
  "payment_method_id": 1,
  "idempotency_key": "c3f1e2a0-...-uuid"
}
```

**Валидация:** карта принадлежит текущему пользователю и `status = Active`; сумма (в
USD) — в границах `CardProduct.topup_min_amount`/`topup_max_amount` карты.

**Ответ `201`:**
```json
{
  "data": {
    "card_id": 42,
    "topup_usd": "50.00",
    "topup_rub": "4775.00",
    "total_rub": "4775.00",
    "payment_transaction_id": "pt_...",
    "payment_url": "https://payment-gateway.example/pay/pt_...",
    "idempotency_key": "c3f1e2a0-...-uuid"
  }
}
```

---

## Раздел 4. Карты и история операций

### 15. `GET /api/v1/cards` — список карт пользователя

Только карты текущего пользователя (`Card.user_id = auth()->id()`).

**Ответ `200`:**
```json
{
  "data": [
    {
      "id": 42,
      "card_product": { "key": "black", "name": "Black", "skin": "https://.../storage/card-skins/....png" },
      "status": "active",
      "currency": "USD",
      "balance": "150.00",
      "card_last4": "4242",
      "expiry": "12/29",
      "issued_at": "2026-09-01T10:00:00Z"
    }
  ]
}
```
`card_last4` — новый безопасный аксессор поверх скрытого `card_number` (модель `Card`
целиком прячет `card_number`/`expiry`/`cvv` в `$hidden`). Полный номер карты и CVV по
API не отдаются никогда — это PCI DSS зона провайдера (Apple Pay/Google Pay
провижининг и просмотр реквизитов — через отдельный защищённый виджет провайдера,
не через наш API).

---

### 16. `GET /api/v1/cards/{card}` — информация о карте с балансом

`{card}` — id карты, доступ только если `card.user_id === auth()->id()` (иначе `403`). Используется
страницей одной карты в ЛК.

**Ответ `200`:** тот же набор полей, что и элемент списка в п. 15 (`CardResource`), плюс два
поля, нужных только странице одной карты (`CardDetailResource extends CardResource`):
```json
{
  "data": {
    "id": 42,
    "card_product": { "key": "black", "name": "Black", "skin": "https://.../storage/card-skins/....png" },
    "status": "active",
    "currency": "USD",
    "balance": "150.00",
    "card_last4": "4242",
    "expiry": "12/29",
    "issued_at": "2026-09-01T10:00:00Z",
    "price_rub": "990.00",
    "billing_address": {
      "country": "US",
      "city": "New York",
      "region": "NY",
      "address": "228 Park Ave S",
      "post_code": "10003"
    }
  }
}
```
`price_rub` — стоимость выпуска этой конкретной карты (зафиксирована на `Card.price_rub` в момент
выпуска, не меняется при изменении цены продукта). `billing_address` — платёжный
адрес карты (AVS), скопированный с `CardProduct.billing_*` при создании карты в `OrderController::issue()`;
любое поле может быть `null`, если админ не заполнил его в карточке продукта. В
`card_product` также добавлены `topup_min_amount`/`topup_max_amount` (вкладка «Лимиты» на странице
карты в ЛК).

---

### 16.1. `GET /api/v1/cards/{card}/requisites` — полный номер и CVV

В отличие от п. 16, отдаётся только по явному запросу с фронта (кнопки «Показать
реквизиты» / «Показать CVV» на странице карты), чтобы полный PAN/CVV не утекал при
каждой загрузке страницы карты. Та же проверка владения (`403`), плюс `404`, если карта ещё
не выпущена провайдером (`Card.card_number` пуст, статус waiting/pending). В отличие от P&L/админки,
владельцу виртуальной карты её полный номер и CVV нужны, чтобы реально расплачиваться ею в
интернете — это не PCI DSS зона провайдера (как Apple Pay/Google Pay провижининг), а обычные
реквизиты для ручного ввода на сайте оплаты.

**Ответ `200`:**
```json
{
  "data": {
    "card_number": "5592 6801 0068 6338",
    "expiry": "12/29",
    "cvv": "123"
  }
}
```

---

### 17. `GET /api/v1/transactions` — история транзакций по пользователю

История по всем картам текущего пользователя (join `CardTransaction` → `Card` по
`user_id`). Поля `cost_amount`/`commission_amount` (внутренняя себестоимость и наша
комиссия) клиенту не отдаются — это P&L-поля для админки, не для ЛК.

**Query-параметры:** `page`, `per_page`, `type` (фильтр по `CardTransactionType`),
`card_id` (фильтр по конкретной карте), `date_from`, `date_to`.

**Ответ `200`:**
```json
{
  "data": [
    {
      "id": 501,
      "card_id": 42,
      "type": "purchase",
      "amount": "25.00",
      "currency": "USD",
      "merchant": "Amazon.com",
      "status": "completed",
      "occurred_at": "2026-09-10T12:00:00Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "total": 57 }
}
```

---

### 18. `GET /api/v1/cards/{card}/transactions` — история транзакций по карте

То же самое, что и п. 17, но только по одной карте (`{card}` — id, с той же проверкой
владения `card.user_id === auth()->id()`, иначе `403`). Использует те же
query-параметры, кроме `card_id` (он не нужен — карта уже задана в пути).

---

## Сводная таблица

| № | Метод | Путь | Авторизация |
|---|---|---|---|
| 1 | `POST` | `/api/v1/auth/login` | нет |
| 2 | `POST` | `/api/v1/auth/register` | нет |
| 3 | `GET` | `/api/v1/profile` | да |
| 4 | `PATCH` | `/api/v1/profile` | да |
| 5 | `POST` | `/api/v1/auth/password/forgot` | нет |
| 6 | `POST` | `/api/v1/auth/logout` | да |
| 7 | `POST` | `/api/v1/profile/password` | да |
| 8 | `GET` | `/api/v1/settings/brand` | нет |
| 9 | `GET` | `/api/v1/settings/referral` | нет |
| 10 | `GET` | `/api/v1/settings/currency-rates` | нет |
| 11 | `GET` | `/api/v1/card-products` | нет |
| 12 | `GET` | `/api/v1/payment-methods` | нет |
| 13 | `POST` | `/api/v1/orders/issue` | да |
| 14 | `POST` | `/api/v1/orders/topup` | да |
| 15 | `GET` | `/api/v1/cards` | да |
| 16 | `GET` | `/api/v1/cards/{card}` | да |
| 16.1 | `GET` | `/api/v1/cards/{card}/requisites` | да |
| 17 | `GET` | `/api/v1/transactions` | да |
| 18 | `GET` | `/api/v1/cards/{card}/transactions` | да |

Методы 8–12 не требуют авторизации намеренно — калькулятор цены и каталог карт нужны
и до регистрации (например, на шаге выбора карты в форме заявки перед регистрацией).
