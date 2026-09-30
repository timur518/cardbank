import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { App } from './App';
import { captureTrackingParams } from './utils/tracking';
import { injectAnalyticsCodes } from './utils/analytics';
import { applyDesktopStandaloneWindowSize, registerServiceWorker } from './utils/pwa';
import './index.css';

// UTM-метки и реферальный код из URL сохраняются в cookie до рендера приложения —
// чтобы RegisterPage мог сразу прочитать их из cookie, если переход на /register был сразу
// с этими параметрами в URL.
captureTrackingParams();

// Код счётчика/пикселей из админки — асинхронно, не блокирует рендер SPA.
void injectAnalyticsCodes();

// PWA: service worker (installability) + фиксация размера окна 1024x800 для установленного
// на десктопе приложения (см. utils/pwa.ts).
registerServiceWorker();
applyDesktopStandaloneWindowSize();

createRoot(document.getElementById('root')!).render(
    <StrictMode>
        <App />
    </StrictMode>,
);
