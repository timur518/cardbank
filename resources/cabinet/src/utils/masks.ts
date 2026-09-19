// Маски полей ввода — портированы из resources/js/app.js (форма заявки на карте на
// лендинге), чтобы поведение совпадало в обоих местах.

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
