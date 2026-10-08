import { BuildingLibraryIcon, CreditCardIcon, QrCodeIcon } from '@heroicons/react/24/outline';
import type { PaymentMethod } from '../../api/types';

interface PaymentMethodOptionProps {
    method: PaymentMethod;
    selected: boolean;
    onSelect: () => void;
    // Бейдж рядом с названием (например, «BitBanker DEV» + «Без комиссии!») — см. PaymentMethodsList.
    badge?: string;
}

// СБП определяем по названию способа оплаты (в PaymentMethod нет отдельного типа
// для СБП — это тоже type=gateway, только с другим названием, задаётся в админке);
// BitBanker всегда оплата по СБП, определяется надёжнее по gateway_code.
function isSbp(method: PaymentMethod): boolean {
    return method.gateway_code === 'bitbanker' || method.name.toLowerCase().includes('сбп');
}

function PaymentMethodIcon({ method }: { method: PaymentMethod }) {
    if (method.type === 'card') {
        return <CreditCardIcon className="h-5 w-5" />;
    }

    if (isSbp(method)) {
        return <QrCodeIcon className="h-5 w-5" />;
    }

    return <BuildingLibraryIcon className="h-5 w-5" />;
}

// Один способ оплаты на шаге выпуска/пополнения карты. Без подписи с диапазоном сумм под названием.
export function PaymentMethodOption({ method, selected, onSelect, badge }: PaymentMethodOptionProps) {
    return (
        <label className={`apply-pay-option ${selected ? 'is-active' : ''}`}>
            <input type="radio" name="pay_method" className="sr-only" checked={selected} onChange={onSelect} />
            <span className="apply-pay-icon" aria-hidden="true">
                <PaymentMethodIcon method={method} />
            </span>
            <span className="apply-pay-info">
                <span className="apply-pay-name">
                    {method.name}
                    {badge && <span className="apply-pay-badge">{badge}</span>}
                </span>
            </span>
        </label>
    );
}
