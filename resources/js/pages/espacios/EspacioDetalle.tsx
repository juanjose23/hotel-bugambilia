import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { EspacioCotizacionSheet } from '@/modules/espacios/components/EspacioCotizacionSheet';
import { EspacioDetalleHero } from '@/modules/espacios/components/EspacioDetalleHero';
import { EspacioServiciosIncluidos } from '@/modules/espacios/components/EspacioServiciosIncluidos';
import { EspacioSimilares } from '@/modules/espacios/components/EspacioSimilares';
import type { EspacioDetalleProps } from '@/modules/espacios/types';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';

export const EspacioDetalle = ({
    space,
    similarSpaces = [],
}: EspacioDetalleProps) => {
    const [cotizacionAbierta, setCotizacionAbierta] = useState(false);
    const { hotel } = usePropiedadesPagina();

    const telefonoWhatsApp = (hotel?.whatsapp || '+50584842323').replace(
        /\D/g,
        '',
    );

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>{`${space.nombre} — Hotel Bugambilias`}</title>
                <meta
                    name="description"
                    content={`${space.nombre} en Hotel Bugambilias Estelí. ${space.descripcion || ''}`}
                />
            </Head>

            {/* Cabecera & Detalle del Espacio */}
            <EspacioDetalleHero
                space={space}
                alAbrirCotizacion={() => setCotizacionAbierta(true)}
                telefonoWhatsApp={telefonoWhatsApp}
            />

            <div className="container mx-auto px-4 pb-16 sm:px-6 lg:max-w-5xl">
                {/* Equipamiento y Amenidades */}
                <EspacioServiciosIncluidos
                    servicios={space.serviciosIncluidos}
                />

                {/* Espacios Similares */}
                <EspacioSimilares similares={similarSpaces} />
            </div>

            {/* Sheet Lateral para Cotización */}
            <EspacioCotizacionSheet
                abierto={cotizacionAbierta}
                alCerrar={() => setCotizacionAbierta(false)}
                espacio={space}
            />
        </div>
    );
};

export default EspacioDetalle;
