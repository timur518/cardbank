import { CheckIcon, DocumentDuplicateIcon } from '@heroicons/react/24/outline';
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
                <DocumentDuplicateIcon className="h-4 w-4" />
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
            {copied ? <CheckIcon className="h-4 w-4" /> : <DocumentDuplicateIcon className="h-4 w-4" />}
        </button>
    );
}
