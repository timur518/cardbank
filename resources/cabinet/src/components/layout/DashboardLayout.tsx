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

/** Логотип из настроек бренда (Filament → BrandSettings); пока настройки грузятся
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

function LogoutIcon() {
    return (
        <svg className="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M15 17.5v1a2.5 2.5 0 0 1-2.5 2.5h-6A2.5 2.5 0 0 1 4 18.5v-13A2.5 2.5 0 0 1 6.5 3h6A2.5 2.5 0 0 1 15 5.5v1" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h11m0 0-3.5-3.5M20 12l-3.5 3.5" />
        </svg>
    );
}

/**
 * Шапка ЛК — та же плавающая «таблетка» с блюром, что и на лендинге
 * (.site-header/.nav-pill, resources/views/welcome.blade.php): закреплена
 * (sticky) с отступом от верхней границы 20px (на лендинге — 40px, здесь
 * меньше, т.к. под шапкой нет hero-видео), при прокрутке страницы фон
 * пилюли уплотняется тем же способом (класс is-scrolled переключается по
 * scroll-листенеру, как в resources/js/app.js).
 *
 * Общая шапка и обёртка контента для всех страниц ЛК, доступных после
 * входа. На widescreen — горизонтальное меню внутри пилюли и
 * компактная кнопка-иконка выхода справа. На мобильных экранах меню
 * скрыто — навигация переезжает в нижнее меню приложения (MobileTabBar),
 * поэтому под контентом добавлен отступ, чтобы его не перекрывала
 * фиксированная панель.
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

                    <nav className="hidden items-center gap-8 text-[16px] lg:flex">
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
                            <LogoutIcon />
                        </button>
                    </div>
                </div>
            </header>

            <main className="mx-auto max-w-[1200px] px-4 py-6 pb-28 lg:px-8 lg:py-8 lg:pb-8">
                <Outlet />
            </main>

            <MobileTabBar />
        </div>
    );
}
