import { BackspaceIcon } from '@heroicons/react/24/outline';

interface PinPadProps {
    onDigit: (digit: string) => void;
    onBackspace: () => void;
}

const KEYS = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];

/**
 * Экранная цифровая клавиатура для ввода ПИН-кода на мобильных устройствах (PinSetupModal.tsx) —
 * скрыта на десктопе через CSS (@media (pointer: coarse) в index.css), там ввод идёт с физической
 * клавиатуры. На тач-устройствах намеренно не используется обычный <input> — чтобы не вызывать
 * штатную экранную клавиатуру ОС, ввод возможен только через эти кнопки.
 */
export function PinPad({ onDigit, onBackspace }: PinPadProps) {
    return (
        <div className="pin-pad">
            {KEYS.map((key) => (
                <button key={key} type="button" className="pin-pad-key" onClick={() => onDigit(key)}>
                    {key}
                </button>
            ))}
            <span />
            <button type="button" className="pin-pad-key" onClick={() => onDigit('0')}>
                0
            </button>
            <button type="button" className="pin-pad-key pin-pad-backspace" onClick={onBackspace} aria-label="Стереть цифру">
                <BackspaceIcon className="h-5 w-5" />
            </button>
        </div>
    );
}
