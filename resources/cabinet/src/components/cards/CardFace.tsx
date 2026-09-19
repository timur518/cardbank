export type CardSide = 'front' | 'back';

interface CardFaceProps {
    side: CardSide;
    productName: string;
    subtitle: string | null;
    maskedNumber: string;
    fullNumber: string | null;
    revealed: boolean;
    expiry: string | null;
    cardholderName: string;
    cvv: string | null;
    showCvv: boolean;
}

// Крупная лицевая/оборотная сторона карты на странице информации о карте — в
// отличие от CardThumbnail (маленькое превью в списках) здесь всегда рендерится
// программно (не картинка card_product.skin — это маркетинговое превью со
// случайным номером на фото, для реальных реквизитов не подходит).
export function CardFace({
    side,
    productName,
    subtitle,
    maskedNumber,
    fullNumber,
    revealed,
    expiry,
    cardholderName,
    cvv,
    showCvv,
}: CardFaceProps) {
    const displayNumber = revealed && fullNumber ? fullNumber : maskedNumber;

    return (
        <div className="relative aspect-[1.586] w-full overflow-hidden rounded-[28px] bg-gradient-to-br from-[#2b2a28] via-[#1c1b19] to-[#0e0e0d] p-6 text-white shadow-xl sm:p-7">
            {/* Декоративный водяной знак — первая буква названия продукта. */}
            <span className="pointer-events-none absolute -right-6 -top-10 text-[13rem] leading-none font-black text-white/5 select-none">
                {productName.charAt(0)}
            </span>

            {side === 'front' ? (
                <div className="relative flex h-full flex-col justify-between">
                    <div className="flex items-center gap-3">
                        <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-orange text-sm font-extrabold text-white">
                            {productName.charAt(0)}
                        </span>
                        <div>
                            <p className="text-sm font-bold">Карта {productName}</p>
                            {subtitle && <p className="text-xs text-white/50">{subtitle}</p>}
                        </div>
                    </div>

                    <div>
                        <ChipIcon />
                        <p className="mt-3 font-mono text-lg tracking-[0.15em] sm:text-2xl">{displayNumber}</p>
                    </div>

                    <div className="flex items-end justify-between">
                        <div>
                            <p className="text-[10px] font-semibold tracking-widest text-white/45">CARDHOLDER</p>
                            <p className="text-sm font-semibold tracking-wide">{cardholderName}</p>
                        </div>
                        <div className="text-right">
                            <p className="text-[10px] font-semibold tracking-widest text-white/45">VALID THRU</p>
                            <p className="font-mono text-sm">{expiry ?? '—/—'}</p>
                        </div>
                        <NetworkMark />
                    </div>
                </div>
            ) : (
                <div className="relative flex h-full flex-col">
                    <div className="-mx-6 mt-2 h-11 bg-black sm:-mx-7" />

                    <div className="mt-6 flex items-center gap-3 rounded bg-white/90 px-3 py-2">
                        <span className="flex-1 font-mono text-sm tracking-widest text-ink italic">{cardholderName}</span>
                        <span className="font-mono text-sm font-bold text-ink">{showCvv ? (cvv ?? '···') : '···'}</span>
                    </div>
                    <p className="mt-2 text-[10px] text-white/40">Код проверки подлинности (CVV) — на полосе для подписи.</p>

                    <div className="mt-auto flex items-center justify-between">
                        <p className="font-mono text-xs tracking-widest text-white/50">{maskedNumber}</p>
                        <NetworkMark />
                    </div>
                </div>
            )}
        </div>
    );
}

function ChipIcon() {
    return (
        <div className="grid h-8 w-10 grid-cols-3 grid-rows-3 gap-px overflow-hidden rounded-md bg-gradient-to-br from-yellow-200 to-yellow-500">
            {Array.from({ length: 9 }).map((_, index) => (
                <span key={index} className="bg-yellow-600/30" />
            ))}
        </div>
    );
}

// Декоративная метка платёжной сети — у CardProduct нет поля с конкретной сетью
// (Visa/Mastercard), поэтому используется нейтральный фирменный знак, а не чужой товарный знак.
function NetworkMark() {
    return (
        <div className="flex -space-x-2">
            <span className="h-6 w-6 rounded-full bg-white/25" />
            <span className="h-6 w-6 rounded-full bg-orange/80" />
        </div>
    );
}
