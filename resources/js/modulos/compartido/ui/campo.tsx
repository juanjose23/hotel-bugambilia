import * as React from 'react';
import { cn } from '@/modulos/compartido/utilidades/clases';

const CampoGrupo = React.forwardRef<
    HTMLDivElement,
    React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
    <div
        ref={ref}
        className={cn('flex flex-col gap-4', className)}
        {...props}
    />
));
CampoGrupo.displayName = 'CampoGrupo';

const Campo = React.forwardRef<
    HTMLDivElement,
    React.HTMLAttributes<HTMLDivElement> & { 'data-invalid'?: boolean }
>(({ className, 'data-invalid': isInvalid, ...props }, ref) => (
    <div
        ref={ref}
        data-invalid={isInvalid}
        className={cn('flex flex-col gap-1.5', className)}
        {...props}
    />
));
Campo.displayName = 'Campo';

const EtiquetaCampo = React.forwardRef<
    HTMLLabelElement,
    React.LabelHTMLAttributes<HTMLLabelElement>
>(({ className, ...props }, ref) => (
    <label
        ref={ref}
        className={cn(
            'text-xs font-semibold tracking-tight text-foreground',
            className,
        )}
        {...props}
    />
));
EtiquetaCampo.displayName = 'EtiquetaCampo';

const DescripcionCampo = React.forwardRef<
    HTMLParagraphElement,
    React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
    <p
        ref={ref}
        className={cn(
            'text-[11px] leading-normal text-muted-foreground',
            className,
        )}
        {...props}
    />
));
DescripcionCampo.displayName = 'DescripcionCampo';

export { CampoGrupo, Campo, EtiquetaCampo, DescripcionCampo };
