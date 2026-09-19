import { useState, type FormEvent } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { AuthLayout } from '../components/AuthLayout';
import { FormField } from '../components/FormField';
import { extractErrorMessage } from '../api/client';

export function LoginPage() {
    const { login } = useAuth();
    const navigate = useNavigate();

    const [values, setValues] = useState({ login: '', password: '' });
    const [error, setError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    async function handleSubmit(event: FormEvent) {
        event.preventDefault();
        setError(null);
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

    return (
        <AuthLayout
            title="Вход в личный кабинет"
            subtitle="Введите телефон или email и пароль от аккаунта."
            footer={
                <>
                    Ещё нет аккаунта? <Link to="/register" className="font-bold text-orange-dark">Зарегистрироваться</Link>
                </>
            }
        >
            <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                {error ? <div className="form-error-banner">{error}</div> : null}

                <FormField
                    label="Телефон или email"
                    name="login"
                    autoComplete="username"
                    value={values.login}
                    onChange={(e) => setValues((prev) => ({ ...prev, login: e.target.value }))}
                    required
                />

                <FormField
                    label="Пароль"
                    name="password"
                    type="password"
                    autoComplete="current-password"
                    value={values.password}
                    onChange={(e) => setValues((prev) => ({ ...prev, password: e.target.value }))}
                    required
                />

                <div className="text-right text-sm">
                    <Link to="/forgot-password" className="font-semibold text-muted hover:text-orange-dark">
                        Забыли пароль?
                    </Link>
                </div>

                <button type="submit" className="btn btn-primary btn-block" disabled={isSubmitting}>
                    {isSubmitting ? 'Входим…' : 'Войти'}
                </button>
            </form>
        </AuthLayout>
    );
}
