import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { useEffect, useMemo, useRef, useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { fetchCardProducts, fetchPaymentMethods } from '../../api/catalog';
import { extractErrorMessage } from '../../api/client';
import { issueOrder } from '../../api/orders';
import type { CardProduct, PaymentMethod } from '../../api/types';
import { CardChoiceGridSkeleton } from '../../components/common/Skeleton';
import { InfoTooltip } from '../../components/common/InfoTooltip';
import { CardProductChoice } from '../../components/orders/CardProductChoice';
import { PaymentMethodOption } from '../../components/orders/PaymentMethodOption';
import { useTopupQuote } from '../../hooks/useTopupQuote';
import { formatRub } from '../../utils/format';

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
        currency: 'USD',
    });
    const totalRub = (selectedProduct ? Number(selectedProduct.price_rub) : 0) + (topupTotalRub ?? 0);

    const minAmount = Number(selectedProduct?.issue_min_amount ?? 10);
    const maxAmount = selectedProduct?.issue_max_amount ? Number(selectedProduct.issue_max_amount) : null;

    // Заготовленные суммы пополнения кнопками — минимум продукта плюс несколько типовых
    // сумм, но только те, что укладываются в [issue_min_amount; issue_max_amount] продукта
    // (и без дублей, если минимум совпадает с одной из типовых сумм).
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
                topup_currency: 'USD',
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
                <div className="flex flex-col gap-5 fade-in-up">
                    <button
                        type="button"
                        onClick={() => setStep('select')}
                        className="flex items-center gap-1.5 text-sm font-semibold text-muted transition hover:text-ink"
                    >
                        <ArrowLeftIcon className="h-4 w-4" />
                        Выбрать другую карту
                    </button>

                    <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                        {/* Блок 1: выбранная карта, сумма пополнения со встроенным USD, заготовленные
                            суммы кнопками, способ оплаты. */}
                        <div className="apply-panel">
                            <div className="apply-form-wrap">
                                <div>
                                    <h2 className="apply-section-title">Выбранная карта</h2>
                                    <div className="apply-selected-card">
                                        <span className={`apply-card-thumb ${selectedProduct.skin ? '' : 'apply-card-thumb-white'}`}>
                                            {selectedProduct.skin && (
                                                <img src={selectedProduct.skin} alt={`Карта ${selectedProduct.name}`} loading="lazy" />
                                            )}
                                        </span>
                                        <div className="min-w-0">
                                            <p className="apply-selected-card-name">
                                                Карта {selectedProduct.name}
                                                <span className="apply-card-badge">{selectedProduct.currency}</span>
                                            </p>
                                            {selectedProduct.description && (
                                                <p className="apply-selected-card-desc">{selectedProduct.description}</p>
                                            )}
                                            {selectedProduct.card_country_label && (
                                                <p className="apply-selected-card-meta">
                                                    {selectedProduct.card_country_flag_url && (
                                                        <img
                                                            src={selectedProduct.card_country_flag_url}
                                                            alt=""
                                                            className="h-4 w-4 shrink-0 rounded-full object-cover"
                                                        />
                                                    )}
                                                    Страна: {selectedProduct.card_country_label}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="apply-divider" />

                                <div>
                                    <h2 className="apply-section-title">Сколько зачислить на карту?</h2>
                                    <p className="apply-section-subtitle">Эти деньги будут на балансе карты для ваших оплат</p>

                                    <div className="apply-field !mt-4">
                                        <div className="apply-amount-input-group">
                                            <input
                                                type="text"
                                                inputMode="numeric"
                                                placeholder="50"
                                                value={amount}
                                                onChange={(event) => setAmount(event.target.value)}
                                            />
                                            <span className="apply-amount-suffix">USD</span>
                                        </div>
                                        <p className="apply-hint">
                                            Минимум {minAmount}$, максимум {maxAmount ?? '—'}$
                                        </p>
                                    </div>

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

                                <div className="apply-divider" />

                                <div>
                                    <div className="flex items-center gap-2">
                                        <h2 className="apply-section-title !mb-0">Способы оплаты</h2>
                                        <InfoTooltip text="Стоимость выпуска не зачисляется на баланс. Для покупок будет доступна введённая сумма пополнения." />
                                    </div>
                                    <div className="apply-pay-list mt-4">
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
                            </div>
                        </div>

                        {/* Блок 2: состав платежа и кнопка оплаты. */}
                        <div className="apply-panel">
                            <div className="apply-form-wrap">
                                <h2 className="apply-section-title">Состав платежа</h2>

                                <div className="apply-summary-row">
                                    <div>
                                        <p className="apply-summary-label">Выпуск карты {selectedProduct.name}</p>
                                        <p className="apply-summary-sub">Один раз при оформлении</p>
                                    </div>
                                    <p className="apply-summary-amount">{formatRub(Number(selectedProduct.price_rub))}</p>
                                </div>

                                <div className="apply-summary-row">
                                    <p className="apply-summary-label">Пополнение на {parsedAmount || 0}$</p>
                                    <p className="apply-summary-amount">
                                        {topupTotalRub !== null ? formatRub(topupTotalRub) : '—'}
                                    </p>
                                </div>

                                <div className="apply-divider" />

                                <div className="apply-summary-total-row">
                                    <span>Итого к оплате:</span>
                                    <span>{formatRub(totalRub)}</span>
                                </div>
                                <p className="apply-summary-balance">На балансе карты будет {parsedAmount || 0} $</p>

                                {submitError && <p className="form-error-banner mt-5">{submitError}</p>}

                                <button type="submit" className="btn btn-primary apply-submit" disabled={isSubmitting}>
                                    {isSubmitting ? 'Оформляем…' : 'Оплатить и выпустить карту'}
                                </button>
                                <p className="apply-hint mt-3 text-center">После оплаты начнётся автоматический выпуск карты</p>
                            </div>
                        </div>
                    </form>
                </div>
            )}
        </div>
    );
}
