import { ChatBubbleLeftRightIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { useEffect, useState } from 'react';

// Адрес встраиваемой страницы консультанта (отдельное PHP-приложение public/mojno-help,
// см. его README) — запрашивается с ?embed=1, чтобы там скрылись собственные шапка/футер
// страницы и остался только компактный диалог, подогнанный под размер окна виджета
// (см. public/mojno-help/public/chat.css, body.is-embedded). Если переменная не задана —
// виджет нигде не показывается, чтобы не выводить пустое/битое окно до настройки на проде.
const CHAT_WIDGET_URL = import.meta.env.VITE_CHAT_WIDGET_URL;

/**
 * Плавающий виджет чата с ИИ-консультантом — классический паттерн онлайн-чата на сайтах
 * (круглая кнопка-бабл в углу экрана, по клику раскрывается компактное окно), но в
 * стилистике ЛК: тёмная шапка попапа (var(--color-ink)), оранжевая кнопка (var(--color-orange)),
 * те же скругления и тень, что у дропдауна уведомлений (.notif-panel-desktop в index.css).
 *
 * В отличие от обычных модалок (Modal.tsx, TopupModal) — без затемнения фона: страница
 * за окном чата остаётся видимой и кликабельной, как у привычных чат-виджетов (Intercom,
 * Crisp и т.п.). Одна и та же разметка на всех экранах — меняются только позиция и размеры
 * окна через медиа-запросы в index.css (мобильный — почти во весь экран, десктоп —
 * компактное окно у правого нижнего угла), поэтому используется только один iframe.
 *
 * Сам диалог — отдельное PHP-приложение (public/mojno-help), встраивается через iframe:
 * запросы внутри iframe идут на его собственный домен (см. VITE_CHAT_WIDGET_URL), поэтому
 * сессия/CSRF/история консультанта работают без CORS независимо от домена ЛК.
 *
 * iframe монтируется один раз при первом открытии и дальше не размонтируется (скрытая
 * панель просто уезжает за пределы экрана через transform/opacity), чтобы диалог не
 * сбрасывался при повторных открытиях и переходах между страницами ЛК — компонент
 * рендерится один раз в DashboardLayout и живёт вне <Outlet />.
 */
export function ChatWidget() {
    const [open, setOpen] = useState(false);
    const [mounted, setMounted] = useState(false);
    const [visible, setVisible] = useState(false);
    const [everOpened, setEverOpened] = useState(false);

    useEffect(() => {
        if (open) {
            setEverOpened(true);
            setMounted(true);
            let raf2 = 0;
            const raf1 = requestAnimationFrame(() => {
                raf2 = requestAnimationFrame(() => setVisible(true));
            });
            return () => {
                cancelAnimationFrame(raf1);
                cancelAnimationFrame(raf2);
            };
        }

        setVisible(false);
        const timeout = setTimeout(() => setMounted(false), 280);
        return () => clearTimeout(timeout);
    }, [open]);

    if (!CHAT_WIDGET_URL) {
        return null;
    }

    return (
        <>
            <button
                type="button"
                // is-open — на мобильных скрывает кнопку через CSS, пока открыта панель
                // (chat-panel): на мобильном её кнопка-крестик по позиции перекрывает
                // кнопку отправки сообщения внутри iframe-виджета (chat.js/chat.css в
                // public/mojno-help). Закрыть чат на мобильном можно через chat-panel-close
                // в шапке панели. На десктопе (см. index.css, @media 640px+) кнопка
                // перекрытия не создаёт и остаётся видимой как обычно.
                className={`chat-fab${open ? ' is-open' : ''}`}
                aria-label={open ? 'Закрыть чат с консультантом' : 'Открыть чат с консультантом'}
                onClick={() => setOpen((value) => !value)}
            >
                {open ? <XMarkIcon className="h-6 w-6" /> : <ChatBubbleLeftRightIcon className="h-6 w-6" />}
            </button>

            {mounted && (
                <div className={`chat-panel${visible ? ' is-visible' : ''}`} role="dialog" aria-label="Чат с консультантом">
                    <div className="chat-panel-header">
                        <div>
                            <p className="chat-panel-title">Консультант МОЖНО</p>
                            <p className="chat-panel-status">
                                <span className="chat-online-dot" />
                                Обычно отвечает быстро
                            </p>
                        </div>
                        <button
                            type="button"
                            className="icon-btn chat-panel-close"
                            onClick={() => setOpen(false)}
                            aria-label="Закрыть чат"
                        >
                            <XMarkIcon className="h-4 w-4" />
                        </button>
                    </div>
                    <div className="chat-panel-body">
                        {everOpened && (
                            <iframe
                                src={`${CHAT_WIDGET_URL}?embed=1`}
                                title="Консультант МОЖНО"
                                className="chat-iframe"
                                loading="lazy"
                            />
                        )}
                    </div>
                </div>
            )}
        </>
    );
}
