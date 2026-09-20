import { useState, type FormEvent } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { AuthLayout } from '../../components/auth/AuthLayout';
import { FormField } from '../../components/common/FormField';
import { extractErrorMessage } from '../../api/client';

type LoginStep = 'identifier' | 'password';
type StepDirection = 'forward' | 'backward';

/**
 * Вход в два шага (как у Google/Microsoft): сначала только телефон/email,
 * после клика по «Войти» экран динамично сменяется на ввод пароля — сама
 * авторизация происходит только на втором шаге. Новый шаг не просто появляется, а
 * «приезжает» с проявлением с нужной стороны (slide-in-right/-left в index.css): вперёд
 * (логин -> пароль) — справа, назад («Изменить») — слева, как в обычных мастерах.
 * Высоту панели при смене шага (короткий шаг 1 -> длиннее шаг 2) плавно подстраивает
 * уже существующий AuthTransition в AuthShell — он следит через ResizeObserver за
 * высотой контента LoginPage.
 */
export function LoginPage() {
    const { login } = useAuth();
    const navigate = useNavigate();

    const [step, setStep] = useState<LoginStep>('identifier');
    const [direction, setDirection] = useState<StepDirection>('forward');
    const [values, setValues] = useState({ login: '', password: '' });
    const [error, setError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    async function handleSubmit(event: FormEvent) {
        event.preventDefault();
        setError(null);

        if (step === 'identifier') {
            setDirection('forward');
            setStep('password');
            return;
        }

        setIsSubmitting(true);

        try {
            await login(values);
            navigate('/', { replace: true });
        } catch (submitError) {
            setError(extractErrorMessage(submitError, 'Неверный телефон/email или пароль.'));
        } finally {
            setIsSubmitting(false);
        }
    }

    function handleBack() {
        setError(null);
        setValues((prev) => ({ ...prev, password: '' }));
        setDirection('backward');
        setStep('identifier');
    }

    const enterClass = direction === 'forward' ? 'slide-in-right' : 'slide-in-left';

    return (
        <AuthLayout
            title="Вход в личный кабинет"
            subtitle={step === 'identifier' ? 'Введите телефон или email от аккаунта.' : 'Введите пароль от аккаунта.'}
            footer={
                <>
                    Ещё нет аккаунта? <Link to="/register" className="font-bold text-orange-dark">Зарегистрироваться</Link>
                </>
            }
        >
            <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                {error ? <div className="form-error-banner">{error}</div> : null}

                {step === 'identifier' ? (
                    <div key={`identifier-${direction}`} className={enterClass}>
                        <FormField
                            label="Телефон или email"
                            name="login"
                            autoComplete="username"
                            autoFocus
                            value={values.login}
                            onChange={(e) => setValues((prev) => ({ ...prev, login: e.target.value }))}
                            required
                        />
                    </div>
                ) : (
                    <div key={`password-${direction}`} className={`flex flex-col gap-5 ${enterClass}`}>
                        <div className="flex items-center justify-between gap-3 text-sm">
                            <span className="truncate text-muted">
                                Вход как <span className="font-semibold text-ink">{values.login}</span>
                            </span>
                            <button
                                type="button"
                                onClick={handleBack}
                                className="shrink-0 font-semibold text-muted hover:text-orange-dark"
                            >
                                Изменить
                            </button>
                        </div>

                        <FormField
                            label="Пароль"
                            name="password"
                            type="password"
                            autoComplete="current-password"
                            autoFocus
                            value={values.password}
                            onChange={(e) => setValues((prev) => ({ ...prev, password: e.target.value }))}
                            required
                        />

                        <div className="text-right text-sm">
                            <Link to="/forgot-password" className="font-semibold text-muted hover:text-orange-dark">
                                Забыли пароль?
                            </Link>
                        </div>
                    </div>
                )}

                <button type="submit" className="btn btn-primary btn-block" disabled={isSubmitting}>
                    {isSubmitting ? 'Входим…' : 'Войти'}
                </button>
            </form>
        </AuthLayout>
    );
}
