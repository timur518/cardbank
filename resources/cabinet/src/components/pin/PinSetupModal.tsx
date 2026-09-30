import axios from 'axios';
import { ArrowPathIcon, LockClosedIcon } from '@heroicons/react/24/outline';
import { useEffect, useRef, useState, type KeyboardEvent } from 'react';
import { extractErrorMessage, type ApiValidationError } from '../../api/client';
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

/**
 * Попап установки/смены 4-значного ПИН-кода — тот же .modal-overlay/.modal-sheet, что и у
 * «Пополнить карту» (см. Modal.tsx/TopupModal). Флоу: [только mode="change"] текущий ПИН →
 * новый ПИН → повтор нового ПИН → сохранение (спиннер) → успех (спиннер анимированно
 * превращается в градиентный кружок с замком).
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

    const surfaceRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (step !== 'saving' && step !== 'success') {
            surfaceRef.current?.focus();
        }
    }, [step]);

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
        const value = activeValue();
        if (value.length >= PIN_LENGTH) {
            return;
        }
        setError(null);
        setActiveValue(value + digit);
    }

    function removeDigit() {
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
        } else if (event.key === 'Enter') {
            handleNext();
        }
    }

    function handleNext() {
        if (activeValue().length !== PIN_LENGTH) {
            return;
        }

        if (step === 'current') {
            setStep('enter');
        } else if (step === 'enter') {
            setStep('confirm');
        } else if (step === 'confirm') {
            void submit();
        }
    }

    async function submit() {
        if (pinConfirmation !== pin) {
            setError('ПИН-коды не совпадают.');
            setPinConfirmation('');
            return;
        }

        setStep('saving');

        try {
            await setPin({
                current_pin: mode === 'change' ? currentPin : undefined,
                pin,
                pin_confirmation: pinConfirmation,
            });
            setStep('success');
        } catch (submitError) {
            const isCurrentPinError =
                axios.isAxiosError<ApiValidationError>(submitError) && !!submitError.response?.data?.errors?.current_pin;
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
        switch (step) {
            case 'current':
                return 'Введите текущий ПИН-код';
            case 'confirm':
                return 'Повторите ПИН-код';
            case 'enter':
                return mode === 'change' ? 'Придумайте новый ПИН-код' : 'Установите ПИН-код';
            default:
                return 'ПИН-код';
        }
    }

    return (
        <Modal title={stepTitle()} onClose={handleClose}>
            {(step === 'current' || step === 'enter' || step === 'confirm') && (
                <div className="flex flex-col items-center gap-5 py-2 text-center">
                    {step === 'enter' && mode === 'create' && (
                        <p className="text-sm text-muted">
                            Быстрый вход по ПИН-коду вместо пароля при каждом открытии приложения.
                        </p>
                    )}
                    {step === 'confirm' && <p className="text-sm text-muted">Повторите ПИН-код:</p>}

                    <div ref={surfaceRef} tabIndex={-1} className="pin-input-surface" onKeyDown={handleKeyDown}>
                        <PinBoxes value={activeValue()} hasError={!!error} />
                    </div>

                    {error && <p className="form-error-banner">{error}</p>}

                    <PinPad onDigit={appendDigit} onBackspace={removeDigit} />

                    <button
                        type="button"
                        className="btn btn-primary w-full justify-center"
                        disabled={activeValue().length !== PIN_LENGTH}
                        onClick={handleNext}
                    >
                        {step === 'confirm' ? 'Установить ПИН' : 'Далее'}
                    </button>
                </div>
            )}

            {(step === 'saving' || step === 'success') && (
                <div className="flex flex-col items-center gap-4 py-6 text-center">
                    <div className={`pin-status-circle${step === 'success' ? ' is-success' : ''}`}>
                        <ArrowPathIcon className="pin-status-icon pin-status-spinner h-8 w-8 animate-spin" />
                        <LockClosedIcon className="pin-status-icon pin-status-lock h-8 w-8" />
                    </div>
                    {step === 'success' && <p className="pin-status-text fade-in-up">ПИН-код установлен</p>}
                </div>
            )}
        </Modal>
    );
}
