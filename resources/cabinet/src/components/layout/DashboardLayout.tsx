import { ArrowRightStartOnRectangleIcon, BellIcon } from '@heroicons/react/24/outline';
import { useEffect, useState } from 'react';
import { NavLink, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useNotifications } from '../../hooks/useNotifications';
import { BrandLogo } from '../common/BrandLogo';
import { NotificationsPanel } from '../notifications/NotificationsPanel';
import { MobileTabBar } from './MobileTabBar';

const NAV_ITEMS = [
    { to: '/', label: 'Главная', end: true },
    { to: '/cards', label: 'Мои карты', end: false },
    { to: '/transactions', label: 'Операции', end: false },
    { to: '/partnership', label: 'Партнёрство', end: false },
];



/**
 * Шапка ЛК
 */
export function DashboardLayout() {
    const { profile, logout } = useAuth();
    const location = useLocation();
    const [scrolled, setScrolled] = useState(false);
    const notifications = useNotifications();

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
                <div className="nav-pill mx-auto flex max-w-[1000px] items-center justify-between gap-4 rounded-full px-5 py-3 sm:px-7">
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

                    <div className="relative flex items-center gap-3">
                        <NavLink
                            to="/profile"
                            className="hidden text-sm font-semibold text-ink hover:text-orange-dark sm:inline"
                        >
                            {profile?.first_name} {profile?.last_name}
                        </NavLink>
                        <button
                            ref={notifications.triggerRef}
                            type="button"
                            className="icon-btn notif-trigger"
                            title="Уведомления"
                            aria-label="Уведомления"
                            onClick={notifications.toggle}
                        >
                            <BellIcon className="h-[18px] w-[18px]" />
                            {notifications.unreadCount > 0 && <span className="notif-badge-dot" />}
                        </button>
                        {/* Десктопный попап — абсолютно позиционирован от этого же relative-блока (точно под
                            кнопкой, без ручных расчётов координат); мобильная версия намеренно вынесена за
                            пределы шапки — см. комментарий у NotificationsPanel ниже. */}
                        <NotificationsPanel
                            mode="desktop"
                            open={notifications.open}
                            loading={notifications.loading}
                            items={notifications.items}
                            onClose={notifications.close}
                            panelRef={notifications.desktopPanelRef}
                        />
                        <button type="button" className="icon-btn" title="Выйти" aria-label="Выйти" onClick={() => logout()}>
                            <ArrowRightStartOnRectangleIcon className="h-[18px] w-[18px]" />
                        </button>
                    </div>
                </div>
            </header>

            {/* Мобильный попап уведомлений — отдельным элементом вне <header>: выезжает снизу вверх как нижний
                лист (bottom sheet), 1-в-1 как модалка пополнения (Modal.tsx/TopupModal, те же CSS-классы
                .modal-overlay/.modal-sheet-*) — с затемнением фона поверх и шапки, и нижнего меню, как и у
                любой другой модалки в приложении. Если бы этот элемент был вложен внутрь <header>, его
                z-index был бы ограничен стекинг-контекстом .site-header и он не смог бы перекрыть нижнее меню
                (отдельный sibling с более высоким z-index вне шапки). */}
            <NotificationsPanel
                mode="mobile"
                open={notifications.open}
                loading={notifications.loading}
                items={notifications.items}
                onClose={notifications.close}
                panelRef={notifications.mobilePanelRef}
            />

            {/* key={location.pathname} — заставляет React перемонтировать этот div при каждом переходе между
                страницами, чтобы .page-transition запускалась заново каждый раз, а не только один
                раз при первом рендере лейаута. */}
            <main className="mx-auto max-w-[830px] px-4 py-6 pb-28 md:pb-8 lg:px-8 lg:py-8">
                <div key={location.pathname} className="page-transition">
                    <Outlet />
                </div>
            </main>

            <MobileTabBar />
        </div>
    );
}
