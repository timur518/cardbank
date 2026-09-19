import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ProtectedRoute, GuestRoute } from './components/ProtectedRoute';
import { AuthShell } from './components/AuthShell';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';
import { ForgotPasswordPage } from './pages/ForgotPasswordPage';
import { DashboardPage } from './pages/DashboardPage';

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
                        <Route path="/" element={<DashboardPage />} />
                    </Route>
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}
