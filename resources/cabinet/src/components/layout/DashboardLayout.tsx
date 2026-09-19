import { useEffect, useState } from 'react';
import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { fetchBrandSettings } from '../../api/settings';

const NAV_ITEMS = [
    { to: '/', label: 'Главная', end: true },
    { to: '/cards', label: 'Мои карты', end: false },
    { to: '/transactions', label: 'Операции', end: false },
    { to: '/partnership', label: 'Партнёрство', end: false },
];

/** Логотип из настроек бренда (Filament → BrandSettings); пока настройки грузятся
 * или логотип не загружен — показываем название сайта текстом. */
function BrandLogo() {
    const [logo, setLogo] = useState<string | null>(null);
    const [siteName, setSiteName] = useState('CardBank');

    useEffect(() => {
        fetchBrandSettings()
            .then((brand) => {
                setLogo(brand.brand_logo);
                if (brand.brand_site_name) {
                    setSiteName(brand.brand_site_name);
                }
            })
            .catch(() => {
                // Настройки не критичны для рендера шапки — молча остаёмся на дефолте.
            });
    }, []);

    if (logo) {
        return <img src={logo} alt={siteName} className="h-9 w-auto" />;
    }

    return <span className="text-xl font-extrabold tracking-tight text-ink">{siteName}</span>;
}

/** Общая шапка (логотип, имя пользователя, выход, горизонтальное меню) и обёртка
 * контента для всех страниц личного кабинета, доступных после входа. */
export function DashboardLayout() {
    const { profile, logout } = useAuth();

    return (
        <div className="min-h-screen">
            <header className="border-b border-border bg-surface">
                <div className="mx-auto flex max-w-[1200px] items-center justify-between px-4 py-4 lg:px-8">
                    <BrandLogo />
                    <div className="flex items-center gap-4">
                        <span className="text-sm font-semibold text-ink">
                            {profile?.first_name} {profile?.last_name}
                        </span>
                        <button type="button" className="btn btn-primary" onClick={() => logout()}>
                            Выйти
                        </button>
                    </div>
                </div>
                <nav className="mx-auto flex max-w-[1200px] gap-7 px-4 pb-4 lg:px-8">
                    {NAV_ITEMS.map((item) => (
                        <NavLink
                            key={item.to}
                            to={item.to}
                            end={item.end}
                            className={({ isActive }) => `dashboard-nav-link ${isActive ? 'is-active' : ''}`}
                        >
                            {item.label}
                        </NavLink>
                    ))}
                </nav>
            </header>

            <main className="mx-auto max-w-[1200px] px-4 py-8 lg:px-8">
                <Outlet />
            </main>
        </div>
    );
}
