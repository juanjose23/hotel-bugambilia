import { Skeleton } from '@/modules/shared/components/ui/skeleton';

export const PromocionCardSkeleton = () => {
    return (
        <div className="flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs">
            {/* Imagen Skeleton */}
            <Skeleton className="aspect-16/10 w-full rounded-none" />

            {/* Contenido Skeleton */}
            <div className="flex flex-1 flex-col justify-between space-y-4 p-5">
                <div className="space-y-2.5">
                    <div className="flex items-center justify-between">
                        <Skeleton className="h-4 w-24 rounded-full" />
                        <Skeleton className="h-4 w-16 rounded-md" />
                    </div>
                    <Skeleton className="h-5 w-3/4 rounded-md" />
                    <Skeleton className="h-3.5 w-full rounded-md" />
                    <Skeleton className="h-3.5 w-4/5 rounded-md" />
                </div>

                {/* Beneficios Skeleton */}
                <div className="space-y-2 border-t border-border/60 pt-3">
                    <Skeleton className="h-3.5 w-2/3 rounded-md" />
                    <Skeleton className="h-3.5 w-1/2 rounded-md" />
                </div>

                {/* Precio y Botón */}
                <div className="flex items-center justify-between pt-2">
                    <div className="space-y-1">
                        <Skeleton className="h-3 w-12 rounded-md" />
                        <Skeleton className="h-6 w-20 rounded-md" />
                    </div>
                    <Skeleton className="h-9 w-28 rounded-full" />
                </div>
            </div>
        </div>
    );
};

export const PromocionGridSkeleton = ({
    cantidad = 3,
}: {
    cantidad?: number;
}) => {
    return (
        <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: cantidad }).map((_, i) => (
                <PromocionCardSkeleton key={i} />
            ))}
        </div>
    );
};

export default PromocionCardSkeleton;
