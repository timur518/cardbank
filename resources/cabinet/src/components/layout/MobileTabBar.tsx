import { useEffect, useLayoutEffect, useRef, useState, type JSX } from 'react';
import { NavLink, useLocation } from 'react-router-dom';

interface TabDef {
    to: string;
    end?: boolean;
    label: string;
    icon: () => JSX.Element;
}

// Порядок соответствует индексам contentRefs (0..3) — используется для расчёта позиции
// скользящего индикатора активного пункта. Кнопка «Пополнить» (FAB) в этот массив
// не входит — она не участвует в подсветке, а всегда рендерится отдельно по центру.
const TABS: TabDef[] = [
    { to: '/', end: true, label: 'Главная', icon: HomeIcon },
    { to: '/cards', label: 'Карты', icon: CardsIcon },
    { to: '/transactions', label: 'История', icon: HistoryIcon },
    { to: '/profile', label: 'Профиль', icon: ProfileIcon },
];

function isTabActive(pathname: string, tab: TabDef): boolean {
    if (tab.end) {
        return pathname === tab.to;
    }

    return pathname === tab.to || pathname.startsWith(`${tab.to}/`);
}

// Небольшой запас сверх диагонали содержимого, чтобы круг не облегал иконку с подписью
// впритык, а оставлял немного воздуха по краям.
const INDICATOR_PADDING = 8;

/**
 * Нижнее меню приложения — видно только на мобильных экранах (lg:hidden, тот же
 * порог, что и у горизонтального меню в шапке и сайдбара «Мои карты»), заменяет
 * собой навигацию из шапки. Плавающая закруглённая панель с отступом 5px от
 * нижней границы экрана и полупрозрачным фоном с блюром — точно как шапка
 * лендинга (.nav-pill в resources/css/app.css), чтобы ЛК на телефоне ощущался
 * как нативное приложение.
 *
 * Переход между разделами сопровождается скользящим индикатором активного пункта:
 * идеально круглый «пузырь»-подсветка (width === height, border-radius: 50%) плавно
 * переезжает к новому разделу (translate с пружинящим cubic-bezier), а иконка и подпись
 * внутри него всегда строго по центру круга — вместо того, чтобы активный пункт просто
 * резко менял цвет. Диаметр круга считается индивидуально под каждый пункт как диагональ
 * прямоугольника «иконка + подпись» (Math.hypot) с небольшим запасом, поэтому оба элемента
 * гарантированно помещаются внутри при любой длине подписи. Позиция считается через refs,
 * пересчитывается при смене маршрута и при изменении размеров окна.
 *
 * Центральный пункт «Пополнить» — приподнятая акцентная кнопка (FAB) ведёт на
 * /topup: если у пользователя ровно одна активная карта — сразу открывает её
 * пополнение, иначе даёт выбрать карту (см. TopupEntryPage).
 */
export function MobileTabBar() {
    const location = useLocation();
    const contentRefs = useRef<Array<HTMLSpanElement | null>>([]);
    const [indicator, setIndicator] = useState({ x: 0, y: 0, size: 0, visible: false });

    function measure() {
        const activeIndex = TABS.findIndex((tab) => isTabActive(location.pathname, tab));
        const el = activeIndex >= 0 ? contentRefs.current[activeIndex] : null;

        if (!el) {
            setIndicator((prev) => ({ ...prev, visible: false }));
            return;
        }

        // Измеряем именно внутренний блок «иконка + подпись» (без паддингов ссылки) —
        // диаметр круга равен его диагонали с небольшим запасом, поэтому оба элемента
        // всегда целиком укладываются внутри идеально круглого индикатора.
        const size = Math.hypot(el.offsetWidth, el.offsetHeight) + INDICATOR_PADDING;

        setIndicator({
            x: el.offsetLeft + el.offsetWidth / 2,
            y: el.offsetTop + el.offsetHeight / 2,
            size,
            visible: true,
        });
    }

    useLayoutEffect(measure, [location.pathname]);

    // Перерегистрируется при каждой смене маршрута, чтобы measure() внутри замыкания
    // всегда видел актуальный location.pathname, а не значение на момент монтирования.
    useEffect(() => {
        window.addEventListener('resize', measure);
        return () => window.removeEventListener('resize', measure);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [location.pathname]);

    return (
        <nav className="mobile-tabbar lg:hidden">
            <span
                className={`mobile-tab-indicator${indicator.visible ? ' is-visible' : ''}`}
                style={{
                    width: indicator.size,
                    height: indicator.size,
                    transform: `translate(${indicator.x - indicator.size / 2}px, ${indicator.y - indicator.size / 2}px)`,
                }}
            />

            {TABS.slice(0, 2).map((tab, index) => (
                <TabLink key={tab.to} tab={tab} contentRef={(el) => (contentRefs.current[index] = el)} />
            ))}

            <NavLink to="/topup" className="mobile-tabbar-item">
                <span className="mobile-tabbar-fab">
                    <PlusIcon />
                </span>
                <span className="mobile-tabbar-fab-label">Пополнить</span>
            </NavLink>

            {TABS.slice(2).map((tab, index) => (
                <TabLink key={tab.to} tab={tab} contentRef={(el) => (contentRefs.current[index + 2] = el)} />
            ))}
        </nav>
    );
}

function TabLink({ tab, contentRef }: { tab: TabDef; contentRef: (el: HTMLSpanElement | null) => void }) {
    const Icon = tab.icon;

    return (
        <NavLink to={tab.to} end={tab.end} className={({ isActive }) => `mobile-tab${isActive ? ' is-active' : ''}`}>
            <span className="mobile-tab-content" ref={contentRef}>
                <span className="mobile-tab-icon">
                    <Icon />
                </span>
                <span className="mobile-tab-label">{tab.label}</span>
            </span>
        </NavLink>
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
