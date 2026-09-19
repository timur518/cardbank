import { AuthTransition } from './AuthTransition';

/**
 * Внешняя плавающая панель (.auth-panel) для /login, /register, /forgot-password —
 * рендерится один раз как layout-route в App.tsx и не пересоздаётся при переходах
 * между этими тремя страницами, поэтому смена шаблона внутри неё (см. AuthTransition)
 * выглядит бесшовно, а не как перезагрузка блока.
 */
export function AuthShell() {
    return (
        <div className="flex min-h-screen items-center justify-center px-4 py-10">
            <div className="auth-panel w-full max-w-[440px] p-8 sm:p-10">
                <AuthTransition />
            </div>
        </div>
    );
}
