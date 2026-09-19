interface CardArtworkProps {
    skin: string | null;
    productName: string;
    last4: string | null;
}

// Крупное превью карты для страницы информации о карте: реальный скин продукта,
// если он загружен в админке, иначе — тёмная заглушка с названием продукта и
// маской номера (стандартное соотношение сторон банковской карты — 1.586:1).
export function CardArtwork({ skin, productName, last4 }: CardArtworkProps) {
    if (skin) {
        return (
            <img
                src={skin}
                alt={`Карта ${productName}`}
                className="aspect-[1.586] w-full max-w-[300px] shrink-0 rounded-2xl object-cover shadow-lg"
            />
        );
    }

    return (
        <div className="relative aspect-[1.586] w-full max-w-[300px] shrink-0 rounded-2xl bg-gradient-to-br from-[#2b2a28] to-[#151515] p-5 text-white shadow-lg">
            <p className="text-sm font-bold uppercase tracking-wide opacity-70">{productName}</p>
            <p className="absolute bottom-5 left-5 text-lg font-semibold tracking-widest">
                •••• •••• •••• {last4 ?? '••••'}
            </p>
        </div>
    );
}
