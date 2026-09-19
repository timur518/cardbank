import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

/** Пускает дальше только авторизованных; иначе — на /login. */
export function ProtectedRoute() {
    const { status } = useAuth();

    if (status === 'checking') {
        return null;
    }

    if (status === 'guest') {
        return <Navigate to="/login" replace />;
    }

    return <Outlet />;
}

/** Обратное: страницы входа/регистрации не нужны уже авторизованному пользователю. */
export function GuestRoute() {
    const { status } = useAuth();

    if (status === 'checking') {
        return null;
    }

    if (status === 'authenticated') {
        return <Navigate to="/" replace />;
    }

    return <Outlet />;
}
