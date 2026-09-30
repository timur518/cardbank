import axios from 'axios';
import { LockClosedIcon } from '@heroicons/react/24/outline';
import { useEffect, useRef, useState, type KeyboardEvent } from 'react';
import { extractErrorMessage, type ApiValidationError } from '../../api/client';
import { verifyPin } from '../../api/auth';
import { useAuth } from '../../context/AuthContext';
import { Modal } from '../common/Modal';
import { PinBoxes, PIN_LENGTH } from './PinBoxes';
import { PinPad } from './PinPad';

type Step = 'current' | 'enter' | 'confirm' | 'saving' | 'success';

interface PinSetupModalProps {
    /** 'create' — первичная установка (нет шага с текущим ПИН-кодом), 'change' — смена (нужен текущий). */
    mode: 'create' | 'change';
    onClose: () => void;
}

const SUCCESS_AUTO_CLOSE_MS = 1800;
const MISMATCH_RESET_DELAY_MS = 2000;

/**
 * Попап установки/смены 4-значного ПИН-кода — тот же .modal-overlay/.modal-sheet, что и у
 * «Пополнить карту» (см. Modal.tsx/TopupModal). Флоу полностью автоматический — кнопок
 * «Далее»/«Установить» нет, переход между шагами происходит сразу по вводу 4-й цифры:
 *   [mode="change"] текущий ПИН (сверяется сразу — POST /profile/pin/verify)
 *   → новый ПИН → повтор нового ПИН (совпал → автосохранение, не совпал → пауза с ошибкой
 *     и возврат на шаг ввода нового ПИН) → сохранение (лоадер) → успех (лоадер анимированно
 *     превращается в градиентный кружок с замком).
 *
 * Ввод цифр работает двумя способами одновременно: с физической клавиатуры (onKeyDown на
 * фокусируемом div — работает только на десктопе, так как без <input> виртуальная клавиатура
 * ОС на тач-устройствах не появляется) и через экранную PinPad (видна только на тач-устройствах,
 * см. @media (pointer: coarse) в index.css).
 */
