import { Tag } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import type { PromocionInicio } from '../interfaces/inicio';
import { TarjetaPromocionItem } from './secciones/TarjetaPromocionItem';

interface PropiedadesSeccionPromociones {
    promociones?: PromocionInicio[];
}

export const SeccionPromociones = ({
    promociones = [],
}: PropiedadesSeccionPromociones) => {
    if (!promociones || promociones.length === 0) {
        return null;
    }

    return (
        <section className="border-b border-border/40 bg-card py-16 font-sans lg:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-16 max-w-3xl text-center">
                    <Badge
                        variant="outline"
                        className="mb-3 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        <Tag
                            className="mr-1 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Ofertas Especiales
                    </Badge>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Promociones &{' '}
                        <span className="text-bugambilia-600 dark:text-bugambilia-400">
                            Paquetes
                        </span>
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        Aproveche nuestros descuentos exclusivos para escapadas
                        de fin de semana, estancias prolongadas y eventos.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {promociones.map((promocion) => (
                        <TarjetaPromocionItem
                            key={promocion.id}
                            promocion={promocion}
                        />
                    ))}
                </div>
            </div>
        </section>
    );
};

export default SeccionPromociones;
