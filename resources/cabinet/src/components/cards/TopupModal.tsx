import { useEffect, useMemo, useRef, useState, type FormEvent } from 'react';
import { fetchPaymentMethods } from '../../api/catalog';
import { extractErrorMessage } from '../../api/client';
import { topupOrder } from '../../api/orders';
import type { CardDetail, PaymentMethod } from '../../api/types';
import { useTopupQuote } from '../../hooks/useTopupQuote';
import { formatRub } from '../../utils/format';
import { Modal } from '../common/Modal';
import { PaymentMethodsSkeleton } from '../common/Skeleton';
import { PaymentMethodOption } from '../orders/PaymentMethodOption';

interface TopupModalProps {
    card: CardDetail;
    onClose: () => void;
}

/** "5 000,50" / "50.5" -> 5000.5. Нечисловой ввод игнорируется. */
function parseAmount(value: string): number {
    const normalized = value.replace(/[^\d.,]/g, '').replace(',', '.');
    const parsed = parseFloat(normalized);

    return Number.isFinite(parsed) ? parsed : 0;
}

// Модальное окно пополнения уже выпущенной активной карты — кнопка «+ Пополнить
// карту» на странице информации о карте. Отдельная от NewCardOrderPage форма:
// здесь карта уже известна и не выбирается, нужны только способ оплаты и сумма.
export function TopupModal({ card, onClose }: TopupModalProps) {
    const idempotencyKey = useRef(crypto.randomUUID());

    const [methods, setMethods] = useState<PaymentMethod[]>([]);
    const [selectedMethodId, setSelectedMethodId] = useState<number | null>(null);
    const [currency, setCurrency] = useState<'USD' | 'RUB'>('USD');
    const [amount, setAmount] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const parsedAmount = useMemo(() => parseAmount(amount), [amount]);
    const { totalRub } = useTopupQuote({ cardId: card.id, amount: parsedAmount, currency });

    useEffect(() => {
        fetchPaymentMethods()
            .then((loaded) => {
                setMethods(loaded);
                setSelectedMethodId(loaded[0]?.id ?? null);
            })
            .catch((err) => setError(extractErrorMessage(err, 'Не удалось загрузить способы оплаты.')))
            .finally(() => setIsLoading(false));
    }, []);

    async function handleSubmit(event: FormEvent) {
        event.preventDefault();
        setError(null);

        if (!selectedMethodId) {
            return;
        }

        const raw = parseAmount(amount);

        if (raw <= 0) {
            setError('Введите сумму пополнения.');
            return;
        }

        setIsSubmitting(true);

        try {
            const result = await topupOrder({
                card_id: card.id,
                amount: raw,
                currency,
                payment_method_id: selectedMethodId,
                idempotency_key: idempotencyKey.current,
            });

            if (result.payment_url) {
                window.location.href = result.payment_url;
                return;
            }

            onClose();
        } catch (err) {
            setError(extractErrorMessage(err));
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <Modal title="Пополнить карту" onClose={onClose}>
            {isLoading ? (
                <PaymentMethodsSkeleton />
            ) : (
                <form onSubmit={handleSubmit} className="fade-in-up">
                        <div className="apply-field !mt-0">
                            <label>Способ оплаты</label>
                            <div className="apply-pay-list">
                                {methods.map((method) => (
                                    <PaymentMethodOption
                                        key={method.id}
                                        method={method}
                                        selected={method.id === selectedMethodId}
                                        onSelect={() => setSelectedMethodId(method.id)}
                                    />
                                ))}
                            </div>
                        </div>

                        <div className="apply-field">
                            <label>Сумма пополнения</label>
                            <div className="apply-amount-input-group">
                                <div className="apply-currency-toggle">
                                    <button
                                        type="button"
                                        className={currency === 'RUB' ? 'is-active' : ''}
                                        onClick={() => setCurrency('RUB')}
                                    >
                                        ₽
                                    </button>
                                    <button
                                        type="button"
                                        className={currency === 'USD' ? 'is-active' : ''}
                                        onClick={() => setCurrency('USD')}
                                    >
                                        $
                                    </button>
                                </div>
                                <input
                                    type="text"
                                    inputMode="numeric"
                                    placeholder={currency === 'USD' ? '50' : '5 000'}
                                    value={amount}
                                    onChange={(event) => setAmount(event.target.value)}
                                />
                            </div>
                            {card.card_product.topup_min_amount && card.card_product.topup_max_amount && (
                                <p className="apply-hint">
                                    От ${card.card_product.topup_min_amount} до ${card.card_product.topup_max_amount}
                                </p>
                            )}
                        </div>

                        {error && <p className="form-error-banner mt-5">{error}</p>}

                    <button type="submit" className="btn btn-primary apply-submit" disabled={isSubmitting}>
                        {isSubmitting
                            ? 'Оформляем…'
                            : totalRub !== null
                              ? `Оплатить • ${formatRub(totalRub)}`
                              : 'Оплатить'}
                    </button>
                </form>
            )}
        </Modal>
    );
}
