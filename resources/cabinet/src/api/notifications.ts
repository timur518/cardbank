import { apiClient } from './client';
import type { AppNotification, Paginated } from './types';

// Список уведомлений опрашивается фоном (см. hooks/useNotifications.ts) — по
// умолчанию отдаём последние 20, этого достаточно для попапа без пагинации.
export async function fetchNotifications(perPage = 20): Promise<Paginated<AppNotification>> {
    const { data } = await apiClient.get<Paginated<AppNotification>>('/notifications', {
        params: { per_page: perPage },
    });
    return data;
}

// Отмечает все уведомления пользователя прочитанными — вызывается при открытии попапа.
export async function markNotificationsRead(): Promise<void> {
    await apiClient.post('/notifications/mark-read');
}
