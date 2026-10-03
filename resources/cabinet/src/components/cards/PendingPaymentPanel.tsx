import { ClockIcon } from '@heroicons/react/24/outline';
import type { PendingPayment } from '../../api/types';
import { formatMoney, formatRub } from '../../utils/format';

interface PendingPaymentPanelProps {
    pendingPayment: PendingPayment;
}

/**
 * Правая колонка страницы карты для статуса «Ожидает оплаты» (вместо RequisitesPanel,
 * которая для такой карты недоступна — реквизитов ещё нет). Предлагает завершить
 * оплату заказа на выпуск карты по той же ссылке, что и на первом шаге оформления.
 */
export function PendingPaymentPanel({ pendingPayment }: PendingPaymentPanelProps) {
    return (
        <div className="auth-panel p-6">
            <div className="mb-2 flex items-center gap-3">
                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f3f0ee] text-ink">
                    <ClockIcon className="h-5 w-5" />
                </span>
                <div>
                    <h2 className="text-base font-extrabold tracking-tight text-ink">
                        Завершите оплату для выпуска карты
                    </h2>
                </div>
            </div>

            <div className="flex flex-col gap-1 border-t border-border pt-4">
                <div className="flex items-center justify-between gap-4 py-1">
                    <span className="text-sm text-muted">Общая сумма</span>
                    <span className="font-mono text-base font-semibold text-ink">
                        {formatRub(Number(pendingPayment.amount_rub))}
                    </span>
                </div>
                <div className="flex items-center justify-between gap-4 py-1">
                    <span className="text-sm text-muted">На балансе карты будет</span>
                    <span className="font-mono text-base font-semibold text-ink">
                        {formatMoney(pendingPayment.topup_usd, 'USD')}
                    </span>
                </div>
            </div>

            <button
                type="button"
                className="btn btn-orange w-full mt-5"
                onClick={() => {
                    window.location.href = pendingPayment.payment_url;
                }}
            >
                Оплатить и выпустить
            </button>
        </div>
    );
}
