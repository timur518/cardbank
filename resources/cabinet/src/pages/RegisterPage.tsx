import { useState, type FormEvent } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { AuthLayout } from '../components/AuthLayout';
import { FormField } from '../components/FormField';
import { extractErrorMessage } from '../api/client';
import { formatDateMask, formatPhoneMask } from '../utils/masks';
import type { RegisterPayload } from '../api/types';

const initialValues: RegisterPayload = {
    first_name: '',
    last_name: '',
    middle_name: '',
    phone: '',
    email: '',
    date_of_birth: '',
    password: '',
    password_confirmation: '',
    personal_data_consent: false,
    referral_code: '',
};

export function RegisterPage() {
    const { register } = useAuth();
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();

    const [values, setValues] = useState<RegisterPayload>(initialValues);
    const [error, setError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    function setField<K extends keyof RegisterPayload>(key: K, value: RegisterPayload[K]) {
        setValues((prev) => ({ ...prev, [key]: value }));
    }

    async function handleSubmit(event: FormEvent) {
        event.preventDefault();
        setError(null);

        if (!values.personal_data_consent) {
            setError('Нужно дать согласие на обработку персональных данных.');
            return;
        }

        setIsSubmitting(true);

        try {
            // UTM-метки из URL — молча прикладываем к заявке, отдельных полей в форме нет.
            await register({
                ...values,
                utm_source: searchParams.get('utm_source') ?? undefined,
                utm_medium: searchParams.get('utm_medium') ?? undefined,
                utm_campaign: searchParams.get('utm_campaign') ?? undefined,
                utm_content: searchParams.get('utm_content') ?? undefined,
            });
            navigate('/', { replace: true });
        } catch (submitError) {
            setError(extractErrorMessage(submitError, 'Не удалось зарегистрироваться. Проверьте введённые данные.'));
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <AuthLayout
            title="Регистрация"
            subtitle="Создайте аккаунт, чтобы оформить и пополнять карты онлайн."
            footer={
                <>
                    Уже есть аккаунт? <Link to="/login" className="font-bold text-orange-dark">Войти</Link>
                </>
            }
        >
            <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                {error ? <div className="form-error-banner">{error}</div> : null}

                <div className="grid grid-cols-2 gap-4">
                    <FormField
                        label="Имя"
                        name="first_name"
                        value={values.first_name}
                        onChange={(e) => setField('first_name', e.target.value)}
                        required
                    />
                    <FormField
                        label="Фамилия"
                        name="last_name"
                        value={values.last_name}
                        onChange={(e) => setField('last_name', e.target.value)}
                        required
                    />
                </div>

                <FormField
                    label="Отчество"
                    name="middle_name"
                    value={values.middle_name}
                    onChange={(e) => setField('middle_name', e.target.value)}
                />

                <div className="grid grid-cols-2 gap-4">
                    <FormField
                        label="Телефон"
                        name="phone"
                        type="tel"
                        placeholder="+7 (999) 123-45-67"
                        value={values.phone}
                        onChange={(e) => setField('phone', formatPhoneMask(e.target.value))}
                        required
                    />
                    <FormField
                        label="Дата рождения"
                        name="date_of_birth"
                        placeholder="дд.мм.гггг"
                        value={values.date_of_birth}
                        onChange={(e) => setField('date_of_birth', formatDateMask(e.target.value))}
                        required
                    />
                </div>

                <FormField
                    label="Email"
                    name="email"
                    type="email"
                    autoComplete="email"
                    value={values.email}
                    onChange={(e) => setField('email', e.target.value)}
                    required
                />

                <div className="grid grid-cols-2 gap-4">
                    <FormField
                        label="Пароль"
                        name="password"
                        type="password"
                        autoComplete="new-password"
                        value={values.password}
                        onChange={(e) => setField('password', e.target.value)}
                        required
                    />
                    <FormField
                        label="Повторите пароль"
                        name="password_confirmation"
                        type="password"
                        autoComplete="new-password"
                        value={values.password_confirmation}
                        onChange={(e) => setField('password_confirmation', e.target.value)}
                        required
                    />
                </div>

                <FormField
                    label="Реферальный код (необязательно)"
                    name="referral_code"
                    value={values.referral_code}
                    onChange={(e) => setField('referral_code', e.target.value)}
                />

                <label className="flex cursor-pointer items-start gap-3 text-sm text-muted">
                    <input
                        type="checkbox"
                        className="mt-1"
                        checked={values.personal_data_consent}
                        onChange={(e) => setField('personal_data_consent', e.target.checked)}
                    />
                    Согласен на обработку персональных данных
                </label>

                <button type="submit" className="btn btn-primary btn-block" disabled={isSubmitting}>
                    {isSubmitting ? 'Создаём аккаунт…' : 'Зарегистрироваться'}
                </button>
            </form>
        </AuthLayout>
    );
}
