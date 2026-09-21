import { ArrowPathIcon } from '@heroicons/react/24/outline';

export type StatusTone = 'success' | 'warning' | 'danger' | 'gray';

interface StatusPillProps {
    label: string;
    tone: StatusTone;
    /** Крутящаяся иконка перед текстом — для статусов, ожидающих обработки (например, «В процессе выпуска»). */
    loading?: boolean;
}

export function StatusPill({ label, tone, loading }: StatusPillProps) {
    return (
        <span className={`status-pill status-pill-${tone}`}>
            {loading && <ArrowPathIcon className="h-3 w-3 animate-spin" />}
            {label}
        </span>
    );
}
