import { useEffect } from 'react';
import { toast, Toaster } from 'sonner';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';

export const Toast = () => {
    const { flash } = usePropiedadesPagina();

    useEffect(() => {
        if (!flash) {
            return;
        }

        const mensajeExito = flash.exito || flash.success;

        if (mensajeExito) {
            toast.success(mensajeExito);
        }

        if (flash.error) {
            toast.error(flash.error);
        }

        if (flash.warning) {
            toast.warning(flash.warning);
        }

        if (flash.info) {
            toast.info(flash.info);
        }
    }, [flash]);

    return <Toaster richColors position="top-right" />;
};

export default Toast;
