interface PreloaderProps {
    isHiding?: boolean;
}

/**
 * Полноэкранный прелоадер первой загрузки ЛК — заблюренный оверлей с бегущим бликом
 * (тот же приём, что у .skeleton) и двумя вращающимися в разные стороны дугами вокруг
 * круглого логотипа, имитирующими процесс загрузки. Монтируется в AppContent (App.tsx)
 * только на время проверки сессии в AuthContext (GET /profile) — один раз за жизнь SPA,
 * а не при каждом переходе между страницами.
 */
export function Preloader({ isHiding = false }: PreloaderProps) {
    return (
        <div className={`app-preloader ${isHiding ? 'is-hiding' : ''}`}>
            <div className="app-preloader-spinner">
                <svg className="app-preloader-ring app-preloader-ring-a" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="54" pathLength={100} strokeDasharray="26 74" strokeLinecap="round" />
                </svg>
                <svg className="app-preloader-ring app-preloader-ring-b" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="44" pathLength={100} strokeDasharray="20 80" strokeLinecap="round" />
                </svg>
                <img src="https://mojno.cc/assets/images/logo_min.svg" alt="" className="app-preloader-logo" />
            </div>
        </div>
    );
}
