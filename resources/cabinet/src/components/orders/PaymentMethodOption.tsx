import type { PaymentMethod } from '../../api/types';
import { formatRub } from '../../utils/format';

interface PaymentMethodOptionProps {
    method: PaymentMethod;
    selected: boolean;
    onSelect: () => void;
}

// СБП определяем по названию способа оплаты (в PaymentMethod нет отдельного типа
// для СБП — это тоже type=gateway, только с другим названием, задаётся в админке).
function isSbp(method: PaymentMethod): boolean {
    return method.name.toLowerCase().includes('сбп');
}

function PaymentMethodIcon({ method }: { method: PaymentMethod }) {
    if (method.type === 'card') {
        return (
            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
                <rect x="2.5" y="5" width="19" height="14" rx="2.5" />
                <path strokeLinecap="round" d="M2.5 9.5h19" />
                <path strokeLinecap="round" d="M6 15h4" />
            </svg>
        );
    }

    if (isSbp(method)) {
        return (
            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
                <rect x="3" y="3" width="7" height="7" rx="1.2" />
                <rect x="14" y="3" width="7" height="7" rx="1.2" />
                <rect x="3" y="14" width="7" height="7" rx="1.2" />
                <path strokeLinecap="round" d="M14 14h3m4 0h0M14 17.5h7M17.5 14v7" />
            </svg>
        );
    }

    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M3 10l9-6 9 6M4.5 9.5V19h15V9.5M9 19v-6h6v6" />
        </svg>
    );
}

// Один способ оплаты на шаге выпуска/пополнения карты. Описание — диапазон сумм
// и валюта из самого PaymentMethod (реальные данные, не захардкоженный текст).
export function PaymentMethodOption({ method, selected, onSelect }: PaymentMethodOptionProps) {
    return (
        <label className={`apply-pay-option ${selected ? 'is-active' : ''}`}>
            <input type="radio" name="pay_method" className="sr-only" checked={selected} onChange={onSelect} />
            <span className="apply-pay-icon" aria-hidden="true">
                <PaymentMethodIcon method={method} />
            </span>
            <span className="apply-pay-info">
                <span className="apply-pay-name">{method.name}</span>
                <span className="apply-pay-desc">
                    От {formatRub(Number(method.min_amount))} до {formatRub(Number(method.max_amount))}
                </span>
            </span>
        </label>
    );
}
