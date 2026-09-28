import type { CardProduct } from '../../api/types';
import { NetworkBadge } from '../common/NetworkBadge';
import { formatRub } from '../../utils/format';

interface CardProductChoiceProps {
    product: CardProduct;
    onSelect: () => void;
}

/** Карточка одного продукта на первом шаге оформления карты — визуал, описание
 * и преимущества из админки (CardProduct.description и CardProduct.advantages),
 * цена, с кнопкой перехода ко второму шагу (пополнение и оплата). */
export function CardProductChoice({ product, onSelect }: CardProductChoiceProps) {
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
                <div className="flex flex-wrap items-center gap-2">
                    <h3 className="text-base font-extrabold tracking-tight text-ink">Карта {product.name}</h3>
                    <span className="apply-card-badge">{product.currency}</span>
                    <NetworkBadge network={product.network} />
                    {product.card_country_flag_url && (
                        <img
                            src={product.card_country_flag_url}
                            alt={product.card_country_label ?? ''}
                            title={product.card_country_label ?? undefined}
                            className="h-4 w-4 shrink-0 rounded-full object-cover"
                        />
                    )}
                    {product.coming_soon && <span className="apply-card-badge">Скоро</span>}
                </div>

                {product.description && <p className="mt-2 text-sm leading-relaxed text-muted">{product.description}</p>}

                {product.advantages && (
                    <div className="advantages-list mt-4" dangerouslySetInnerHTML={{ __html: product.advantages }} />
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
