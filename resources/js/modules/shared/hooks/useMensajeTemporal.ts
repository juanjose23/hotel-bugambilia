import { useState, useEffect, useRef } from 'react';
import { usePropiedadesPagina } from './usePropiedadesPagina';
export const useMensajeTemporal = (tiempoDesaparecer = 5000) => {
    const { flash } = usePropiedadesPagina();
    const [oculto, setOculto] = useState(false);
    const anteriorFlash = useRef(flash?.exito);
    useEffect(() => {
        if (flash?.exito !== anteriorFlash.current) {
            anteriorFlash.current = flash?.exito;
            setOculto(false);
        }

        if (!tiempoDesaparecer || !flash?.exito) {
            return;
        }

        const temporizador = setTimeout(
            () => setOculto(true),
            tiempoDesaparecer,
        );

        return () => clearTimeout(temporizador);
    }, [flash, tiempoDesaparecer]);
    const visible = !oculto && !!flash?.exito;

    return { flash, visible, ocultar: () => setOculto(true) };
};
