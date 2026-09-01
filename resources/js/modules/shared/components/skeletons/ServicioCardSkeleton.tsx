import { Skeleton } from '@/modules/shared/components/ui/skeleton';

export const ServicioCardSkeleton = () => {
    return (
        <div className="flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs">
            <Skeleton className="aspect-4/3 w-full rounded-none" />
            <div className="flex flex-1 flex-col justify-between space-y-3 p-4.5">
                <div className="space-y-2">
                    <div className="flex items-center gap-2">
                        <Skeleton className="size-7 rounded-lg" />
                        <Skeleton className="h-4 w-3/4 rounded-md" />
                    </div>
                    <Skeleton className="h-3 w-full rounded-md" />
                    <Skeleton className="h-3 w-4/5 rounded-md" />
                </div>
                <div className="flex items-center justify-between border-t border-border/60 pt-2.5">
                    <Skeleton className="h-4 w-16 rounded-md" />
                    <Skeleton className="h-3 w-14 rounded-md" />
                </div>
            </div>
        </div>
    );
};

export const ServicioGridSkeleton = ({
    cantidad = 3,
}: {
    cantidad?: number;
}) => {
    return (
        <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: cantidad }).map((_, i) => (
                <ServicioCardSkeleton key={i} />
            ))}
        </div>
    );
};

export const EspacioCardSkeleton = () => {
    return (
        <div className="flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs">
            <Skeleton className="aspect-4/3 w-full rounded-none" />
            <div className="flex flex-1 flex-col justify-between space-y-3 p-4.5">
                <div className="space-y-2">
                    <div className="flex items-center gap-2">
                        <Skeleton className="size-7 rounded-lg" />
                        <Skeleton className="h-4 w-3/4 rounded-md" />
                    </div>
                    <Skeleton className="h-3 w-full rounded-md" />
                    <Skeleton className="h-3 w-3/5 rounded-md" />
                </div>
                <div className="flex items-center justify-between border-t border-border/60 pt-2.5">
                    <Skeleton className="h-3 w-24 rounded-md" />
                    <Skeleton className="h-3 w-16 rounded-md" />
                </div>
            </div>
        </div>
    );
};

export const EspacioGridSkeleton = ({
    cantidad = 3,
}: {
    cantidad?: number;
}) => {
    return (
        <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: cantidad }).map((_, i) => (
                <EspacioCardSkeleton key={i} />
            ))}
        </div>
    );
};

export default ServicioCardSkeleton;
