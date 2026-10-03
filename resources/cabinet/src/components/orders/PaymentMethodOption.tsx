import { BuildingLibraryIcon, CreditCardIcon, QrCodeIcon } from '@heroicons/react/24/outline';
import type { PaymentMethod } from '../../api/types';

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
        return <CreditCardIcon className="h-5 w-5" />;
    }

    if (isSbp(method)) {
        return <QrCodeIcon className="h-5 w-5" />;
    }

    return <BuildingLibraryIcon className="h-5 w-5" />;
}

// Один способ оплаты на шаге выпуска/пополнения карты. Без подписи с диапазоном сумм под названием.
export function PaymentMethodOption({ method, selected, onSelect }: PaymentMethodOptionProps) {
    return (
        <label className={`apply-pay-option ${selected ? 'is-active' : ''}`}>
            <input type="radio" name="pay_method" className="sr-only" checked={selected} onChange={onSelect} />
            <span className="apply-pay-icon" aria-hidden="true">
                <PaymentMethodIcon method={method} />
            </span>
            <span className="apply-pay-info">
                <span className="apply-pay-name">{method.name}</span>
            </span>
        </label>
    );
}
