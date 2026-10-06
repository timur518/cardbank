import { ChevronRightIcon } from '@heroicons/react/24/outline';
import { useEffect, useMemo, useRef, useState, type FormEvent } from 'react';
import { fetchPaymentMethods } from '../../api/catalog';
import { extractErrorMessage } from '../../api/client';
import { topupOrder } from '../../api/orders';
import type { CardDetail, PaymentMethod } from '../../api/types';
import { useTopupQuote } from '../../hooks/useTopupQuote';
import { trackAddToCart, storePendingPurchase } from '../../utils/ecommerce';
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
    const [amount, setAmount] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const parsedAmount = useMemo(() => parseAmount(amount), [amount]);
    const { totalRub } = useTopupQuote({ cardId: card.id, amount: parsedAmount, currency: 'USD' });

    const minAmount = Number(card.card_product.topup_min_amount ?? 10);
    const maxAmount = card.card_product.topup_max_amount ? Number(card.card_product.topup_max_amount) : null;

    // Заготовленные суммы пополнения кнопками — та же логика, что и в NewCardOrderPage.tsx,
    // но от topup_min/max_amount карточного продукта (не issue_min/max_amount — здесь карта уже выпущена).
    const amountPresets = useMemo(() => {
        const candidates = [
            { value: minAmount, label: `${minAmount}$ минимум` },
            { value: 50, label: '50$' },
            { value: 100, label: '100$' },
            { value: 300, label: '300$' },
        ];
        const seen = new Set<number>();

        return candidates.filter(({ value }) => {
            if (value < minAmount || (maxAmount !== null && value > maxAmount) || seen.has(value)) {
                return false;
            }
            seen.add(value);
            return true;
        });
    }, [minAmount, maxAmount]);

    useEffect(() => {
        fetchPaymentMethods()
            .then((loaded) => {
                setMethods(loaded);
                setSelectedMethodId(loaded[0]?.id ?? null);
            })
            .catch((err) => setError(extractErrorMessage(err, 'Не удалось загрузить способы оплаты.')))
            .finally(() => setIsLoading(false));
    }, []);

    // Эл.коммерция: модалка монтируется только при клике на «Пополнить
    // баланс» (CardDetailPage.tsx/BalancePanel.tsx) — этот момент и есть «добавление в корзину» —
    // сумма пополнения ещё не введена, поэтому без цены.
    useEffect(() => {
        trackAddToCart({ id: `topup-${card.id}`, name: 'Пополнение баланса' });
        // eslint-disable-next-line react-hooks/exhaustive-deps
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
                currency: 'USD',
                payment_method_id: selectedMethodId,
                idempotency_key: idempotencyKey.current,
            });

            if (result.payment_url) {
                // Эл.коммерция: событие purchase отправится после возврата клиента со
                // страницы оплаты (см. DashboardLayout.tsx / flushPendingPurchase()).
                storePendingPurchase(
                    { id: `topup-${card.id}`, name: 'Пополнение баланса', price: Number(result.total_rub) },
                    idempotencyKey.current,
                );
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
                            <label>Сумма для пополнения карты</label>
                            <div className="apply-amount-input-group">
                                <input
                                    type="text"
                                    inputMode="numeric"
                                    placeholder="50"
                                    value={amount}
                                    onChange={(event) => setAmount(event.target.value)}
                                />
                            </div>
                            {card.card_product.topup_min_amount && card.card_product.topup_max_amount && (
                                <p className="apply-hint">
                                    От ${card.card_product.topup_min_amount} до ${card.card_product.topup_max_amount}
                                </p>
                            )}

                            <div className="apply-amount-presets">
                                {amountPresets.map((preset) => (
                                    <button
                                        key={preset.value}
                                        type="button"
                                        className={`apply-preset-btn ${parsedAmount === preset.value ? 'is-active' : ''}`}
                                        onClick={() => setAmount(String(preset.value))}
                                    >
                                        {preset.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {error && <p className="form-error-banner mt-5">{error}</p>}

                    <button type="submit" className="btn btn-primary apply-submit" disabled={isSubmitting}>
                        {isSubmitting ? (
                            'Оформляем…'
                        ) : (
                            <>
                                {totalRub !== null ? `Оплатить • ${formatRub(totalRub)}` : 'Оплатить'}
                                <ChevronRightIcon className="h-4 w-4" />
                            </>
                        )}
                    </button>
                </form>
            )}
        </Modal>
    );
}
