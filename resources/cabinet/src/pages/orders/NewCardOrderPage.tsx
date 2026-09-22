import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { useEffect, useMemo, useRef, useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchCardProducts, fetchPaymentMethods } from '../../api/catalog';
import { extractErrorMessage } from '../../api/client';
import { issueOrder } from '../../api/orders';
import type { CardProduct, PaymentMethod } from '../../api/types';
import { CardChoiceGridSkeleton } from '../../components/common/Skeleton';
import { CardProductChoice } from '../../components/orders/CardProductChoice';
import { PaymentMethodOption } from '../../components/orders/PaymentMethodOption';
import { useTopupQuote } from '../../hooks/useTopupQuote';
import { formatRub } from '../../utils/format';

type AmountCurrency = 'USD' | 'RUB';
type OrderStep = 'select' | 'topup';

/** "5 000,50" / "50.5" -> 5000.5. Нечисловой ввод игнорируется. */
function parseAmount(value: string): number {
    const normalized = value.replace(/[^\d.,]/g, '').replace(',', '.');
    const parsed = parseFloat(normalized);

    return Number.isFinite(parsed) ? parsed : 0;
}

// Оформление заявки на выпуск новой карты: выбор карточного продукта, способа
// оплаты и суммы первого пополнения — реализует «Шаг 1» и «Шаг 2» из
// CARD_ORDER_AND_ISSUANCE_FLOW.md через OrderController::issue().
export function NewCardOrderPage() {
    const navigate = useNavigate();
    const idempotencyKey = useRef(crypto.randomUUID());

    const [products, setProducts] = useState<CardProduct[]>([]);
    const [methods, setMethods] = useState<PaymentMethod[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [loadError, setLoadError] = useState<string | null>(null);

    const [step, setStep] = useState<OrderStep>('select');
    const [selectedProductId, setSelectedProductId] = useState<number | null>(null);
    const [selectedMethodId, setSelectedMethodId] = useState<number | null>(null);
    const [currency, setCurrency] = useState<AmountCurrency>('USD');
    const [amount, setAmount] = useState('');
    const [submitError, setSubmitError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        Promise.all([fetchCardProducts(), fetchPaymentMethods()])
            .then(([loadedProducts, loadedMethods]) => {
                setProducts(loadedProducts);
                setMethods(loadedMethods);
                setSelectedMethodId(loadedMethods[0]?.id ?? null);
            })
            .catch((error) => setLoadError(extractErrorMessage(error, 'Не удалось загрузить каталог карт.')))
            .finally(() => setIsLoading(false));
    }, []);

    const selectedProduct = useMemo(
        () => products.find((product) => product.id === selectedProductId) ?? null,
        [products, selectedProductId],
    );

    const parsedAmount = useMemo(() => parseAmount(amount), [amount]);
    // topup_total_rub уже включает комиссию провайдера за пополнение (см. OrderController::quote()) —
    // цена самой карты к ней не относится, плюсуем отдельно.
    const { totalRub: topupTotalRub } = useTopupQuote({
        cardProductId: selectedProduct?.id ?? null,
        amount: parsedAmount,
        currency,
    });
    const totalRub = (selectedProduct ? Number(selectedProduct.price_rub) : 0) + (topupTotalRub ?? 0);

    async function handleSubmit(event: FormEvent) {
        event.preventDefault();
        setSubmitError(null);

        if (!selectedProduct || !selectedMethodId) {
            return;
        }

        const raw = parseAmount(amount);

        if (raw <= 0) {
            setSubmitError('Введите сумму пополнения.');
            return;
        }

        setIsSubmitting(true);

        try {
            const result = await issueOrder({
                card_product_id: selectedProduct.id,
                topup_amount: raw,
                topup_currency: currency,
                payment_method_id: selectedMethodId,
                idempotency_key: idempotencyKey.current,
            });

            if (result.payment_url) {
                window.location.href = result.payment_url;
                return;
            }

            navigate('/cards');
        } catch (error) {
            setSubmitError(extractErrorMessage(error));
        } finally {
            setIsSubmitting(false);
        }
    }

    if (isLoading) {
        return (
            <div className="flex flex-col gap-6">
                <h1 className="text-2xl font-extrabold tracking-tight text-ink">Оформление карты</h1>
                <CardChoiceGridSkeleton />
            </div>
        );
    }

    if (loadError || products.length === 0 || methods.length === 0) {
        return (
            <p className="form-error-banner">
                {loadError ?? 'Оформление карт временно недоступно: нет доступных продуктов или способов оплаты.'}
            </p>
        );
    }

    // Шаг 1: выбор карты (описание и преимущества — как на лендинге). Шаг 2:
    // способ оплаты, сумма пополнения и переход к оплате — переключение между
    // шагами так же, как в apply-форме на лендинге (простая смена блока с fade-in).
    return (
        <div className="flex flex-col gap-6">
            <h1 className="text-2xl font-extrabold tracking-tight text-ink">Оформление карты</h1>

            {step === 'select' && (
                <div className="grid grid-cols-1 gap-5 lg:grid-cols-3 fade-in-up">
                    {products.map((product) => (
                        <CardProductChoice
                            key={product.id}
                            product={product}
                            onSelect={() => {
                                setSelectedProductId(product.id);
                                setStep('topup');
                            }}
                        />
                    ))}
                </div>
            )}

            {step === 'topup' && selectedProduct && (
                <div className="apply-panel fade-in-up">
                    <div className="apply-form-wrap">
                        <button
                            type="button"
                            onClick={() => setStep('select')}
                            className="flex items-center gap-1.5 text-sm font-semibold text-muted transition hover:text-ink"
                        >
                            <ArrowLeftIcon className="h-4 w-4" />
                            Выбрать другую карту
                        </button>

                        <div className="apply-card-list mt-5">
                            <div className="apply-card-option is-active" style={{ cursor: 'default' }}>
                                <span className={`apply-card-thumb ${selectedProduct.skin ? '' : 'apply-card-thumb-white'}`}>
                                    {selectedProduct.skin && (
                                        <img src={selectedProduct.skin} alt={`Карта ${selectedProduct.name}`} loading="lazy" />
                                    )}
                                </span>
                                <span className="apply-card-info">
                                    <span className="apply-card-name">
                                        Карта {selectedProduct.name}
                                        <span className="apply-card-badge">{selectedProduct.currency}</span>
                                    </span>
                                    <span className="apply-card-price">{formatRub(Number(selectedProduct.price_rub))} за выпуск</span>
                                </span>
                            </div>
                        </div>

                        <form onSubmit={handleSubmit}>
                            <div className="apply-field">
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
                                <label>
                                    {currency === 'USD' ? 'Введите сколько зачислить на карту' : 'Введите сколько заплатить'}
                                </label>
                                <div className="apply-amount-row">
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
                                    <p className="apply-amount-total">К оплате: {formatRub(totalRub)}</p>
                                </div>
                                {selectedProduct && (
                                    <p className="apply-hint">
                                        Пополнение: от ${selectedProduct.topup_min_amount ?? '10'} до $
                                        {selectedProduct.topup_max_amount ?? '—'}
                                    </p>
                                )}
                            </div>

                            {submitError && <p className="form-error-banner mt-5">{submitError}</p>}

                            <button type="submit" className="btn btn-primary apply-submit" disabled={isSubmitting}>
                                {isSubmitting ? 'Оформляем…' : 'Оплатить и выпустить карту'}
                            </button>
                            <p className="apply-hint mt-3 text-center">
                                Оплата на защищённой странице банка. Карта пополнится в течение 3 минут после выпуска карты.
                            </p>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
