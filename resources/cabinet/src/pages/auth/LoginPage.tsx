import { useState, type FormEvent } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { AuthLayout } from '../../components/auth/AuthLayout';
import { FormField } from '../../components/common/FormField';
import { extractErrorMessage } from '../../api/client';

type LoginStep = 'identifier' | 'password';

/**
 * Вход в два шага (как у Google/Microsoft): сначала только телефон/email,
 * после клика по «Войти» экран динамично сменяется на ввод пароля — сама
 * авторизация происходит только на втором шаге. Плавность обеспечивают
 * .fade-in-up (появление блока нового шага) и уже существующий
 * AuthTransition в AuthShell — он следит через ResizeObserver за высотой
 * контента LoginPage и сам плавно подстраивает высоту панели при смене шага.
 */
export function LoginPage() {
    const { login } = useAuth();
    const navigate = useNavigate();

    const [step, setStep] = useState<LoginStep>('identifier');
    const [values, setValues] = useState({ login: '', password: '' });
    const [error, setError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    async function handleSubmit(event: FormEvent) {
        event.preventDefault();
        setError(null);

        if (step === 'identifier') {
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
        setStep('identifier');
    }

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
                    <div key="identifier-step" className="fade-in-up">
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
                    <div key="password-step" className="flex flex-col gap-5 fade-in-up">
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
