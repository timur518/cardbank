import { useAuth } from '../context/AuthContext';

// Заготовка главной страницы аккаунта — точная структура (карты, транзакции,
// пополнение и т.д., см. CABINET_API_SPEC.md, разделы 3–4) будет уточнена отдельно.
export function DashboardPage() {
    const { profile, logout } = useAuth();

    return (
        <div className="mx-auto flex min-h-screen max-w-[1100px] flex-col gap-8 px-4 py-10">
            <header className="flex items-center justify-between">
                <div>
                    <p className="text-sm text-muted">Личный кабинет</p>
                    <h1 className="text-2xl font-extrabold tracking-tight text-ink">
                        Здравствуйте, {profile?.first_name}!
                    </h1>
                </div>
                <button type="button" className="btn btn-primary" onClick={() => logout()}>
                    Выйти
                </button>
            </header>

            <div className="auth-panel p-8 text-sm text-muted">
                Здесь появятся ваши карты и история операций.
            </div>
        </div>
    );
}
