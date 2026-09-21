import { useEffect, useRef, useState } from 'react';
import { quoteTopup } from '../api/orders';

interface UseTopupQuoteParams {
    /** Ровно один из cardId/cardProductId — см. TopupQuoteRequest на бэкенде. */
    cardId?: string;
    cardProductId?: number | null;
    amount: number;
    currency: 'USD' | 'RUB';
}

const DEBOUNCE_MS = 350;

/**
 * Живой предрасчёт суммы к оплате (в рублях, с уже учтённой комиссией провайдера за
 * пополнение) по мере ввода суммы — для кнопки «Оплатить • {сумма} ₽» в TopupModal и
 * NewCardOrderPage. Дебаунсит ввод и игнорирует устаревшие ответы (если пользователь
 * успел изменить сумму, пока предыдущий запрос ещё летел) через счётчик последнего
 * отправленного запроса.
 */
export function useTopupQuote({ cardId, cardProductId, amount, currency }: UseTopupQuoteParams) {
    const [totalRub, setTotalRub] = useState<number | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const requestSeq = useRef(0);

    useEffect(() => {
        if (amount <= 0 || (!cardId && !cardProductId)) {
            setTotalRub(null);
            setIsLoading(false);
            return;
        }

        setIsLoading(true);
        const seq = ++requestSeq.current;

        const timer = window.setTimeout(() => {
            quoteTopup({ card_id: cardId, card_product_id: cardProductId ?? undefined, amount, currency })
                .then((result) => {
                    if (seq === requestSeq.current) {
                        setTotalRub(Number(result.topup_total_rub));
                    }
                })
                .catch(() => {
                    if (seq === requestSeq.current) {
                        setTotalRub(null);
                    }
                })
                .finally(() => {
                    if (seq === requestSeq.current) {
                        setIsLoading(false);
                    }
                });
        }, DEBOUNCE_MS);

        return () => window.clearTimeout(timer);
    }, [cardId, cardProductId, amount, currency]);

    return { totalRub, isLoading };
}
