import type { CardProduct } from '../../api/types';
import { formatRub } from '../../utils/format';

interface CardProductOptionProps {
    product: CardProduct;
    selected: boolean;
    onSelect: () => void;
}

// Один пункт списка выбора карточного продукта на странице оформления заказа —
// повторяет вид блока выбора карты на лендинге (apply-card-option).
export function CardProductOption({ product, selected, onSelect }: CardProductOptionProps) {
    return (
        <label className={`apply-card-option ${selected ? 'is-active' : ''} ${product.coming_soon ? 'apply-card-disabled' : ''}`}>
            <input
                type="radio"
                name="card_product"
                className="sr-only"
                checked={selected}
                disabled={product.coming_soon}
                onChange={onSelect}
            />
            <span className={`apply-card-thumb ${product.skin ? '' : 'apply-card-thumb-white'}`}>
                {product.skin && <img src={product.skin} alt={`Карта ${product.name}`} loading="lazy" />}
            </span>
            <span className="apply-card-info">
                <span className="apply-card-name">
                    Карта {product.name}
                    <span className="apply-card-badge">{product.currency}</span>
                    {product.coming_soon && <span className="apply-card-badge">Скоро</span>}
                </span>
                {product.description && <span className="apply-card-desc">{product.description}</span>}
                <span className="apply-card-price">{formatRub(Number(product.price_rub))}</span>
            </span>
        </label>
    );
}
