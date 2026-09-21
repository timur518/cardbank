import { useNavigate } from 'react-router-dom';
import type { AppNotification } from '../../api/types';
import { formatTime } from '../../utils/dateGroups';
import { NOTIFICATION_TYPE_ICON_CLASSES, NOTIFICATION_TYPE_ICONS } from '../../utils/labels';

const EXTERNAL_URL_RE = /^https?:\/\//i;

/** Одна строка в попапе уведомлений — кружок с иконкой по типу уведомления слева,
 * заголовок и текст справа. Кликабельна только если задан action_url (внешняя
 * ссылка открывается в новой вкладке, внутренний маршрут — через SPA-роутер). */
export function NotificationRow({ item, onNavigate }: { item: AppNotification; onNavigate: () => void }) {
    const navigate = useNavigate();
    const Icon = NOTIFICATION_TYPE_ICONS[item.type];
    const clickable = Boolean(item.action_url);

    function handleActivate() {
        if (!item.action_url) {
            return;
        }

        onNavigate();

        if (EXTERNAL_URL_RE.test(item.action_url)) {
            window.open(item.action_url, '_blank', 'noopener');
        } else {
            navigate(item.action_url);
        }
    }

    return (
        <div
            className={`notif-row${clickable ? ' notif-row-clickable' : ''}`}
            role={clickable ? 'button' : undefined}
            tabIndex={clickable ? 0 : undefined}
            onClick={clickable ? handleActivate : undefined}
            onKeyDown={
                clickable
                    ? (event) => {
                          if (event.key === 'Enter' || event.key === ' ') {
                              event.preventDefault();
                              handleActivate();
                          }
                      }
                    : undefined
            }
        >
            <span className={`notif-icon ${NOTIFICATION_TYPE_ICON_CLASSES[item.type]}`}>
                <Icon />
            </span>
            <span className="notif-info">
                <span className="notif-title">{item.title}</span>
                {item.body && <span className="notif-body">{item.body}</span>}
                <span className="notif-time">{formatTime(item.created_at)}</span>
            </span>
        </div>
    );
}
