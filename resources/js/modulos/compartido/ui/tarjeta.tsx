import * as React from 'react';
import { cn } from '@/modulos/compartido/utilidades/clases';

const Card = ({
    className,
    size = 'default',
    ...props
}: React.ComponentProps<'div'> & {
    size?: 'default' | 'sm';
}) => {
    return (
        <div
            data-slot="card"
            data-size={size}
            className={cn(
                'group/card flex flex-col gap-4 overflow-hidden rounded-3xl border border-border/60 bg-card p-6 text-card-foreground shadow-xs transition-all duration-300 hover:border-border hover:shadow-md data-[size=sm]:p-4',
                className,
            )}
            {...props}
        />
    );
};
Card.displayName = 'Card';

const CardHeader = ({ className, ...props }: React.ComponentProps<'div'>) => {
    return (
        <div
            data-slot="card-header"
            className={cn('flex flex-col gap-1.5', className)}
            {...props}
        />
    );
};
CardHeader.displayName = 'CardHeader';

const CardTitle = ({ className, ...props }: React.ComponentProps<'div'>) => {
    return (
        <div
            data-slot="card-title"
            className={cn(
                'text-lg leading-snug font-bold tracking-tight text-foreground',
                className,
            )}
            {...props}
        />
    );
};
CardTitle.displayName = 'CardTitle';

const CardDescription = ({
    className,
    ...props
}: React.ComponentProps<'div'>) => {
    return (
        <div
            data-slot="card-description"
            className={cn(
                'text-xs leading-normal text-muted-foreground',
                className,
            )}
            {...props}
        />
    );
};
CardDescription.displayName = 'CardDescription';

const CardAction = ({ className, ...props }: React.ComponentProps<'div'>) => {
    return (
        <div
            data-slot="card-action"
            className={cn(
                'col-start-2 row-span-2 row-start-1 self-start justify-self-end',
                className,
            )}
            {...props}
        />
    );
};
CardAction.displayName = 'CardAction';

const CardContent = ({ className, ...props }: React.ComponentProps<'div'>) => {
    return (
        <div
            data-slot="card-content"
            className={cn('flex flex-col gap-3', className)}
            {...props}
        />
    );
};
CardContent.displayName = 'CardContent';

const CardFooter = ({ className, ...props }: React.ComponentProps<'div'>) => {
    return (
        <div
            data-slot="card-footer"
            className={cn(
                'mt-2 flex items-center justify-between border-t border-border/40 pt-4',
                className,
            )}
            {...props}
        />
    );
};
CardFooter.displayName = 'CardFooter';

// Aliases en español
export {
    Card,
    CardHeader,
    CardFooter,
    CardTitle,
    CardAction,
    CardDescription,
    CardContent,
    Card as Tarjeta,
    CardHeader as TarjetaCabecera,
    CardFooter as TarjetaPie,
    CardTitle as TarjetaTitulo,
    CardAction as TarjetaAccion,
    CardDescription as TarjetaDescripcion,
    CardContent as TarjetaContenido,
};
