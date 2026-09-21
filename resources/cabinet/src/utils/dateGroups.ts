const MONTHS = [
    'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
    'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
];

function isSameDay(a: Date, b: Date): boolean {
    return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
}

/** Заголовок группы по дате («Сегодня»/«Вчера» для последних суток, «ДД месяц» для
 * остальных дат текущего года, «ДД месяц ГГГГ» — для дат прошлых лет). Общий для
 * списка операций (TransactionsTable) и попапа уведомлений (NotificationsPanel). */
export function dateGroupTitle(isoDate: string): string {
    const date = new Date(isoDate);
    const now = new Date();
    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);

    if (isSameDay(date, now)) {
        return 'Сегодня';
    }
    if (isSameDay(date, yesterday)) {
        return 'Вчера';
    }

    const dayMonth = `${date.getDate()} ${MONTHS[date.getMonth()]}`;
    return date.getFullYear() === now.getFullYear() ? dayMonth : `${dayMonth} ${date.getFullYear()}`;
}

export function formatTime(isoDate: string): string {
    return new Date(isoDate).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
}

export interface DateGroup<T> {
    title: string;
    items: T[];
}

/** Группирует список по дате (dateGroupTitle) — предполагает, что items уже
 * отсортированы по дате (по убыванию), группы формируются простым проходом:
 * новая группа открывается при смене заголовка у соседних элементов. */
export function groupByDate<T>(items: T[], getDate: (item: T) => string): DateGroup<T>[] {
    const groups: DateGroup<T>[] = [];

    for (const item of items) {
        const title = dateGroupTitle(getDate(item));
        const current = groups[groups.length - 1];

        if (current && current.title === title) {
            current.items.push(item);
        } else {
            groups.push({ title, items: [item] });
        }
    }

    return groups;
}
