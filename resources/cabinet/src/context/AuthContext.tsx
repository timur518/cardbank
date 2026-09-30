import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState, type ReactNode } from 'react';
import * as authApi from '../api/auth';
import { setUnauthorizedHandler } from '../api/client';
import type { LoginPayload, Profile, RegisterPayload, SetPinPayload, UpdateProfilePayload } from '../api/types';

// 'locked' — сессия отсутствует (не было или истекла), но на этом устройстве есть доверенный
// ПИН-токен (см. deviceStatus()) — вместо /login показываем PinUnlockPage.tsx.
type AuthStatus = 'checking' | 'guest' | 'locked' | 'authenticated';

interface AuthContextValue {
    status: AuthStatus;
    profile: Profile | null;
    // Маскированный email доверенного устройства ("i***@example.com") — есть только пока status === 'locked'.
    maskedEmail: string | null;
    login: (payload: LoginPayload) => Promise<void>;
    register: (payload: RegisterPayload) => Promise<void>;
    logout: () => Promise<void>;
    updateProfile: (payload: UpdateProfilePayload) => Promise<void>;
    refreshProfile: () => Promise<void>;
    markPwaInstalled: () => Promise<void>;
    setPin: (payload: SetPinPayload) => Promise<void>;
    unlockPin: (pin: string) => Promise<void>;
    forgetDevice: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [status, setStatus] = useState<AuthStatus>('checking');
    const [profile, setProfile] = useState<Profile | null>(null);
    const [maskedEmail, setMaskedEmail] = useState<string | null>(null);

    // Не даёт обработчику 401 запускать повторную проверку device-status параллельно для
    // нескольких одновременных запросов, упавших с 401 почти одновременно (например, если
    // дашборд одновременно грузит карты/транзакции/уведомления) — сбрасывается, как только
    // пользователь снова аутентифицирован (см. эффект ниже).
    const sessionExpiryHandledRef = useRef(false);

    useEffect(() => {
        if (status === 'authenticated') {
            sessionExpiryHandledRef.current = false;
        }
    }, [status]);

    // Единая точка входа для двух случаев истечения сессии: холодная загрузка SPA (см. эффект
    // бутстрапа ниже) и истечение сессии прямо во время открытой вкладки (см. регистрацию
    // setUnauthorizedHandler ниже) — решает, показывать ли экран ввода ПИН-кода (/unlock) вместо
    // обычного /login, не давая при этом дашборду продолжать грузить данные личного кабинета.
    const resolveUnauthenticatedState = useCallback(async () => {
        try {
            const result = await authApi.deviceStatus();
            if (result.trusted) {
                setMaskedEmail(result.masked_email ?? null);
                setStatus('locked');
                return;
            }
        } catch {
            // device-status недоступен — считаем устройство недоверенным, как и при trusted: false.
        }
        setMaskedEmail(null);
        setStatus('guest');
    }, []);

    // При первой загрузке SPA пытаемся восстановить сессию (кука Sanctum могла
    // остаться от предыдущего визита) — GET /profile вернёт 401, если её нет.
    useEffect(() => {
        authApi
            .fetchProfile()
            .then((data) => {
                setProfile(data);
                setStatus('authenticated');
            })
            .catch(() => {
                void resolveUnauthenticatedState();
            });
    }, [resolveUnauthenticatedState]);

    // Ловит 401 от любого защищённого эндпоинта (карты/транзакции/профиль и т.д.), пришедший
    // прямо во время работы с открытой вкладкой (сессия истекла по SESSION_LIFETIME) — переводит
    // в 'locked'/'guest' тем же путём, что и холодный бутстрап, чтобы данные ЛК не продолжали
    // отображаться/грузиться после истечения сессии.
    useEffect(() => {
        setUnauthorizedHandler(() => {
            if (sessionExpiryHandledRef.current) {
                return;
            }
            sessionExpiryHandledRef.current = true;
            setProfile(null);
            void resolveUnauthenticatedState();
        });

        return () => setUnauthorizedHandler(null);
    }, [resolveUnauthenticatedState]);

    const login = useCallback(async (payload: LoginPayload) => {
        const data = await authApi.login(payload);
        setProfile(data);
        setMaskedEmail(null);
        setStatus('authenticated');
    }, []);

    const register = useCallback(async (payload: RegisterPayload) => {
        const data = await authApi.register(payload);
        setProfile(data);
        setMaskedEmail(null);
        setStatus('authenticated');
    }, []);

    const logout = useCallback(async () => {
        await authApi.logout();
        setProfile(null);
        setMaskedEmail(null);
        setStatus('guest');
    }, []);

    // Обновляет профиль в контексте сразу после успешного PATCH /profile — чтобы
    // имя в шапке (DashboardLayout) и везде ещё обновилось без перезагрузки страницы.
    const updateProfile = useCallback(async (payload: UpdateProfilePayload) => {
        const data = await authApi.updateProfile(payload);
        setProfile(data);
    }, []);

    // Перечитывает профиль без изменения статуса сессии — используется после закрытия
    // попапа верификации Didit (KycStatusSection), чтобы подтянуть актуальный kyc_status,
    // когда вебхук от Didit уже обработан.
    const refreshProfile = useCallback(async () => {
        const data = await authApi.fetchProfile();
        setProfile(data);
    }, []);

    // Вызывается из PwaInstallPrompt.tsx при обнаружении установки приложения — обновляет
    // profile.pwa_installed в контексте, чтобы страница профиля сразу показала отметку без перезагрузки.
    const markPwaInstalled = useCallback(async () => {
        const data = await authApi.markPwaInstalled();
        setProfile(data);
    }, []);

    // Вызывается из PinSetupModal.tsx при успешной установке/смене ПИН-кода — обновляет profile.has_pin,
    // чтобы напоминание установить ПИН (PinSetupPrompt.tsx) больше не показывалось без перезагрузки.
    const setPin = useCallback(async (payload: SetPinPayload) => {
        const data = await authApi.setPin(payload);
        setProfile(data);
    }, []);

    // Вызывается из PinUnlockPage.tsx при успешном вводе ПИН-кода на доверенном устройстве —
    // переводит в 'authenticated' так же, как login()/register(), после чего дашборд наконец
    // начинает грузить данные (карты/транзакции/уведомления и т.д.).
    const unlockPin = useCallback(async (pin: string) => {
        const data = await authApi.unlockPin(pin);
        setProfile(data);
        setMaskedEmail(null);
        setStatus('authenticated');
    }, []);

    // «Войти по паролю» на экране ввода ПИН-кода — отзывает доверие этому устройству и
    // возвращает в 'guest', чтобы GuestRoute наконец пропустил на обычный /login.
    const forgetDevice = useCallback(async () => {
        await authApi.forgetDevice();
        setMaskedEmail(null);
        setStatus('guest');
    }, []);

    const value = useMemo<AuthContextValue>(
        () => ({
            status,
            profile,
            maskedEmail,
            login,
            register,
            logout,
            updateProfile,
            refreshProfile,
            markPwaInstalled,
            setPin,
            unlockPin,
            forgetDevice,
        }),
        [status, profile, maskedEmail, login, register, logout, updateProfile, refreshProfile, markPwaInstalled, setPin, unlockPin, forgetDevice],
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth должен использоваться внутри <AuthProvider>');
    }

    return context;
}
