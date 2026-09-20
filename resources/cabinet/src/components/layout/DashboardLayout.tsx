import { ArrowRightStartOnRectangleIcon } from '@heroicons/react/24/outline';
import { useEffect, useState } from 'react';
import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { fetchBrandSettings } from '../../api/settings';
import { MobileTabBar } from './MobileTabBar';

const NAV_ITEMS = [
    { to: '/', label: 'Главная', end: true },
    { to: '/cards', label: 'Мои карты', end: false },
    { to: '/transactions', label: 'Операции', end: false },
    { to: '/partnership', label: 'Партнёрство', end: false },
];

/** Логотип из настроек; пока настройки грузятся
 * или логотип не загружен — показываем название сайта текстом (.brand-mark —
 * тот же класс, что и у логотипа-текста на лендинге). */
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
        return <img src={logo} alt={siteName} className="h-8 w-auto" />;
    }

    return <span className="brand-mark">{siteName}</span>;
}



/**
 * Шапка ЛК
 */
export function DashboardLayout() {
    const { profile, logout } = useAuth();
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        function updateScrolled() {
            setScrolled(window.scrollY > 20);
        }

        updateScrolled();
        window.addEventListener('scroll', updateScrolled, { passive: true });
        return () => window.removeEventListener('scroll', updateScrolled);
    }, []);

    return (
        <div className="min-h-screen">
            <header
                className={`site-header sticky top-[20px] z-30 mt-[20px] px-4 lg:px-8 ${scrolled ? 'is-scrolled' : ''}`}
            >
                <div className="nav-pill mx-auto flex max-w-[1200px] items-center justify-between gap-4 rounded-full px-5 py-3 sm:px-7">
                    <NavLink to="/">
                        <BrandLogo />
                    </NavLink>

                    <nav className="hidden items-center gap-8 text-[16px] md:flex">
                        {NAV_ITEMS.map((item) => (
                            <NavLink
                                key={item.to}
                                to={item.to}
                                end={item.end}
                                className={({ isActive }) => `nav-link ${isActive ? 'is-active' : ''}`}
                            >
                                {item.label}
                            </NavLink>
                        ))}
                    </nav>

                    <div className="flex items-center gap-3">
                        <NavLink
                            to="/profile"
                            className="hidden text-sm font-semibold text-ink hover:text-orange-dark sm:inline"
                        >
                            {profile?.first_name} {profile?.last_name}
                        </NavLink>
                        <button type="button" className="icon-btn" title="Выйти" aria-label="Выйти" onClick={() => logout()}>
                            <ArrowRightStartOnRectangleIcon className="h-[18px] w-[18px]" />
                        </button>
                    </div>
                </div>
            </header>

            <main className="mx-auto max-w-[1200px] px-4 py-6 pb-28 md:pb-8 lg:px-8 lg:py-8">
                <Outlet />
            </main>

            <MobileTabBar />
        </div>
    );
}
