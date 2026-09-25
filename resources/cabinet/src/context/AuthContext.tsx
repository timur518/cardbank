import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import * as authApi from '../api/auth';
import type { LoginPayload, Profile, RegisterPayload, UpdateProfilePayload } from '../api/types';

type AuthStatus = 'checking' | 'guest' | 'authenticated';

interface AuthContextValue {
    status: AuthStatus;
    profile: Profile | null;
    login: (payload: LoginPayload) => Promise<void>;
    register: (payload: RegisterPayload) => Promise<void>;
    logout: () => Promise<void>;
    updateProfile: (payload: UpdateProfilePayload) => Promise<void>;
    refreshProfile: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [status, setStatus] = useState<AuthStatus>('checking');
    const [profile, setProfile] = useState<Profile | null>(null);

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
                setStatus('guest');
            });
    }, []);

    const login = useCallback(async (payload: LoginPayload) => {
        const data = await authApi.login(payload);
        setProfile(data);
        setStatus('authenticated');
    }, []);

    const register = useCallback(async (payload: RegisterPayload) => {
        const data = await authApi.register(payload);
        setProfile(data);
        setStatus('authenticated');
    }, []);

    const logout = useCallback(async () => {
        await authApi.logout();
        setProfile(null);
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

    const value = useMemo<AuthContextValue>(
        () => ({ status, profile, login, register, logout, updateProfile, refreshProfile }),
        [status, profile, login, register, logout, updateProfile, refreshProfile],
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
