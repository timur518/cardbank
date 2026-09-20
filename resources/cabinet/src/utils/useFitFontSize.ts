import { useLayoutEffect, useRef } from 'react';

// Шаг уменьшения кегля в пикселях на каждой итерации подбора.
const STEP_PX = 1;

/**
 * Хук для «крупной цифры» вроде текущего остатка: заранее неизвестно, сколько
 * символов будет в сумме (курс, валюта, количество карт), поэтому кегль не
 * фиксируется одним значением — он подбирается по фактической ширине
 * отрендеренного текста относительно ширины родителя, пока строка не
 * перестанет переноситься/вылезать за его пределы. В отличие от заранее
 * заданных констант (как было со скользящим индикатором нижнего меню), здесь
 * ничего не рассинхронизируется при изменении вёрстки — размер всегда
 * пересчитывается от реальных пикселей на экране.
 *
 * Возвращает ref, который нужно повесить на элемент с текстом; сам текст и
 * границы min/max кегля (в px) передаются явно и являются зависимостями
 * пересчёта.
 */
export function useFitFontSize(text: string, maxPx: number, minPx: number) {
    const ref = useRef<HTMLSpanElement | null>(null);

    useLayoutEffect(() => {
        const el = ref.current;
        if (!el || !el.parentElement) {
            return;
        }

        function fit() {
            if (!el || !el.parentElement) {
                return;
            }

            let size = maxPx;
            el.style.fontSize = `${size}px`;

            while (size > minPx && el.scrollWidth > el.parentElement.clientWidth) {
                size -= STEP_PX;
                el.style.fontSize = `${size}px`;
            }
        }

        fit();
        window.addEventListener('resize', fit);
        return () => window.removeEventListener('resize', fit);
    }, [text, maxPx, minPx]);

    return ref;
}
