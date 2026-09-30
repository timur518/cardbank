import { BackspaceIcon } from '@heroicons/react/24/outline';

interface PinPadProps {
    onDigit: (digit: string) => void;
    onBackspace: () => void;
    disabled?: boolean;
}

const KEYS = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];

/**
 * Экранная цифровая клавиатура для ввода ПИН-кода (PinSetupModal.tsx) — одинакова и
 * на десктопе, и на мобильных (один и тот же попап без скрытия клавиатуры по устройству). На
 * тач-устройствах намеренно не используется обычный <input> — чтобы не вызывать штатную
 * экранную клавиатуру ОС, ввод там возможен только через эти кнопки; на десктопе работает
 * дополнительно и физическая клавиатура (onKeyDown в PinSetupModal.tsx).
 */
export function PinPad({ onDigit, onBackspace, disabled }: PinPadProps) {
    return (
        <div className="pin-pad">
            {KEYS.map((key) => (
                <button key={key} type="button" className="pin-pad-key" onClick={() => onDigit(key)} disabled={disabled}>
                    {key}
                </button>
            ))}
            <span />
            <button type="button" className="pin-pad-key" onClick={() => onDigit('0')} disabled={disabled}>
                0
            </button>
            <button
                type="button"
                className="pin-pad-key pin-pad-backspace"
                onClick={onBackspace}
                disabled={disabled}
                aria-label="Стереть цифру"
            >
                <BackspaceIcon className="h-5 w-5" />
            </button>
        </div>
    );
}
