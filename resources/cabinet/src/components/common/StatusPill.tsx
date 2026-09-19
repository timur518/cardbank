export type StatusTone = 'success' | 'warning' | 'danger' | 'gray';

interface StatusPillProps {
    label: string;
    tone: StatusTone;
}

export function StatusPill({ label, tone }: StatusPillProps) {
    return <span className={`status-pill status-pill-${tone}`}>{label}</span>;
}
