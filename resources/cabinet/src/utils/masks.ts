// Маски полей ввода — портированы из resources/js/app.js (форма заявки на карте на
// лендинге), чтобы поведение совпадало в обоих местах.

const CYRILLIC_TO_LATIN: Record<string, string> = {
    а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'e', ж: 'zh', з: 'z',
    и: 'i', й: 'y', к: 'k', л: 'l', м: 'm', н: 'n', о: 'o', п: 'p', р: 'r',
    с: 's', т: 't', у: 'u', ф: 'f', х: 'kh', ц: 'ts', ч: 'ch', ш: 'sh',
    щ: 'shch', ъ: '', ы: 'y', ь: '', э: 'e', ю: 'yu', я: 'ya',
};

/**
 * Транслитерация ФИО кириллица → латиница в реальном времени — портирована из
 * resources/js/app.js (форма заявки на лендинге), поведение идентично: первая
 * буква транслитерированного блока наследует регистр исходной буквы (Х → Kh, а
 * не KH), остальные символы — в нижнем регистре.
 */
export function transliterateFio(value: string): string {
    return value.replace(/[а-яёА-ЯЁ]/g, (char) => {
        const lower = char.toLowerCase();
        const mapped = CYRILLIC_TO_LATIN[lower];

        if (mapped === undefined) {
            return char;
        }

        if (char === lower) {
            return mapped;
        }

        return mapped.charAt(0).toUpperCase() + mapped.slice(1);
    });
}

export interface SplitFio {
    last_name: string;
    first_name: string;
    middle_name: string;
}

/**
 * Разбивает одно поле ФИО (в порядке "Фамилия Имя Отчество", как на лендинге,
 * placeholder "Ivanov Ivan Ivanovich") на три части для API — RegisterRequest
 * ждёт first_name/last_name/middle_name отдельными полями.
 */
export function splitFio(fio: string): SplitFio {
    const parts = fio.trim().split(/\s+/).filter(Boolean);

    return {
        last_name: parts[0] ?? '',
        first_name: parts[1] ?? '',
        middle_name: parts.slice(2).join(' '),
    };
}

/**
 * Обратная операция к splitFio() — собирает фамилию/имя/отчество (как отдаёт
 * UserResource) обратно в одно поле в том же порядке «Фамилия Имя Отчество» — для
 * поля ФИО на странице профиля.
 */
export function joinFio(parts: { first_name: string; last_name: string; middle_name: string | null }): string {
    return [parts.last_name, parts.first_name, parts.middle_name].filter(Boolean).join(' ');
}

/** Маска российского мобильного телефона: +7 (999) 123-45-67. */
export function formatPhoneMask(value: string): string {
    let digits = value.replace(/\D/g, '');

    if (!digits) {
        return '';
    }

    if (digits.startsWith('8')) {
        digits = `7${digits.slice(1)}`;
    } else if (!digits.startsWith('7')) {
        digits = `7${digits}`;
    }

    digits = digits.slice(0, 11);

    let formatted = '+7';

    if (digits.length > 1) {
        formatted += ` (${digits.slice(1, 4)}`;
    }
    if (digits.length >= 4) {
        formatted += ')';
    }
    if (digits.length >= 5) {
        formatted += ` ${digits.slice(4, 7)}`;
    }
    if (digits.length >= 8) {
        formatted += `-${digits.slice(7, 9)}`;
    }
    if (digits.length >= 10) {
        formatted += `-${digits.slice(9, 11)}`;
    }

    return formatted;
}

/** "1990-05-20" (как отдаёт UserResource) -> "20.05.1990" (формат маски поля ввода даты рождения). */
export function isoDateToDisplay(iso: string | null): string {
    if (!iso) {
        return '';
    }

    const [year, month, day] = iso.split('-');
    return `${day}.${month}.${year}`;
}

/** Маска даты рождения: дд.мм.гггг — именно в этом формате её ждёт RegisterRequest. */
export function formatDateMask(value: string): string {
    const digits = value.replace(/\D/g, '').slice(0, 8);

    let formatted = digits.slice(0, 2);

    if (digits.length > 2) {
        formatted += `.${digits.slice(2, 4)}`;
    }
    if (digits.length > 4) {
        formatted += `.${digits.slice(4, 8)}`;
    }

    return formatted;
}
