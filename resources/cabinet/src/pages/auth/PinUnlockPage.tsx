import { useEffect, useRef, useState, type KeyboardEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useAuth } from '../../context/AuthContext';
import { extractErrorMessage } from '../../api/client';
import { BrandLogo } from '../../components/common/BrandLogo';
import { PinBoxes, PIN_LENGTH } from '../../components/pin/PinBoxes';
import { PinPad } from '../../components/pin/PinPad';

/**
 * Экран разблокировки ЛК по ПИН-коду на доверенном устройстве — рендерится вместо дашборда,
 * когда обычная Sanctum-сессия истекла/отсутствует, но AuthContext (bootstrap или обработчик
 * 401, см. AuthContext.tsx) определил, что на этом устройстве есть валидный токен доверия
 * (кука mojno_pin_device). В этот момент сессии ещё НЕТ — ни один защищённый эндпоинт ЛК не
 * может ничего отдать, поэтому данные физически не могут загрузиться раньше времени: экран
 * лишь ждёт верного ПИН-кода, который создаёт новую сессию (AuthContext::unlockPin()).
 *
 * Вёрстка — самостоятельная страница в стиле /login (как и LoginPage.tsx, не через общий
 * AuthShell), но вместо email/пароля сразу ПИН-боксы + клавиатура (как в PinSetupModal.tsx).
 */
export function PinUnlockPage() {
    const { maskedEmail, unlockPin, forgetDevice } = useAuth();
    const navigate = useNavigate();

    const [pin, setPin] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isLeaving, setIsLeaving] = useState(false);

    const surfaceRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        surfaceRef.current?.focus();
    }, []);

    useEffect(() => {
        if (pin.length !== PIN_LENGTH || isSubmitting) {
            return;
        }

        setIsSubmitting(true);
        setError(null);

        unlockPin(pin)
            .then(() => navigate('/', { replace: true }))
            .catch((submitError) => {
                // 401 — доверие устройству отозвано (например, после превышения числа попыток
                // на сервере) или его больше нет: остаётся только обычный вход по паролю.
                if (axios.isAxiosError(submitError) && submitError.response?.status === 401) {
                    setIsLeaving(true);
                    void forgetDevice().then(() => navigate('/login', { replace: true }));
                    return;
                }

                setError(extractErrorMessage(submitError, 'Неверный ПИН-код'));
                setPin('');
                setIsSubmitting(false);
            });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pin]);

    function appendDigit(digit: string) {
        if (isSubmitting || isLeaving || pin.length >= PIN_LENGTH) {
            return;
        }
        setError(null);
        setPin((prev) => prev + digit);
    }

    function removeDigit() {
        if (isSubmitting || isLeaving) {
            return;
        }
        setError(null);
        setPin((prev) => prev.slice(0, -1));
    }

    function handleKeyDown(event: KeyboardEvent<HTMLDivElement>) {
        if (/^[0-9]$/.test(event.key)) {
            appendDigit(event.key);
        } else if (event.key === 'Backspace') {
            removeDigit();
        }
    }

    async function handleUseAnotherAccount() {
        setIsLeaving(true);
        await forgetDevice();
        navigate('/login', { replace: true });
    }

    return (
        <div className="flex min-h-screen flex-col items-center bg-white px-4 py-10">
            <div className="mb-10">
                <a href="https://mojno.cc/">
                    <BrandLogo />
                </a>
            </div>

            <div className="flex w-full flex-1 items-center justify-center">
                <div className="auth-panel flex w-full max-w-[440px] flex-col items-center p-8 text-center sm:p-10">
                    <h1 className="mb-2 text-2xl font-extrabold tracking-tight text-ink">Введите ПИН-код</h1>
                    <p className="mb-[32px] text-sm text-muted">
                        {maskedEmail ? (
                            <>
                                Вход как <span className="font-semibold text-ink">{maskedEmail}</span>
                            </>
                        ) : (
                            'Быстрый вход в личный кабинет'
                        )}
                    </p>

                    <div ref={surfaceRef} tabIndex={-1} className="pin-input-surface" onKeyDown={handleKeyDown}>
                        <PinBoxes value={pin} hasError={!!error} />
                    </div>

                    {error && <p className="form-error-banner mt-5">{error}</p>}

                    <div className="mt-6">
                        <PinPad onDigit={appendDigit} onBackspace={removeDigit} disabled={isSubmitting || isLeaving} />
                    </div>

                    <button
                        type="button"
                        onClick={handleUseAnotherAccount}
                        disabled={isLeaving}
                        className="mt-[32px] text-sm font-semibold text-muted hover:text-orange-dark"
                    >
                        Войти по паролю
                    </button>
                </div>
            </div>
        </div>
    );
}
