import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

interface FlashProps {
    exito?: string;
    success?: string;
    error?: string;
}

export function NotificacionesFlash() {
    const { props } = usePage();
    const flash = props.flash as FlashProps | undefined;
    const mensajeExito = flash?.exito || flash?.success;
    const mensajeError = flash?.error;

    useEffect(() => {
        if (mensajeExito) {
            toast.success(mensajeExito);
        }
    }, [mensajeExito]);

    useEffect(() => {
        if (mensajeError) {
            toast.error(mensajeError);
        }
    }, [mensajeError]);

    return null;
}
