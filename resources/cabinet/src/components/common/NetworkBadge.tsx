/** Русские названия платёжных систем зеркалят App\Enums\CardNetwork на бэкенде
 * (значения network в API карточных продуктов/карт: mc, visa, mir, unionpay, amex). */
const WORDMARK_LABELS: Record<string, string> = {
    visa: 'VISA',
    mir: 'МИР',
    unionpay: 'UnionPay',
    amex: 'AMEX',
};

const WORDMARK_COLORS: Record<string, string> = {
    visa: '#1a1f71',
    mir: '#0f754e',
    unionpay: '#e21836',
    amex: '#2e77bc',
};

interface NetworkBadgeProps {
    network: string | null | undefined;
    className?: string;
    /** brand — фирменные цвета платёжной системы (светлый фон, форма выбора карты,
     * сетка «Мои карты»); mono — светлый монохромный вариант поверх тёмного визуала
     * карты (CardVisual). Кружки Mastercard остаются в фирменных цветах в обоих
     * случаях — они узнаваемы и на тёмном фоне так же, как на пластике настоящей карты. */
    tone?: 'brand' | 'mono';
}

/** Значок платёжной системы (Mastercard/Visa/МИР/UnionPay/Amex) — упрощённый
 * знак вместо официального логотипа: два кружка для Mastercard, курсивный
 * вордмарк для остальных сетей. */
export function NetworkBadge({ network, className = '', tone = 'brand' }: NetworkBadgeProps) {
    if (!network) {
        return null;
    }

    if (network === 'mc') {
        return (
            <span className={`relative inline-flex h-4 w-6 shrink-0 ${className}`} title="Mastercard">
                <span className="absolute left-0 h-4 w-4 rounded-full bg-[#eb001b]" />
                <span className="absolute left-2.5 h-4 w-4 rounded-full bg-[#ff5f00] opacity-90" />
            </span>
        );
    }

    const label = WORDMARK_LABELS[network];

    if (!label) {
        return null;
    }

    return (
        <span
            className={`shrink-0 text-xs leading-none font-black tracking-tight italic ${className}`}
            style={{ color: tone === 'mono' ? 'rgba(255,255,255,.92)' : WORDMARK_COLORS[network] }}
            title={label}
        >
            {label}
        </span>
    );
}
