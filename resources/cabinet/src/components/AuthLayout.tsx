import type { ReactNode } from 'react';

interface AuthLayoutProps {
    title: string;
    subtitle?: string;
    children: ReactNode;
    footer?: ReactNode;
}

/** Общая обёртка для страниц входа/регистрации/восстановления пароля. */
export function AuthLayout({ title, subtitle, children, footer }: AuthLayoutProps) {
    return (
        <div className="flex min-h-screen items-center justify-center px-4 py-10">
            <div className="auth-panel w-full max-w-[440px] p-8 sm:p-10">
                <h1 className="text-2xl font-extrabold tracking-tight text-ink">{title}</h1>
                {subtitle ? <p className="mt-2 text-sm text-muted">{subtitle}</p> : null}
                <div className="mt-8 flex flex-col gap-5">{children}</div>
                {footer ? <div className="mt-7 text-center text-sm text-muted">{footer}</div> : null}
            </div>
        </div>
    );
}
