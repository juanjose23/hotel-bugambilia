import { AlertDialog as AlertDialogPrimitive } from '@base-ui/react/alert-dialog';
import * as React from 'react';
import { cn } from '@/modulos/compartido/utilidades/clases';

const AlertDialog = AlertDialogPrimitive.Root;
const AlertDialogTrigger = AlertDialogPrimitive.Trigger;
const AlertDialogPortal = AlertDialogPrimitive.Portal;
const AlertDialogClose = AlertDialogPrimitive.Close;

const AlertDialogBackdrop = React.forwardRef<
    React.ElementRef<typeof AlertDialogPrimitive.Backdrop>,
    React.ComponentPropsWithoutRef<typeof AlertDialogPrimitive.Backdrop>
>(({ className, ...props }, ref) => (
    <AlertDialogPrimitive.Backdrop
        ref={ref}
        className={cn(
            'fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity data-[ending-style]:opacity-0 data-[starting-style]:opacity-0',
            className,
        )}
        {...props}
    />
));
AlertDialogBackdrop.displayName = 'AlertDialogBackdrop';

const AlertDialogPopup = React.forwardRef<
    React.ElementRef<typeof AlertDialogPrimitive.Popup>,
    React.ComponentPropsWithoutRef<typeof AlertDialogPrimitive.Popup>
>(({ className, ...props }, ref) => (
    <AlertDialogPortal>
        <AlertDialogBackdrop />
        <AlertDialogPrimitive.Popup
            ref={ref}
            className={cn(
                'fixed top-1/2 left-1/2 flex w-[calc(100vw-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 flex-col gap-4 rounded-lg border bg-background p-6 text-foreground shadow-lg transition-all outline-none data-[ending-style]:scale-95 data-[ending-style]:opacity-0 data-[starting-style]:scale-95 data-[starting-style]:opacity-0',
                className,
            )}
            {...props}
        />
    </AlertDialogPortal>
));
AlertDialogPopup.displayName = 'AlertDialogPopup';

const AlertDialogTitle = React.forwardRef<
    React.ElementRef<typeof AlertDialogPrimitive.Title>,
    React.ComponentPropsWithoutRef<typeof AlertDialogPrimitive.Title>
>(({ className, ...props }, ref) => (
    <AlertDialogPrimitive.Title
        ref={ref}
        className={cn('text-lg font-semibold', className)}
        {...props}
    />
));
AlertDialogTitle.displayName = 'AlertDialogTitle';

const AlertDialogDescription = React.forwardRef<
    React.ElementRef<typeof AlertDialogPrimitive.Description>,
    React.ComponentPropsWithoutRef<typeof AlertDialogPrimitive.Description>
>(({ className, ...props }, ref) => (
    <AlertDialogPrimitive.Description
        ref={ref}
        className={cn('text-sm text-muted-foreground', className)}
        {...props}
    />
));
AlertDialogDescription.displayName = 'AlertDialogDescription';

const AlertDialogActions = ({
    className,
    ...props
}: React.ComponentPropsWithoutRef<'div'>) => (
    <div
        className={cn(
            'flex flex-col-reverse gap-2 sm:flex-row sm:justify-end',
            className,
        )}
        {...props}
    />
);
AlertDialogActions.displayName = 'AlertDialogActions';

export {
    AlertDialog,
    AlertDialogActions,
    AlertDialogClose,
    AlertDialogDescription,
    AlertDialogPopup,
    AlertDialogTitle,
    AlertDialogTrigger,
};
