import { Sparkles } from 'lucide-react';
import { usePropiedadesPagina } from '@/modulos/compartido/hooks/usePropiedadesPagina';
import { Badge } from '@/modulos/compartido/ui/insignia';
import type { PropiedadesEncabezadoAuth } from '../interfaces/autenticacionInterfaces';

export const EncabezadoAuth = ({
    badge = 'Acceso Huéspedes',
    titulo,
    subtituloEnfasis,
    descripcion,
}: PropiedadesEncabezadoAuth) => {
    const { hotel } = usePropiedadesPagina();
    const hotelName = hotel?.name || 'Hotel Bugambilias';

    return (
        <div className="mb-6 flex flex-col items-center text-center">
            <Badge
                variant="outline"
                className="mb-2 border-primary/30 bg-primary/10 text-primary"
            >
                <Sparkles className="mr-1 size-3.5" data-icon="inline-start" />
                {badge}
            </Badge>
            <h1 className="mb-1 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                {titulo}{' '}
                {subtituloEnfasis && (
                    <span className="font-serif font-normal text-primary italic">
                        {subtituloEnfasis}
                    </span>
                )}
            </h1>
            <p className="text-xs font-medium text-muted-foreground sm:text-sm">
                {descripcion ||
                    `Gestione sus reservas y experiencias exclusivas en ${hotelName}`}
            </p>
        </div>
    );
};
