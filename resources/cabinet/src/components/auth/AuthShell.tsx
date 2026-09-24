import { BrandLogo } from '../common/BrandLogo';
import { AuthTransition } from './AuthTransition';

/**
 * Внешняя плавающая панель (.auth-panel) для /login, /register, /forgot-password —
 * рендерится один раз как layout-route в App.tsx и не пересоздаётся при переходах
 * между этими тремя страницами, поэтому смена шаблона внутри неё (см. AuthTransition)
 * выглядит бесшовно, а не как перезагрузка блока.
 * Логотип вынесен над панелью и центрирован по верхнему краю экрана —
 * идентично /login (LoginPage), где он тоже вынесен из карточки входа.
 */
export function AuthShell() {
    return (
        <div className="flex min-h-screen flex-col items-center bg-white px-4 py-10">
            <div className="mb-10">
                <BrandLogo />
            </div>

            <div className="flex w-full flex-1 items-center justify-center">
                <div className="auth-panel w-full max-w-[440px] p-8 sm:p-10">
                    <AuthTransition />
                </div>
            </div>
        </div>
    );
}
