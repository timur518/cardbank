import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ProtectedRoute, GuestRoute } from './components/routing/ProtectedRoute';
import { AuthShell } from './components/auth/AuthShell';
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
                <Routes>
                    <Route element={<GuestRoute />}>
                        <Route element={<AuthShell />}>
                            <Route path="/login" element={<LoginPage />} />
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
            </AuthProvider>
        </BrowserRouter>
    );
}
