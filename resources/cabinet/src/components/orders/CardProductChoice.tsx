import { CheckIcon } from '@heroicons/react/24/outline';
import type { CardProduct } from '../../api/types';
import { formatRub } from '../../utils/format';

interface CardProductChoiceProps {
    product: CardProduct;
    onSelect: () => void;
}

/** Описание и преимущества карточных продуктов — тот же текст, что и в блоке
 * #products на лендинге (resources/views/welcome.blade.php). В CardProduct
 * такого текста нет (там только служебное поле description), поэтому копия
 * держится здесь локально и подбирается по product.key. */
const PRODUCT_COPY: Record<string, { desc: string; points: string[] }> = {
    black: {
        desc: 'Главная карта для онлайн платежей. Оплата ИИ-сервисов, облачных платформ, рекламы, подписок и зарубежных интернет-магазинов',
        points: ['Оформление за 3 минуты', 'Пополнение Российской картой или по СБП', 'Обслуживание - бесплатно'],
    },
    orange: {
        desc: 'Карта для жизни вне экрана. Добавляйте в Apple Pay и Google Pay, оплачивайте покупки телефоном или часами в кафе, ресторанах, отелях и магазинах.',
        points: ['Поддерживает Apple Pay и Google Pay', 'Можно платить в магазинах и кафе', 'Работает с Apple Watch и Wear OS'],
    },
    white: {
        desc: 'Карта для жизни вне экрана. Добавляйте в Apple Pay и Google Pay, оплачивайте покупки телефоном или часами в кафе, ресторанах, отелях и магазинах.',
        points: ['Поддерживает Apple Pay и Google Pay', 'Можно платить в магазинах и кафе', 'Работает с Apple Watch и Wear OS'],
    },
};

/** Карточка одного продукта на первом шаге оформления карты — визуал,
 * описание, преимущества и цена (как на лендинге), с кнопкой перехода
 * ко второму шагу (пополнение и оплата). */
export function CardProductChoice({ product, onSelect }: CardProductChoiceProps) {
    const copy = PRODUCT_COPY[product.key] ?? { desc: product.description ?? '', points: [] };

    return (
        <div className="flex flex-col rounded-[28px] bg-surface p-5 shadow-sm">
            {product.skin ? (
                <img src={product.skin} alt={`Карта ${product.name}`} className="w-full" />
            ) : (
                <div className="relative flex aspect-[1.586] w-full items-center justify-center overflow-hidden rounded-2xl bg-bg text-5xl font-black text-ink/10">
                    {product.name.charAt(0)}
                </div>
            )}

            <div className="mt-5 flex flex-1 flex-col">
                <div className="flex items-center gap-2">
                    <h3 className="text-base font-extrabold tracking-tight text-ink">Карта {product.name}</h3>
                    <span className="apply-card-badge">{product.currency}</span>
                    {product.coming_soon && <span className="apply-card-badge">Скоро</span>}
                </div>

                {copy.desc && <p className="mt-2 text-sm leading-relaxed text-muted">{copy.desc}</p>}

                {copy.points.length > 0 && (
                    <ul className="mt-4 flex flex-col gap-2">
                        {copy.points.map((point) => (
                            <li key={point} className="flex items-start gap-2 text-sm font-medium text-ink">
                                <CheckIcon className="mt-0.5 h-4 w-4 shrink-0 text-orange" />
                                {point}
                            </li>
                        ))}
                    </ul>
                )}

                <div className="mt-auto pt-5">
                    <p className="text-sm font-bold text-ink">{formatRub(Number(product.price_rub))} за выпуск</p>
                    <button
                        type="button"
                        className="btn btn-orange btn-block mt-3"
                        disabled={product.coming_soon}
                        onClick={onSelect}
                    >
                        {product.coming_soon ? 'Скоро' : 'Выбрать'}
                    </button>
                </div>
            </div>
        </div>
    );
}
