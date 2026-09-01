import { Skeleton } from '@/modules/shared/components/ui/skeleton';

export const MisReservasCardSkeleton = () => {
    return (
        <div className="flex flex-col gap-6 rounded-3xl border border-border bg-card p-6 shadow-xs sm:p-8">
            <div className="flex flex-wrap items-center justify-between gap-4 border-b border-border/60 pb-4">
                <div className="space-y-1.5">
                    <Skeleton className="h-4 w-32 rounded-md" />
                    <Skeleton className="h-6 w-48 rounded-md" />
                </div>
                <Skeleton className="h-7 w-24 rounded-full" />
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div className="space-y-2">
                    <Skeleton className="h-3.5 w-20 rounded-md" />
                    <Skeleton className="h-5 w-36 rounded-md" />
                </div>
                <div className="space-y-2">
                    <Skeleton className="h-3.5 w-20 rounded-md" />
                    <Skeleton className="h-5 w-36 rounded-md" />
                </div>
                <div className="space-y-2">
                    <Skeleton className="h-3.5 w-20 rounded-md" />
                    <Skeleton className="h-6 w-28 rounded-md" />
                </div>
            </div>

            <div className="flex justify-end gap-3 pt-2">
                <Skeleton className="h-9 w-28 rounded-full" />
                <Skeleton className="h-9 w-32 rounded-full" />
            </div>
        </div>
    );
};

export const MisReservasGridSkeleton = ({
    cantidad = 2,
}: {
    cantidad?: number;
}) => {
    return (
        <div className="space-y-6">
            {Array.from({ length: cantidad }).map((_, i) => (
                <MisReservasCardSkeleton key={i} />
            ))}
        </div>
    );
};

export default MisReservasCardSkeleton;
