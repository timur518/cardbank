import type { ReactNode } from 'react';

interface AuthLayoutProps {
    title: string;
    subtitle?: string;
    children: ReactNode;
    footer?: ReactNode;
}

/**
 * Содержимое одной страницы входа/регистрации/восстановления пароля —
 * только внутренняя часть (заголовок/форма/футер), без внешней панели — она
 * вынесена в AuthShell, который рендерится один раз на все три страницы и не
 * пересоздаётся при переходе между ними — благодаря этому переключение между
 * шаблонами выглядит бесшовным (AuthTransition делает кросс-фейд внутри).
 */
export function AuthLayout({ title, subtitle, children, footer }: AuthLayoutProps) {
    return (
        <>
            <h1 className="text-2xl font-extrabold tracking-tight text-ink">{title}</h1>
            {subtitle ? <p className="mt-2 text-sm text-muted">{subtitle}</p> : null}
            <div className="mt-8 flex flex-col gap-5">{children}</div>
            {footer ? <div className="mt-7 text-center text-sm text-muted">{footer}</div> : null}
        </>
    );
}
