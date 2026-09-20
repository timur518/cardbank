import { type JSX } from 'react';
import { NavLink } from 'react-router-dom';
import { CardsIcon, HistoryIcon, HomeIcon, PlusIcon, ProfileIcon } from '../common/Icons';

interface TabDef {
    to: string;
    end?: boolean;
    label: string;
    icon: () => JSX.Element;
}

// Кнопка «Пополнить» (FAB) в этот массив не входит — она всегда рендерится отдельно по центру.
const TABS: TabDef[] = [
    { to: '/', end: true, label: 'Главная', icon: HomeIcon },
    { to: '/cards', label: 'Карты', icon: CardsIcon },
    { to: '/transactions', label: 'История', icon: HistoryIcon },
    { to: '/profile', label: 'Профиль', icon: ProfileIcon },
];

/**
 * Нижнее меню приложения — видно ТОЛЬКО на мобильных экранах, скрывается с 768px вверх через
 * @media (min-width: 768px) { display: none } в самом index.css (а не через Tailwind-класс
 * md:hidden — .mobile-tabbar здесь кастомный класс вне @layer, а Tailwind-утилиты лежат внутри
 * @layer utilities, и нелейерный display: flex здесь всегда побеждал бы лейерный md:hidden).
 * Тот же порог 768px и у горизонтального меню в шапке (появляется с md:flex) — никакой
 * «дыры» без навигации между ними нет. Заменяет собой навигацию из шапки. Плавающая закруглённая
 * панель с отступом 5px от нижней границы экрана и полупрозрачным тёмным фоном с блюром.
 *
 * Активный пункт подсвечивается без отдельного «скользящего» индикатора и JS-расчёта позиции
 * (раньше это делал отдельный .mobile-tab-indicator через refs и translate(x, y), но любая
 * последующая правка размеров/отступов требовала пересчитывать его вручную — ненадёжно). Фон
 * активного состояния теперь повешен прямо на сам .mobile-tab (min-width/min-height задаёт
 * размер овала у любого пункта, background появляется только у .is-active) — он гарантированно
 * центрирован на своём пункте (align-items/justify-content у .mobile-tab), без риска
 * рассинхронизации с реальным положением элемента на экране.
 *
 * Центральный пункт «Пополнить» — приподнятая акцентная кнопка (FAB) ведёт на
 * /topup: если у пользователя ровно одна активная карта — сразу открывает её
 * пополнение, иначе даёт выбрать карту (см. TopupEntryPage).
 */
export function MobileTabBar() {
    return (
        <nav className="mobile-tabbar">
            {TABS.slice(0, 2).map((tab) => (
                <TabLink key={tab.to} tab={tab} />
            ))}

            <NavLink to="/topup" className="mobile-tabbar-item">
                <span className="mobile-tabbar-fab">
                    <PlusIcon />
                </span>
                <span className="mobile-tabbar-fab-label">Пополнить</span>
            </NavLink>

            {TABS.slice(2).map((tab) => (
                <TabLink key={tab.to} tab={tab} />
            ))}
        </nav>
    );
}

function TabLink({ tab }: { tab: TabDef }) {
    const Icon = tab.icon;

    return (
        <NavLink to={tab.to} end={tab.end} className={({ isActive }) => `mobile-tab${isActive ? ' is-active' : ''}`}>
            <span className="mobile-tab-content">
                <span className="mobile-tab-icon">
                    <Icon />
                </span>
                <span className="mobile-tab-label">{tab.label}</span>
            </span>
        </NavLink>
    );
}

