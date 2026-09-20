import { useState, type FormEvent } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { API_ROOT, extractErrorMessage } from '../../api/client';
import { BrandLogo } from '../../components/common/BrandLogo';
import { FormField } from '../../components/common/FormField';

type LoginStep = 'identifier' | 'password';

// Фоновое видео справа — статический ассет бэкенда (тот же файл, что и на
// лендинге), путь строим от настроенного API_ROOT, а не хардкодим хост.
const heroVideoUrl = `${API_ROOT}/assets/images/herobg.mp4`;

/**
 * Вход — отдельный двухколоночный экран (не через общий AuthShell/AuthLayout,
 * как /register и /forgot-password, поэтому кросс-фейд AuthTransition между
 * /login и остальными auth-страницами больше не применяется — вёрстка входа
 * теперь принципиально другая):
 * слева — карточка входа фиксированной высоты 550px (лого + заголовок
 * сверху, форма по центру, ссылка на регистрацию снизу); справа — видео-
 * панель, которая визуально «выезжает» из-под карточки (ниже по z-index и
 * частично перекрыта её правым краем) с плавающим блоком быстрого входа
 * внизу. Видео-панель показывается только от 1024px (lg) и шире — на
 * мобильных остаётся только карточка входа, как раньше.
 *
 * Сам вход — в два шага (как у Google/Microsoft): сначала только
 * телефон/email, после клика по «Войти» форма динамично сменяется на ввод
 * пароля — реальная авторизация происходит только на втором шаге.
 * .fade-in-up обеспечивает плавное появление блока нового шага.
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
        <div className="flex min-h-screen items-center justify-center px-4 py-10">
            <div className="relative w-full max-w-[900px] lg:h-[550px]">
                {/* Карточка входа — поверх видео-панели (z-10), фиксированная высота 550px только от lg. */}
                <div className="auth-panel relative z-10 mx-auto flex w-full max-w-[440px] flex-col p-8 sm:p-10 lg:mx-0 lg:h-full">
                    <div>
                        <BrandLogo />
                        <h1 className="mt-6 text-2xl font-extrabold tracking-tight text-ink">Вход в личный кабинет</h1>
                    </div>

                    <div className="flex flex-1 flex-col justify-center py-6">
                        <p className="mb-5 text-sm text-muted">
                            {step === 'identifier' ? 'Введите телефон или email от аккаунта.' : 'Введите пароль от аккаунта.'}
                        </p>

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
                    </div>

                    <div className="text-center text-sm text-muted">
                        Ещё нет аккаунта? <Link to="/register" className="font-bold text-orange-dark">Зарегистрироваться</Link>
                    </div>
                </div>

                {/* Видео-панель — справа, абсолютно спозиционирована так, что её левая
                    часть уходит под карточку входа (z-0 против z-10 у карточки), создавая
                    эффект «выезжает из-под». */}
                <div className="absolute inset-y-0 right-0 z-0 hidden w-[520px] overflow-hidden rounded-3xl lg:block">
                    <video
                        className="h-full w-full object-cover object-right"
                        src={heroVideoUrl}
                        autoPlay
                        muted
                        loop
                        playsInline
                    />

                    <div className="absolute inset-x-6 bottom-6 flex flex-col gap-3 rounded-3xl bg-white p-4 shadow-lg">
                        <button type="button" className="btn rounded-full">
                            Войти через Яндекс
                        </button>
                        <button type="button" className="btn rounded-full">
                            Войти через VK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
