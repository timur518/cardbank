import { useState } from 'react';

interface CopyButtonProps {
    /** Значение для копирования; если null (данные ещё не загружены/раскрыты), кнопка неактивна. */
    value: string | null;
    /** Текст на кнопке; без текста рендерится только иконка (для строк таблицы реквизитов). */
    label?: string;
}

/** Иконка/кнопка копирования значения в буфер обмена с кратковременной обратной связью «Скопировано». */
export function CopyButton({ value, label }: CopyButtonProps) {
    const [copied, setCopied] = useState(false);

    async function handleClick() {
        if (!value) {
            return;
        }

        await navigator.clipboard.writeText(value);
        setCopied(true);
        setTimeout(() => setCopied(false), 1500);
    }

    if (label) {
        return (
            <button type="button" className="btn" disabled={!value} onClick={handleClick}>
                <CopyIcon />
                {copied ? 'Скопировано' : label}
            </button>
        );
    }

    return (
        <button
            type="button"
            className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-muted transition hover:bg-bg hover:text-ink disabled:opacity-40"
            disabled={!value}
            onClick={handleClick}
            title={copied ? 'Скопировано' : 'Скопировать'}
        >
            {copied ? <CheckIcon /> : <CopyIcon />}
        </button>
    );
}

function CopyIcon() {
    return (
        <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <rect x="9" y="9" width="12" height="12" rx="2" />
            <path strokeLinecap="round" d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1" />
        </svg>
    );
}

function CheckIcon() {
    return (
        <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
        </svg>
    );
}
