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

// Фиксированный размер овала-индикатора — один и тот же для всех пунктов меню, подобран вручную
// через dev tools так, чтобы иконка с подписью всегда оказывались с идеальным отступом от краёв.
const INDICATOR_WIDTH = 73;
const INDICATOR_HEIGHT = 57;
// Базовые top/left индикатора в CSS (см. .mobile-tab-indicator в index.css) — учитываются при
// переводе центра активной вкладки в translate(x, y), чтобы итоговая позиция (top + y, left + x)
// точно центрировала овал относительно иконки и подписи.
const INDICATOR_TOP = -5;
const INDICATOR_LEFT = -9;

/**
 * Нижнее меню приложения — видно только на мобильных экранах (lg:hidden, тот же
 * порог, что и у горизонтального меню в шапке и сайдбара «Мои карты»), заменяет
 * собой навигацию из шапки. Плавающая закруглённая панель с отступом 5px от
 * нижней границы экрана и полупрозрачным фоном с блюром — точно как шапка
 * лендинга (.nav-pill в resources/css/app.css), чтобы ЛК на телефоне ощущался
 * как нативное приложение.
 *
 * Переход между разделами сопровождается скользящим индикатором активного пункта:
 * овальная «пузырь»-подсветка фиксированного размера (73×57, подобран вручную через dev tools)
 * плавно переезжает к новому разделу (translate с пружинящим cubic-bezier), а иконка и подпись внутри
 * него всегда строго по центру овала — вместо того, чтобы активный пункт просто резко менял
 * цвет. Центр активной вкладки (иконка + подпись вместе) считается через refs, и овал
 * центрируется на нём transform: translate(x, y) поверх базовых top/left из CSS. Пересчитывается
 * при смене маршрута и при изменении размеров окна.
 *
 * Центральный пункт «Пополнить» — приподнятая акцентная кнопка (FAB) ведёт на
 * /topup: если у пользователя ровно одна активная карта — сразу открывает её
 * пополнение, иначе даёт выбрать карту (см. TopupEntryPage).
 */
export function MobileTabBar() {
    const location = useLocation();
    const contentRefs = useRef<Array<HTMLSpanElement | null>>([]);
    const [indicator, setIndicator] = useState({ x: 0, y: 0, visible: false });

    function measure() {
        const activeIndex = TABS.findIndex((tab) => isTabActive(location.pathname, tab));
        const el = activeIndex >= 0 ? contentRefs.current[activeIndex] : null;

        if (!el) {
            setIndicator((prev) => ({ ...prev, visible: false }));
            return;
        }

        // Центр внутреннего блока «иконка + подпись» (без паддингов ссылки). translate(x, y) добавляется
        // к базовым top/left из CSS, поэтому итоговая позиция овала (top + y, left + x) должна равняться
        // (centerY - height/2, centerX - width/2), отсюда x = centerX - width/2 - left, y = centerY - height/2 - top.
        setIndicator({
            x: el.offsetLeft + el.offsetWidth / 2 - INDICATOR_WIDTH / 2 - INDICATOR_LEFT,
            y: el.offsetTop + el.offsetHeight / 2 - INDICATOR_HEIGHT / 2 - INDICATOR_TOP,
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
                style={{ transform: `translate(${indicator.x}px, ${indicator.y}px)` }}
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
