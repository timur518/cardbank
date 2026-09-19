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

/** Целое число рублей с разделителем разрядов: 9550.4 -> "9 550 ₽". Используется там, где копейки
 * не нужны (цена карты, итоговая сумма заказа «К оплате»), в отличие от formatMoney(). */
export function formatRub(amount: number): string {
    return `${Math.round(amount).toLocaleString('ru-RU')} ₽`;
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
