import { useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { AuthLayout } from '../../components/auth/AuthLayout';
import { FormField } from '../../components/common/FormField';
import { forgotPassword } from '../../api/auth';
import { extractErrorMessage } from '../../api/client';

export function ForgotPasswordPage() {
    const [login, setLogin] = useState('');
    const [message, setMessage] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    async function handleSubmit(event: FormEvent) {
        event.preventDefault();
        setError(null);
        setIsSubmitting(true);

        try {
            const response = await forgotPassword(login);
            setMessage(response);
        } catch (submitError) {
            setError(extractErrorMessage(submitError));
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <AuthLayout
            title="Восстановление пароля"
            subtitle="Укажите телефон или email — новый пароль придёт на почту."
            footer={
                <Link to="/login" className="font-bold text-orange-dark">
                    Вернуться ко входу
                </Link>
            }
        >
            {message ? (
                <p className="text-sm text-muted">{message}</p>
            ) : (
                <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                    {error ? <div className="form-error-banner">{error}</div> : null}

                    <FormField
                        label="Телефон или email"
                        name="login"
                        value={login}
                        onChange={(e) => setLogin(e.target.value)}
                        required
                    />

                    <button type="submit" className="btn btn-primary btn-block" disabled={isSubmitting}>
                        {isSubmitting ? 'Отправляем…' : 'Восстановить пароль'}
                    </button>
                </form>
            )}
        </AuthLayout>
    );
}
