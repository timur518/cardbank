const CURRENCY_SYMBOLS: Record<string, string> = {
    USD: '$',
    EUR: '€',
    RUB: '₽',
    GBP: '£',
};

/** "124.55" + "USD" -> "124.55 $". Неизвестная валюта выводится как есть. */
export function formatMoney(amount: string | number, currency: string): string {
    const value = Number(amount).toFixed(2);
    const symbol = CURRENCY_SYMBOLS[currency] ?? currency;

    return `${value} ${symbol}`;
}

/**
 * Большой баланс карты на странице одной карты: символ перед числом и запятая в
 * качестве разделителя дробной части ("$10,00"), в отличие от formatMoney() ("10.00 $"),
 * которая используется в таблицах/списках.
 */
export function formatBalanceHero(amount: string | number, currency: string): string {
    const value = Number(amount).toFixed(2).replace('.', ',');

    if (currency === 'RUB') {
        return `${value} ₽`;
    }

    const symbol = CURRENCY_SYMBOLS[currency] ?? currency;

    return `${symbol}${value}`;
}

/** Целое число рублей с разделителем разрядов: 9550.4 -> "9 550 ₽". Используется там, где копейки
 * не нужны (цена карты, итоговая сумма заказа «К оплате»), в отличие от formatMoney(). */
export function formatRub(amount: number): string {
    return `${Math.round(amount).toLocaleString('ru-RU')} ₽`;
}

/** Первый день текущего месяца в формате YYYY-MM-DD — для фильтра date_from при запросе
 * трат за месяц (страница карты и список карт). */
export function startOfMonth(): string {
    const date = new Date();
    date.setDate(1);

    return date.toISOString().slice(0, 10);
}

/**
 * Сумма для «Потрачено в этом месяце» из выборки транзакций (ожидается выборка с
 * type ∈ {purchase, decline}, см. fetchCardTransactions(..., { type: ['purchase', 'decline'] })):
 * - purchase со status=success (списанные покупки) И status=pending (авторизационный
 *   холд у CardsPro ещё в обработке, но уже удерживает средства на карте — неучёт таких
 *   операций занижал счётчик на сумму всех висящих холдов);
 * - decline — сама покупка отклонена и её сумма (`amount`) денег с карты не списывает; реально
 *   списывается только комиссия за неуспешную попытку, если CardsPro её прислал — она приходит
 *   отдельным полем `decline_fee` (null, если комиссии не было). Берём именно её, а не `amount`
 *   (в `amount` decline-записи зашит ещё и сумма самой отклонённой покупки, которая не списана).
 */
export function sumSuccessfulPurchases(
    transactions: { type: string; status: string; amount: string | number; decline_fee?: string | number | null }[],
): number {
    return transactions.reduce((sum, tx) => {
        if (tx.type === 'decline') {
            return sum + Math.abs(Number(tx.decline_fee ?? 0));
        }

        if (tx.type === 'purchase' && (tx.status === 'success' || tx.status === 'pending')) {
            return sum + Math.abs(Number(tx.amount));
        }

        return sum;
    }, 0);
}

/** "2026-09-10T12:00:00Z" -> "10.09.2026, 15:00". */
export function formatDateTime(iso: string): string {
    return new Date(iso).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
