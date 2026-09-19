import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import * as authApi from '../api/auth';
import type { LoginPayload, Profile, RegisterPayload } from '../api/types';

type AuthStatus = 'checking' | 'guest' | 'authenticated';

interface AuthContextValue {
    status: AuthStatus;
    profile: Profile | null;
    login: (payload: LoginPayload) => Promise<void>;
    register: (payload: RegisterPayload) => Promise<void>;
    logout: () => Promise<void>;
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

    const value = useMemo<AuthContextValue>(
        () => ({ status, profile, login, register, logout }),
        [status, profile, login, register, logout],
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
