import { NavLink } from 'react-router-dom';

/**
 * Нижнее меню приложения — видно только на мобильных экранах (md:hidden),
 * заменяет собой горизонтальную навигацию из шапки. Фон — полупрозрачный
 * белый с блюром (как шапка лендинга, .nav-pill в resources/css/app.css),
 * чтобы ЛК на телефоне ощущался как нативное приложение, а не веб-страница.
 * Центральный пункт «Пополнить» — приподнятая акцентная кнопка (FAB) ведёт на
 * /topup: если у пользователя ровно одна активная карта — сразу открывает её
 * пополнение, иначе даёт выбрать карту (см. TopupEntryPage).
 */
export function MobileTabBar() {
    return (
        <nav className="mobile-tabbar md:hidden">
            <NavLink to="/" end className={({ isActive }) => `mobile-tab${isActive ? ' is-active' : ''}`}>
                <HomeIcon />
                Главная
            </NavLink>

            <NavLink to="/cards" className={({ isActive }) => `mobile-tab${isActive ? ' is-active' : ''}`}>
                <CardsIcon />
                Карты
            </NavLink>

            <NavLink to="/topup" className="mobile-tabbar-item">
                <span className="mobile-tabbar-fab">
                    <PlusIcon />
                </span>
                <span className="mobile-tabbar-fab-label">Пополнить</span>
            </NavLink>

            <NavLink to="/transactions" className={({ isActive }) => `mobile-tab${isActive ? ' is-active' : ''}`}>
                <HistoryIcon />
                История
            </NavLink>

            <NavLink to="/profile" className={({ isActive }) => `mobile-tab${isActive ? ' is-active' : ''}`}>
                <ProfileIcon />
                Профиль
            </NavLink>
        </nav>
    );
}

function HomeIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M4 11.5 12 4l8 7.5" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 10v9a1 1 0 0 0 1 1h3v-5a2 2 0 0 1 4 0v5h3a1 1 0 0 0 1-1v-9" />
        </svg>
    );
}

function CardsIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <rect x="2.5" y="5" width="19" height="14" rx="2.5" />
            <path strokeLinecap="round" d="M2.5 9.5h19" />
        </svg>
    );
}

function HistoryIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <circle cx="12" cy="13" r="8" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v4l3 2" />
            <path strokeLinecap="round" d="M9 3h6" />
        </svg>
    );
}

function ProfileIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <circle cx="12" cy="8" r="3.5" />
            <path strokeLinecap="round" d="M4.5 20c1.4-4 4.2-6 7.5-6s6.1 2 7.5 6" />
        </svg>
    );
}

function PlusIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.2}>
            <path strokeLinecap="round" d="M12 5v14M5 12h14" />
        </svg>
    );
}
