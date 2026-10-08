import { QrCodeIcon } from '@heroicons/react/24/outline';

export type BitbankerTileState = 1 | 2 | 3 | '3a';

interface BitbankerTileProps {
    state: BitbankerTileState;
    onAction: () => void;
    isActionLoading?: boolean;
    // Название из админки (PaymentMethod.name, см. BitbankerStatus.method_name) — фолбэк на
    // случай, пока он ещё не загрузился.
    methodName?: string | null;
}

export const BITBANKER_BADGE = 'Без комиссии!';

/**
 * Некликабельная плитка BitBanker в списке способов оплаты (см. PaymentMethodsList) —
 * показывается первым элементом списка, пока BitBanker ещё не появился в
 * GET /v1/payment-methods (состояния 1-3a из BITBANKER_INTEGRATION_PLAN.md раздел 10.2).
 * Как только способ оплаты становится доступен — на его месте рендерится обычная
 * выбираемая PaymentMethodOption с тем же бейджем (состояние 4).
 */
export function BitbankerTile({ state, onAction, isActionLoading, methodName }: BitbankerTileProps) {
    return (
        <div className="apply-pay-option apply-pay-option-static">
            <span className="apply-pay-icon" aria-hidden="true">
                <QrCodeIcon className="h-5 w-5" />
            </span>
            <span className="apply-pay-info">
                <span className="apply-pay-name">
                    {methodName || 'BitBanker (СБП)'}
                    <span className="apply-pay-badge">{BITBANKER_BADGE}</span>
                </span>
                {state === 3 && <span className="apply-pay-desc">Заявка на рассмотрении</span>}
                {state === '3a' && (
                    <span className="apply-pay-desc">Пополнение через BitBanker недоступно, обратитесь в поддержку</span>
                )}
            </span>
            {(state === 1 || state === 2) && (
                <button type="button" className="btn" onClick={onAction} disabled={isActionLoading}>
                    {state === 1 ? (isActionLoading ? 'Открываем…' : 'Пройти верификацию') : 'Принять оферту'}
                </button>
            )}
        </div>
    );
}
