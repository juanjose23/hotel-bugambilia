import { Skeleton } from '@/modules/shared/components/ui/skeleton';

export const RoomCardSkeleton = () => {
    return (
        <div className="flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs">
            {/* Imagen Skeleton */}
            <Skeleton className="aspect-4/3 w-full rounded-none" />

            {/* Contenido Skeleton */}
            <div className="flex flex-1 flex-col justify-between space-y-4 p-5">
                <div className="space-y-2">
                    {/* Badge & Título */}
                    <div className="flex items-center justify-between">
                        <Skeleton className="h-4 w-24 rounded-full" />
                        <Skeleton className="h-4 w-12 rounded-md" />
                    </div>
                    <Skeleton className="h-5 w-3/4 rounded-md" />
                    <Skeleton className="h-3.5 w-full rounded-md" />
                    <Skeleton className="h-3.5 w-2/3 rounded-md" />
                </div>

                {/* Amenidades */}
                <div className="flex items-center gap-2 border-t border-border/60 pt-2">
                    <Skeleton className="size-6 rounded-full" />
                    <Skeleton className="size-6 rounded-full" />
                    <Skeleton className="size-6 rounded-full" />
                    <Skeleton className="ml-auto h-3 w-16 rounded-md" />
                </div>

                {/* Precio y Botón */}
                <div className="flex items-center justify-between pt-2">
                    <div className="space-y-1">
                        <Skeleton className="h-3 w-10 rounded-md" />
                        <Skeleton className="h-6 w-20 rounded-md" />
                    </div>
                    <Skeleton className="h-9 w-24 rounded-full" />
                </div>
            </div>
        </div>
    );
};

export const RoomGridSkeleton = ({ cantidad = 4 }: { cantidad?: number }) => {
    return (
        <div className="-mx-4 flex scrollbar-none gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:px-0 sm:pb-0 lg:grid-cols-3 xl:grid-cols-4">
            {Array.from({ length: cantidad }).map((_, i) => (
                <div
                    key={i}
                    className="w-[84vw] max-w-[340px] shrink-0 sm:w-auto sm:max-w-none sm:shrink"
                >
                    <RoomCardSkeleton />
                </div>
            ))}
        </div>
    );
};

export default RoomCardSkeleton;
