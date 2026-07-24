import { usePropiedadesPagina } from './usePropiedadesPagina';
export const useAutenticacion = () => {
    const { auth } = usePropiedadesPagina();

    return auth;
};