export function PinSetupModal({ mode, onClose }: PinSetupModalProps) {
    const { setPin } = useAuth();

    const [step, setStep] = useState<Step>(mode === 'change' ? 'current' : 'enter');
    const [currentPin, setCurrentPin] = useState('');
    const [pin, setPinValue] = useState('');
    const [pinConfirmation, setPinConfirmation] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [isBusy, setIsBusy] = useState(false);

    const surfaceRef = useRef<HTMLDivElement>(null);
    const busyRef = useRef(false);

    useEffect(() => {
        if (step !== 'saving' && step !== 'success') {
            surfaceRef.current?.focus();
        }
    }, [step]);

    // Шаг 0 (только mode="change") — сверяем текущий ПИН сразу, не дожидаясь конца флоу.
    useEffect(() => {
        if (step !== 'current' || currentPin.length !== PIN_LENGTH || busyRef.current) {
            return;
        }

        busyRef.current = true;
        setIsBusy(true);
        setError(null);

        verifyPin(currentPin)
            .then(() => setStep('enter'))
            .catch((verifyError) => {
                setError(extractErrorMessage(verifyError, 'Неверный ПИН-код.'));
                setCurrentPin('');
            })
            .finally(() => {
                busyRef.current = false;
                setIsBusy(false);
            });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [step, currentPin]);

    // Шаг 1 — ввод нового ПИН-кода, по 4-й цифре сразу переходим к повтору.
    useEffect(() => {
        if (step === 'enter' && pin.length === PIN_LENGTH) {
            setError(null);
            setStep('confirm');
        }
    }, [step, pin]);

    // Шаг 2 — повтор нового ПИН-кода: совпал — сохраняем, не совпал — пауза и возврат на шаг 1.
    useEffect(() => {
        if (step !== 'confirm' || pinConfirmation.length !== PIN_LENGTH) {
            return;
        }

        if (pinConfirmation === pin) {
            void submit();
            return;
        }

        setError('ПИН-коды не совпадают.');
        const timer = window.setTimeout(() => {
            setPinValue('');
            setPinConfirmation('');
            setError(null);
            setStep('enter');
        }, MISMATCH_RESET_DELAY_MS);

        return () => window.clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [step, pinConfirmation]);

    useEffect(() => {
        if (step !== 'success') {
            return;
        }
        const timer = window.setTimeout(onClose, SUCCESS_AUTO_CLOSE_MS);
        return () => window.clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [step]);

    function activeValue(): string {
        if (step === 'current') return currentPin;
        if (step === 'enter') return pin;
        if (step === 'confirm') return pinConfirmation;
        return '';
    }

    function setActiveValue(value: string) {
        if (step === 'current') setCurrentPin(value);
        else if (step === 'enter') setPinValue(value);
        else if (step === 'confirm') setPinConfirmation(value);
    }

    function appendDigit(digit: string) {
        if (isBusy) {
            return;
        }
        const value = activeValue();
        if (value.length >= PIN_LENGTH) {
            return;
        }
        setError(null);
        setActiveValue(value + digit);
    }

    function removeDigit() {
        if (isBusy) {
            return;
        }
        setError(null);
        setActiveValue(activeValue().slice(0, -1));
    }

    function handleKeyDown(event: KeyboardEvent<HTMLDivElement>) {
        if (step === 'saving' || step === 'success') {
            return;
        }
        if (/^[0-9]$/.test(event.key)) {
            appendDigit(event.key);
        } else if (event.key === 'Backspace') {
            removeDigit();
        }
    }

    async function submit() {
        setStep('saving');

        try {
            await setPin({
                current_pin: mode === 'change' ? currentPin : undefined,
                pin,
                pin_confirmation: pinConfirmation,
            });
            setStep('success');
        } catch (submitError) {
            const isCurrentPinError = axios.isAxiosError<ApiValidationError>(submitError) && !!submitError.response?.data?.errors?.current_pin;
            setError(extractErrorMessage(submitError, 'Не удалось сохранить ПИН-код. Попробуйте ещё раз.'));
            setPinValue('');
            setPinConfirmation('');
            if (isCurrentPinError) {
                setCurrentPin('');
                setStep('current');
            } else {
                setStep('enter');
            }
        }
    }

    function handleClose() {
        if (step === 'saving') {
            return;
        }
        onClose();
    }

    function stepTitle(): string {
        if (step === 'current') {
            return 'Введите текущий ПИН-код';
        }
        return mode === 'change' ? 'Изменение ПИН-кода' : 'Установка ПИН-кода';
    }

    return (
        <Modal title={stepTitle()} onClose={handleClose}>
            {(step === 'current' || step === 'enter' || step === 'confirm') && (
                <div className="flex flex-col items-center gap-5 py-2 text-center">
                    {step === 'enter' && mode === 'create' && (
                        <p className="text-sm text-muted">
                            Быстрый вход по ПИН-коду
                            <br />
                            вместо ввода пароля при каждом входе
                        </p>
                    )}
                    {step === 'confirm' && <p className="text-sm text-muted">Повторите ПИН-код:</p>}

                    <div ref={surfaceRef} tabIndex={-1} className="pin-input-surface" onKeyDown={handleKeyDown}>
                        <PinBoxes value={activeValue()} hasError={!!error} />
                    </div>

                    {error && <p className="form-error-banner">{error}</p>}

                    <PinPad onDigit={appendDigit} onBackspace={removeDigit} disabled={isBusy} />
                </div>
            )}

            {(step === 'saving' || step === 'success') && (
                <div className="flex flex-col items-center gap-4 py-6 text-center">
                    <div className={`pin-status-circle${step === 'success' ? ' is-success' : ''}`}>
                        <span className="pin-status-ring" />
                        <LockClosedIcon className="pin-status-lock h-8 w-8" />
                    </div>
                    {step === 'success' && <p className="pin-status-text fade-in-up">ПИН-код установлен</p>}
                </div>
            )}
        </Modal>
    );
}
