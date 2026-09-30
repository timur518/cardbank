const PIN_LENGTH = 4;

interface PinBoxesProps {
    value: string;
    hasError?: boolean;
}

/**
 * 4 квадратика для ввода ПИН-кода (PinSetupModal.tsx) — заполненная позиция показывает точку
 * (маскируем сами цифры, как в обычных банковских ПИН-экранах), а не введённое значение.
 */
export function PinBoxes({ value, hasError }: PinBoxesProps) {
    return (
        <div className={`pin-boxes${hasError ? ' pin-boxes-error' : ''}`}>
            {Array.from({ length: PIN_LENGTH }).map((_, index) => (
                <div key={index} className={`pin-box${index < value.length ? ' pin-box-filled' : ''}`}>
                    {index < value.length && <span className="pin-box-dot" />}
                </div>
            ))}
        </div>
    );
}

export { PIN_LENGTH };
