import { InformationCircleIcon } from '@heroicons/react/24/outline';

interface InfoTooltipProps {
    text: string;
}

/** Кнопка-подсказка: по наведению показывает всплывающий тултип с текстом справа от курсора. */
export function InfoTooltip({ text }: InfoTooltipProps) {
    return (
        <span className="info-tooltip">
            <button type="button" className="info-tooltip-trigger" tabIndex={-1} aria-label="Подсказка">
                <InformationCircleIcon className="h-[18px] w-[18px]" />
            </button>
            <span className="info-tooltip-bubble" role="tooltip">
                {text}
            </span>
        </span>
    );
}
