interface SkeletonProps {
    className?: string;
}

/**
 * Базовый блок скелетона загрузки — серый прямоугольник с бегущим бликом
 * (.skeleton в index.css). Размер/форма настраиваются снаружи через
 * className (обычно Tailwind h-*, w-*, rounded-*). Составные скелетоны ниже
 * собраны из этого блока по форме реальных карточек/строк, которые они
 * временно заменяют, — чтобы контент не «прыгал» после загрузки.
 */
export function Skeleton({ className = '' }: SkeletonProps) {
    return <span className={`skeleton rounded-md ${className}`} aria-hidden="true" />;
}

/** Список операций (TransactionsTable) — используется в CardTabsSection, DashboardPage,
 * TransactionsPage вместо «Загрузка…» на время первого запроса. */
export function TransactionsSkeleton({ rows = 5 }: { rows?: number }) {
    return (
        <div className="tx-list" aria-hidden="true">
            <div className="tx-group">
                {Array.from({ length: rows }).map((_, index) => (
                    <div key={index} className="tx-row">
                        <Skeleton className="h-10 w-10 shrink-0 rounded-full" />
                        <span className="tx-info">
                            <Skeleton className="h-3.5 w-32" />
                            <Skeleton className="h-3 w-20" />
                        </span>
                        <Skeleton className="h-4 w-16 shrink-0" />
                    </div>
                ))}
            </div>
        </div>
    );
}

/** Строки сайдбара «Мои карты» (CardListItem) — CardsSidebar на время загрузки списка. */
export function CardsListSkeleton({ rows = 3 }: { rows?: number }) {
    return (
        <div className="flex flex-col gap-3" aria-hidden="true">
            {Array.from({ length: rows }).map((_, index) => (
                <div key={index} className="flex items-center justify-between gap-3 rounded-2xl border border-border bg-surface px-3 py-3">
                    <div className="flex items-center gap-3">
                        <Skeleton className="h-9 w-14 shrink-0 rounded-lg" />
                        <Skeleton className="h-4 w-20" />
                    </div>
                    <div className="flex flex-col items-end gap-1.5">
                        <Skeleton className="h-4 w-16" />
                        <Skeleton className="h-3 w-12" />
                    </div>
                </div>
            ))}
        </div>
    );
}

/** Сетка карточек (CardGridCard) — CardsPage на время загрузки списка карт. */
export function CardsGridSkeleton({ items = 6 }: { items?: number }) {
    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-hidden="true">
            {Array.from({ length: items }).map((_, index) => (
                <div key={index} className="flex flex-col rounded-[28px] border border-border bg-surface p-4 shadow-sm">
                    <Skeleton className="aspect-[1.586] w-full rounded-2xl" />
                    <div className="mt-4 flex flex-col gap-3">
                        <div className="flex items-center justify-between gap-2">
                            <Skeleton className="h-4 w-28" />
                            <Skeleton className="h-5 w-16 rounded-full" />
                        </div>
                        <Skeleton className="h-3.5 w-36" />
                        <div className="flex items-center justify-between border-t border-border pt-3">
                            <Skeleton className="h-3.5 w-32" />
                            <Skeleton className="h-3.5 w-14" />
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}

/** Верхний блок страницы карты (CardFace + BalancePanel слева, RequisitesPanel
 * справа) — CardDetailPage на время загрузки самой карты. */
export function CardDetailSkeleton() {
    return (
        <div className="grid gap-6 lg:grid-cols-2" aria-hidden="true">
            <div className="flex flex-col gap-4">
                <Skeleton className="aspect-[1.586] w-full rounded-[28px]" />
                <div className="auth-panel flex flex-col gap-3 p-6">
                    <Skeleton className="h-3.5 w-28" />
                    <Skeleton className="h-8 w-40" />
                    <Skeleton className="mt-2 h-10 w-full rounded-full" />
                </div>
            </div>

            <div className="auth-panel flex flex-col p-6">
                <div className="mb-2 flex items-center gap-3">
                    <Skeleton className="h-11 w-11 shrink-0 rounded-full" />
                    <div className="flex flex-col gap-2">
                        <Skeleton className="h-4 w-32" />
                        <Skeleton className="h-3 w-40" />
                    </div>
                </div>
                {Array.from({ length: 4 }).map((_, index) => (
                    <div key={index} className="flex items-center justify-between gap-4 border-t border-border px-1 py-4 first:border-t-0">
                        <div className="flex flex-col gap-2">
                            <Skeleton className="h-3 w-24" />
                            <Skeleton className="h-4 w-32" />
                        </div>
                        <Skeleton className="h-8 w-8 shrink-0 rounded-lg" />
                    </div>
                ))}
            </div>
        </div>
    );
}

/** Список способов оплаты (PaymentMethodOption, apply-pay-option) — TopupModal
 * и NewCardOrderPage на время загрузки способов оплаты. */
export function PaymentMethodsSkeleton({ rows = 2 }: { rows?: number }) {
    return (
        <div className="apply-pay-list" aria-hidden="true">
            {Array.from({ length: rows }).map((_, index) => (
                <div key={index} className="apply-pay-option">
                    <Skeleton className="h-11 w-11 shrink-0 rounded-xl" />
                    <div className="flex flex-1 flex-col gap-2">
                        <Skeleton className="h-4 w-24" />
                        <Skeleton className="h-3 w-32" />
                    </div>
                </div>
            ))}
        </div>
    );
}

/** Список карточных продуктов (CardProductOption, apply-card-option) —
 * NewCardOrderPage на время загрузки каталога. */
export function CardProductsSkeleton({ items = 3 }: { items?: number }) {
    return (
        <div className="apply-card-list" aria-hidden="true">
            {Array.from({ length: items }).map((_, index) => (
                <div key={index} className="apply-card-option">
                    <Skeleton className="h-16 w-24 shrink-0 rounded-lg" />
                    <div className="flex flex-1 flex-col gap-2">
                        <Skeleton className="h-4 w-28" />
                        <Skeleton className="h-3 w-full" />
                        <Skeleton className="h-3.5 w-16" />
                    </div>
                </div>
            ))}
        </div>
    );
}

/** Список карт для выбора при пополнении (TopupEntryPage) — карточки со
 * структурой, идентичной обычной строке-ссылке на карту. */
export function TopupCardsSkeleton({ rows = 2 }: { rows?: number }) {
    return (
        <div className="flex flex-col gap-3" aria-hidden="true">
            {Array.from({ length: rows }).map((_, index) => (
                <div key={index} className="flex items-center justify-between gap-3 rounded-2xl border border-border bg-surface px-4 py-3">
                    <div className="flex items-center gap-3">
                        <Skeleton className="h-9 w-14 shrink-0 rounded-lg" />
                        <div className="flex flex-col gap-2">
                            <Skeleton className="h-4 w-28" />
                            <Skeleton className="h-3 w-16" />
                        </div>
                    </div>
                    <Skeleton className="h-4 w-16" />
                </div>
            ))}
        </div>
    );
}
