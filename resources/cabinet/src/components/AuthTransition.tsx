import { useEffect, useRef, useState, type ReactElement, type TransitionEvent } from 'react';
import { useLocation, useOutlet } from 'react-router-dom';

interface RenderedRoute {
    key: string;
    node: ReactElement | null;
}

/**
 * Кросс-фейд содержимого .auth-panel при переходе между /login, /register и
 * /forgot-password — сама панель (AuthShell) не пересоздаётся, меняется только
 * шаблон внутри неё: текущий плавно гаснет, затем подставляется новый и плавно
 * проявляется, а высота панели синхронизируется с реальной высотой контента
 * (формы разной длины — вход короче регистрации), чтобы переключение не дёргало
 * вёрстку рывком.
 */
export function AuthTransition() {
    const location = useLocation();
    const outlet = useOutlet();

    const [rendered, setRendered] = useState<RenderedRoute>({ key: location.pathname, node: outlet });
    const [visible, setVisible] = useState(true);

    const wrapperRef = useRef<HTMLDivElement>(null);
    const contentRef = useRef<HTMLDivElement>(null);
    const pendingRef = useRef<RenderedRoute | null>(null);

    // Маршрут сменился — сначала плавно гасим текущий шаблон, подстановка нового
    // произойдёт в handleTransitionEnd по завершении fade-out.
    useEffect(() => {
        if (location.pathname === rendered.key) {
            return;
        }

        pendingRef.current = { key: location.pathname, node: outlet };
        setVisible(false);
    }, [location.pathname, outlet, rendered.key]);

    function handleTransitionEnd(event: TransitionEvent<HTMLDivElement>) {
        if (event.target !== contentRef.current || visible || !pendingRef.current) {
            return;
        }

        const next = pendingRef.current;
        pendingRef.current = null;
        setRendered(next);
        requestAnimationFrame(() => requestAnimationFrame(() => setVisible(true)));
    }

    // Подгоняем высоту обёртки под реальную высоту текущего контента.
    useEffect(() => {
        const content = contentRef.current;
        const wrapper = wrapperRef.current;

        if (!content || !wrapper) {
            return;
        }

        const sync = () => {
            wrapper.style.height = `${content.offsetHeight}px`;
        };
        sync();

        const observer = new ResizeObserver(sync);
        observer.observe(content);
        return () => observer.disconnect();
    }, [rendered.node]);

    return (
        <div ref={wrapperRef} className="auth-transition-wrapper">
            <div
                ref={contentRef}
                className={`auth-transition-content${visible ? ' is-visible' : ''}`}
                onTransitionEnd={handleTransitionEnd}
            >
                {rendered.node}
            </div>
        </div>
    );
}
