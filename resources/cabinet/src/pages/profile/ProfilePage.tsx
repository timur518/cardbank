import { useState, type FormEvent } from 'react';
import { updatePassword } from '../../api/auth';
import { extractErrorMessage } from '../../api/client';
import { FormField } from '../../components/common/FormField';
import { useAuth } from '../../context/AuthContext';
import { formatDateMask, formatPhoneMask, isoDateToDisplay, joinFio, splitFio, transliterateFio } from '../../utils/masks';

// Страница профиля из двух независимых блоков: «Мои данные» (ФИО/телефон/дата
// рождения — PATCH /profile) и «Безопасность» (смена пароля — POST /profile/password).
// У каждого блока своя форма, своё состояние отправки и свои сообщения об
// успехе/ошибке — сохранение одного не затрагивает другой.
export function ProfilePage() {
    return (
        <div className="flex flex-col gap-6">
            <h1 className="text-2xl font-extrabold tracking-tight text-ink">Профиль</h1>

            <ProfileDataSection />
            <SecuritySection />
        </div>
    );
}

interface ProfileFormValues {
    fio: string;
    phone: string;
    date_of_birth: string;
}

function ProfileDataSection() {
    const { profile, updateProfile } = useAuth();

    const [values, setValues] = useState<ProfileFormValues>(() => ({
        fio: profile ? joinFio(profile) : '',
        phone: profile?.phone ?? '',
        date_of_birth: isoDateToDisplay(profile?.date_of_birth ?? null),
    }));
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);

    function setField<K extends keyof ProfileFormValues>(key: K, value: ProfileFormValues[K]) {
        setValues((prev) => ({ ...prev, [key]: value }));
        setSuccess(false);
    }

    async function handleSubmit(event: FormEvent) {
        event.preventDefault();
        setError(null);
        setSuccess(false);

        const { last_name, first_name, middle_name } = splitFio(values.fio);

        if (!last_name || !first_name) {
            setError('Укажите фамилию и имя в поле ФИО.');
            return;
        }

        setIsSubmitting(true);

        try {
            await updateProfile({
                first_name,
                last_name,
                middle_name: middle_name || undefined,
                phone: values.phone,
                date_of_birth: values.date_of_birth,
            });
            setSuccess(true);
        } catch (submitError) {
            setError(extractErrorMessage(submitError, 'Не удалось сохранить изменения. Проверьте введённые данные.'));
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <section className="auth-panel p-6">
            <div className="mb-5 flex items-center gap-3">
                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f3f0ee] text-ink">
                    <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
                        <circle cx="12" cy="8" r="3.5" />
                        <path strokeLinecap="round" d="M4.5 20c1.4-4 4.2-6 7.5-6s6.1 2 7.5 6" />
                    </svg>
                </span>
                <div>
                    <h2 className="text-base font-extrabold tracking-tight text-ink">Мои данные</h2>
                    <p className="text-xs text-muted">ФИО, телефон и дата рождения</p>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                {error && <div className="form-error-banner">{error}</div>}
                {success && <div className="form-success-banner">Изменения сохранены.</div>}

                <FormField
                    label="ФИО"
                    name="fio"
                    placeholder="Халяпов Тимур Рамилевич"
                    autoComplete="name"
                    value={values.fio}
                    onChange={(e) => setField('fio', transliterateFio(e.target.value))}
                    required
                />

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
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

                <FormField label="Email" name="email" value={profile?.email ?? ''} disabled />

                <button type="submit" className="btn btn-primary self-start" disabled={isSubmitting}>
                    {isSubmitting ? 'Сохраняем…' : 'Сохранить изменения'}
                </button>
            </form>
        </section>
    );
}

interface PasswordFormValues {
    current_password: string;
    password: string;
    password_confirmation: string;
}

const initialPasswordValues: PasswordFormValues = {
    current_password: '',
    password: '',
    password_confirmation: '',
};

function SecuritySection() {
    const [values, setValues] = useState<PasswordFormValues>(initialPasswordValues);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);

    function setField<K extends keyof PasswordFormValues>(key: K, value: PasswordFormValues[K]) {
        setValues((prev) => ({ ...prev, [key]: value }));
        setSuccess(false);
    }

    async function handleSubmit(event: FormEvent) {
        event.preventDefault();
        setError(null);
        setSuccess(false);
        setIsSubmitting(true);

        try {
            await updatePassword(values);
            setValues(initialPasswordValues);
            setSuccess(true);
        } catch (submitError) {
            setError(extractErrorMessage(submitError, 'Не удалось изменить пароль. Проверьте введённые данные.'));
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <section className="auth-panel p-6">
            <div className="mb-5 flex items-center gap-3">
                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f3f0ee] text-ink">
                    <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
                        <rect x="5" y="10.5" width="14" height="9.5" rx="2.2" />
                        <path strokeLinecap="round" d="M8 10.5V7.5a4 4 0 0 1 8 0v3" />
                    </svg>
                </span>
                <div>
                    <h2 className="text-base font-extrabold tracking-tight text-ink">Безопасность</h2>
                    <p className="text-xs text-muted">Смена пароля от личного кабинета</p>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                {error && <div className="form-error-banner">{error}</div>}
                {success && <div className="form-success-banner">Пароль изменён.</div>}

                <FormField
                    label="Текущий пароль"
                    name="current_password"
                    type="password"
                    autoComplete="current-password"
                    value={values.current_password}
                    onChange={(e) => setField('current_password', e.target.value)}
                    required
                />

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <FormField
                        label="Новый пароль"
                        name="password"
                        type="password"
                        autoComplete="new-password"
                        value={values.password}
                        onChange={(e) => setField('password', e.target.value)}
                        required
                    />
                    <FormField
                        label="Повторите новый пароль"
                        name="password_confirmation"
                        type="password"
                        autoComplete="new-password"
                        value={values.password_confirmation}
                        onChange={(e) => setField('password_confirmation', e.target.value)}
                        required
                    />
                </div>

                <button type="submit" className="btn btn-primary self-start" disabled={isSubmitting}>
                    {isSubmitting ? 'Меняем пароль…' : 'Изменить пароль'}
                </button>
            </form>
        </section>
    );
}
