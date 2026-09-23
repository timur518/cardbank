interface CardThumbnailProps {
    skin: string | null;
    name: string;
}

// Мини-превью карточного продукта: реальный скин, если он загружен в админке
// (CardProductForm), иначе — тёмная заглушка с названием продукта.
export function CardThumbnail({ skin, name }: CardThumbnailProps) {
    if (skin) {
        return (
            <img
                src={skin}
                alt={name}
                className="h-9 w-14 shrink-0 rounded-lg object-cover shadow-sm"
            />
        );
    }

    return (
        <div className="flex h-9 w-14 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#2b2a28] to-[#3A3C40] text-[9px] font-bold uppercase tracking-wide text-white/70 shadow-sm">
            {name.slice(0, 3)}
        </div>
    );
}
