interface CardFaceProps {
    flipped: boolean;
    onFlip: () => void;
    productName: string;
    subtitle: string | null;
    maskedNumber: string;
    fullNumber: string | null;
    expiry: string | null;
    cardholderName: string;
    cvv: string | null;
    showCvv: boolean;
}

// Крупная лицевая/оборотная сторона карты на странице информации о карте — в
// отличие от CardThumbnail (маленькое превью в списках) здесь всегда рендерится
// программно (не картинка card_product.skin — это маркетинговое превью со
// случайным номером на фото, для реальных реквизитов не подходит). Обе стороны
// существуют в DOM одновременно и развёрнуты в 3D относительно друг друга
// (card-flip-* классы в index.css) — переворот кнопкой-иконкой выглядит как
// вращение настоящей карты, а не смена картинки.
export function CardFace({
    flipped,
    onFlip,
    productName,
    subtitle,
    maskedNumber,
    fullNumber,
    expiry,
    cardholderName,
    cvv,
    showCvv,
}: CardFaceProps) {
    // Полный номер подгружается автоматически при открытии страницы (без отдельной
    // кнопки-гейта) — пока он не загружен, показывается маска.
    const displayNumber = fullNumber ?? maskedNumber;

    return (
        <div className="card-flip-scene aspect-[1.586] w-full">
            <div className={`card-flip-inner h-full w-full${flipped ? ' is-flipped' : ''}`}>
                <div className="card-flip-face relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#2b2a28] via-[#1c1b19] to-[#0e0e0d] p-6 text-white shadow-xl sm:p-7">
                    <span className="pointer-events-none absolute -right-6 -top-10 text-[13rem] leading-none font-black text-white/5 select-none">
                        {productName.charAt(0)}
                    </span>

                    <FlipButton onClick={onFlip} />

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
                </div>

                <div className="card-flip-face card-flip-face-back relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#2b2a28] via-[#1c1b19] to-[#0e0e0d] p-6 text-white shadow-xl sm:p-7">
                    <span className="pointer-events-none absolute -right-6 -top-10 text-[13rem] leading-none font-black text-white/5 select-none">
                        {productName.charAt(0)}
                    </span>

                    <FlipButton onClick={onFlip} />

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
                </div>
            </div>
        </div>
    );
}

function FlipButton({ onClick }: { onClick: () => void }) {
    return (
        <button
            type="button"
            onClick={onClick}
            title="Перевернуть карту"
            className="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur-sm transition hover:bg-white/20"
        >
            <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M17.5 9.5a6 6 0 1 0-1.5 6.5" />
                <path strokeLinecap="round" strokeLinejoin="round" d="M17.5 4.5v5h-5" />
            </svg>
        </button>
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
