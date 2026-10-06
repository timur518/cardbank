import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { App } from './App';
import { captureTrackingParams } from './utils/tracking';
import { injectAnalyticsCodes } from './utils/analytics';
import { applyDesktopStandaloneWindowSize, initInstallPromptCapture, registerServiceWorker } from './utils/pwa';
import './index.css';

// Перехват beforeinstallprompt — самое первое, до рендера React-дерева: это одноразовое
// событие, и браузер может выстрелить его раньше, чем PwaInstallPrompt успеет смонтироваться
// внутри DashboardLayout (после проверки сессии/прелоадера) — без этого событие теряется
// безвозвратно и попап не показывается до следующей полной перезагрузки страницы.
initInstallPromptCapture();

// UTM-метки и реферальный код из URL сохраняются в cookie до рендера приложения —
// чтобы RegisterPage мог сразу прочитать их из cookie, если переход на /register был сразу
// с этими параметрами в URL.
captureTrackingParams();

// Контейнер данных эл.коммерции Яндекс.Метрики (utils/ecommerce.ts) должен существовать до
// загрузки скрипта счётчика ниже (он асинхронный — придёт из настроек админки), иначе события,
// отправленные до его загрузки (например, impressions при открытии /cards/new сразу после
// входа), были бы потеряны — счётчик связывается с массивом по имени, не создаёт его заново.
window.dataLayer = window.dataLayer || [];

// Код счётчика/пикселей из админки — асинхронно, не блокирует рендер SPA.
void injectAnalyticsCodes();

// PWA: service worker (installability) + фиксация размера окна 770x850 для установленного
// на десктопе приложения (см. utils/pwa.ts).
registerServiceWorker();
applyDesktopStandaloneWindowSize();

// Полностью глушим системное контекстное меню по правому клику во всём ЛК (как в нативном
// приложении) — событие просто не всплывает ни к чему, никакого собственного меню не показываем.
document.addEventListener('contextmenu', (event) => event.preventDefault());

createRoot(document.getElementById('root')!).render(
    <StrictMode>
        <App />
    </StrictMode>,
);
