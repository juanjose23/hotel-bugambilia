import { useState } from 'react';
import type { CarouselApi } from '@/modules/shared/components/ui/carousel';

export const useCarouselGaleria = () => {
    const [api, setApi] = useState<CarouselApi>();
    const [actual, setActual] = useState(0);

    return {
        api,
        setApi,
        actual,
        setActual,
    };
};

export default useCarouselGaleria;
