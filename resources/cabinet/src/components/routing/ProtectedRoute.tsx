import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

/** Пускает дальше только полностью авторизованных; 'locked' — на /unlock, иначе — на /login. */
export function ProtectedRoute() {
    const { status } = useAuth();

    if (status === 'checking') {
        return null;
    }

    if (status === 'locked') {
        return <Navigate to="/unlock" replace />;
    }

    if (status === 'guest') {
        return <Navigate to="/login" replace />;
    }

    return <Outlet />;
}

/** Обратное: страницы входа/регистрации не нужны уже авторизованному или заблокированному пользователю. */
export function GuestRoute() {
    const { status } = useAuth();

    if (status === 'checking') {
        return null;
    }

    if (status === 'locked') {
        return <Navigate to="/unlock" replace />;
    }

    if (status === 'authenticated') {
        return <Navigate to="/" replace />;
    }

    return <Outlet />;
}

/** /unlock (PinUnlockPage) доступен только пока status === 'locked' — иначе уводит в ЛК или на /login. */
export function LockedRoute() {
    const { status } = useAuth();

    if (status === 'checking') {
        return null;
    }

    if (status === 'authenticated') {
        return <Navigate to="/" replace />;
    }

    if (status === 'guest') {
        return <Navigate to="/login" replace />;
    }

    return <Outlet />;
}
