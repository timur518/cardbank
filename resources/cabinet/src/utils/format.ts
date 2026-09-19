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
