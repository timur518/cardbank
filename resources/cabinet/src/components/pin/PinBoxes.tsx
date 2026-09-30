const PIN_LENGTH = 4;

interface PinBoxesProps {
    value: string;
    hasError?: boolean;
}

/**
 * 4 прочерка для ввода ПИН-кода (PinSetupModal.tsx) — заполненная позиция анимированно
 * превращается в жирную точку (маскируем саму цифру, как в обычных банковских ПИН-экранах).
 */
export function PinBoxes({ value, hasError }: PinBoxesProps) {
    return (
        <div className={`pin-boxes${hasError ? ' pin-boxes-error' : ''}`}>
            {Array.from({ length: PIN_LENGTH }).map((_, index) => (
                <span key={index} className={`pin-dash${index < value.length ? ' pin-dash-filled' : ''}`} />
            ))}
        </div>
    );
}

export { PIN_LENGTH };
