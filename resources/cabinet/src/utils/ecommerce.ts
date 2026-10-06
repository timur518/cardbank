// Электронная коммерция Яндекс.Метрики (счётчик 113131748, контейнер данных
// window.dataLayer, формат идентичен Google Analytics Enhanced Ecommerce) —
// https://yandex.ru/support/metrica/ecommerce/data.html
//
// Воронка ЛК (CARD_ORDER_AND_ISSUANCE_FLOW.md) размечена так:
//   1. Открытие /cards/new (список карточных продуктов)   -> impressions (просмотр списка товаров)
//   2. Клик «Выбрать» по карте на шаге 1                  -> add (добавление в корзину)
//   3. Клик «Пополнить баланс» (открытие TopupModal)       -> add (добавление в корзину)
//   4. Успешная оплата (клиент вернулся с платёжного шлюза) -> purchase (покупка)
//
// Товар для «выпуск карты + пополнение» и «пополнение баланса» — виртуальный (у
// нас нет SKU), поэтому id/название формируются здесь же: ровно то, что просил
// пользователь — "Пополнение баланса" / "Выпуск карты {название} и пополнение".
// Сумма всегда в рублях (currencyCode: RUB), т.к. это валюта, в которой клиент
// реально платит (Income.amount/total_rub, см. OrderController).

declare global {
    interface Window {
        dataLayer?: unknown[];
    }
}

export interface EcommerceProduct {
    id: string;
    name: string;
    /** Цена за единицу в рублях. Необязательна — на шаге «добавления в корзину» сумма
     * может быть ещё не известна (пользователь не ввёл сумму пополнения). */
    price?: number;
    quantity?: number;
}

function pushEcommerce(payload: Record<string, unknown>): void {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        ecommerce: {
            currencyCode: 'RUB',
            ...payload,
        },
    });
}

/** Просмотр списка товаров — открытие /cards/new. */
export function trackProductListView(products: EcommerceProduct[]): void {
    pushEcommerce({
        impressions: products.map((product, index) => ({
            id: product.id,
            name: product.name,
            price: product.price,
            list: 'Оформление карты',
            position: index + 1,
        })),
    });
}

/** Добавление товара в корзину — выбор карты на шаге 1 или открытие формы пополнения. */
export function trackAddToCart(product: EcommerceProduct): void {
    pushEcommerce({
        add: {
            products: [
                {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: product.quantity ?? 1,
                },
            ],
        },
    });
}

/** Покупка — подтверждённая оплата (см. storePendingPurchase/consumePendingPurchase). */
function trackPurchase(product: EcommerceProduct, orderId: string): void {
    pushEcommerce({
        purchase: {
            actionField: { id: orderId },
            products: [
                {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: product.quantity ?? 1,
                },
            ],
        },
    });
}

const PENDING_PURCHASE_KEY = 'mojno_pending_purchase';

interface PendingPurchase {
    orderId: string;
    product: EcommerceProduct;
}

/**
 * Перед редиректом на страницу платёжного шлюза (window.location.href = payment_url)
 * сохраняет данные заказа, чтобы отправить событие «purchase» уже после возврата
 * клиента обратно в ЛК (на success_url способа оплаты) — сама оплата происходит на
 * внешнем сайте шлюза, где наш dataLayer недоступен. sessionStorage переживает
 * полный переход на другой домен и обратно (в рамках той же вкладки).
 */
export function storePendingPurchase(product: EcommerceProduct, orderId: string): void {
    try {
        sessionStorage.setItem(PENDING_PURCHASE_KEY, JSON.stringify({ orderId, product } satisfies PendingPurchase));
    } catch {
        // sessionStorage недоступен (приватный режим и т.п.) — событие purchase в этот раз
        // просто не будет отправлено, на оформление заказа это никак не влияет.
    }
}

/**
 * Вызывается один раз при старте ЛК (DashboardLayout). Если ранее был сохранён
 * незавершённый заказ — значит клиент успешно вернулся со страницы оплаты
 * (success_url открывается провайдером только при успешном платеже) — отправляет
 * «purchase» и забывает заказ, чтобы не отправить его повторно при следующих
 * перезагрузках/переходах по ЛК.
 */
export function flushPendingPurchase(): void {
    let raw: string | null;

    try {
        raw = sessionStorage.getItem(PENDING_PURCHASE_KEY);
    } catch {
        return;
    }

    if (!raw) {
        return;
    }

    sessionStorage.removeItem(PENDING_PURCHASE_KEY);

    try {
        const { orderId, product } = JSON.parse(raw) as PendingPurchase;
        trackPurchase(product, orderId);
    } catch {
        // Повреждённые данные в sessionStorage — ничего не отправляем.
    }
}
