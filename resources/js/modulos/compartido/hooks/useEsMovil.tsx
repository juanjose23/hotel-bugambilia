import { useState, useEffect } from 'react';
const PUNTO_RUPTURA_MOVIL = 768;
export const useEsMovil = () => {
    const [esMovil, setEsMovil] = useState(() => {
        if (typeof window === 'undefined') {
            return false;
        }

        return window.innerWidth < PUNTO_RUPTURA_MOVIL;
    });
    useEffect(() => {
        const mediaQuery = window.matchMedia(
            `(max-width: ${PUNTO_RUPTURA_MOVIL - 1}px)`,
        );
        const onChange = () =>
            setEsMovil(window.innerWidth < PUNTO_RUPTURA_MOVIL);
        mediaQuery.addEventListener('change', onChange);

        return () => mediaQuery.removeEventListener('change', onChange);
    }, []);

    return esMovil;
};
