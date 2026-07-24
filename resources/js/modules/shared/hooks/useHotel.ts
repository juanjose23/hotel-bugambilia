import { usePropiedadesPagina } from './usePropiedadesPagina';
export const useHotel = () => {
    const { hotel } = usePropiedadesPagina();

    return hotel;
};
