import { useEffect, useState } from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import { ProtectedRoute, GuestRoute } from './components/routing/ProtectedRoute';
import { AuthShell } from './components/auth/AuthShell';
import { Preloader } from './components/common/Preloader';
import { DashboardLayout } from './components/layout/DashboardLayout';
import { LoginPage } from './pages/auth/LoginPage';
import { RegisterPage } from './pages/auth/RegisterPage';
import { ForgotPasswordPage } from './pages/auth/ForgotPasswordPage';
import { DashboardPage } from './pages/dashboard/DashboardPage';
import { CardDetailPage } from './pages/cards/CardDetailPage';
import { CardsPage } from './pages/cards/CardsPage';
import { NewCardOrderPage } from './pages/orders/NewCardOrderPage';
import { TransactionsPage } from './pages/transactions/TransactionsPage';
import { PartnershipPage } from './pages/partnership/PartnershipPage';
import { ProfilePage } from './pages/profile/ProfilePage';
import { TopupEntryPage } from './pages/topup/TopupEntryPage';

export function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <AppContent />
            </AuthProvider>
        </BrowserRouter>
    );
}

/**
 * Прелоадер (Preloader.tsx) показывается только на время проверки сессии в AuthContext
 * (status === 'checking', см. AuthContext.tsx) — то есть при жёсткой перезагрузке страницы
 * или заходе по прямой ссылке. AuthProvider монтируется один раз на всё время жизни SPA и
 * не размонтируется при переходах между страницами через React Router (включая переход на
 * главную из других страниц), поэтому status меняет 'checking' на 'guest'/'authenticated'
 * только один раз за сессию — повторно прелоадер эти переходы не запускают.
 */
function AppContent() {
    const { status } = useAuth();
    const [showPreloader, setShowPreloader] = useState(true);
    const [isHiding, setIsHiding] = useState(false);

    useEffect(() => {
        if (status === 'checking') {
            return;
        }

        // Небольшая минимальная задержка перед скрытием — чтобы анимация не мелькала
        // одним кадром на быстрой сети, а затем плавный фейд-аут (см. .app-preloader.is-hiding).
        const showTimer = setTimeout(() => setIsHiding(true), 400);
        const removeTimer = setTimeout(() => setShowPreloader(false), 800);

        return () => {
            clearTimeout(showTimer);
            clearTimeout(removeTimer);
        };
    }, [status]);

    return (
        <>
            {showPreloader && <Preloader isHiding={isHiding} />}

            <Routes>
                <Route element={<GuestRoute />}>
                    {/* /login — свой самостоятельный двухколоночный экран (карточка входа + видеопанель),
                        не через общий AuthShell/AuthLayout — его вёрстка структурно не похожа на
                        /register и /forgot-password, кроссфейд между ними больше не нужен. */}
                    <Route path="/login" element={<LoginPage />} />

                    <Route element={<AuthShell />}>
                        <Route path="/register" element={<RegisterPage />} />
                        <Route path="/forgot-password" element={<ForgotPasswordPage />} />
                    </Route>
                </Route>

                <Route element={<ProtectedRoute />}>
                    <Route element={<DashboardLayout />}>
                        <Route path="/" element={<DashboardPage />} />
                        <Route path="/cards" element={<CardsPage />} />
                        <Route path="/cards/new" element={<NewCardOrderPage />} />
                        <Route path="/cards/:id" element={<CardDetailPage />} />
                        <Route path="/transactions" element={<TransactionsPage />} />
                        <Route path="/partnership" element={<PartnershipPage />} />
                        <Route path="/profile" element={<ProfilePage />} />
                        <Route path="/topup" element={<TopupEntryPage />} />
                    </Route>
                </Route>
            </Routes>
        </>
    );
}
