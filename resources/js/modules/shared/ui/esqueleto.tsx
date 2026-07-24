import { cn } from '@/modules/shared/utils/clases';
const Skeleton = ({
    className,
    ...props
}: React.HTMLAttributes<HTMLDivElement>) => {
    return (
        <div
            className={cn('animate-pulse rounded-md bg-muted', className)}
            {...props}
        />
    );
};
export { Skeleton };
