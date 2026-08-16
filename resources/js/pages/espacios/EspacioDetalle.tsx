import { Head } from '@inertiajs/react';
import { SeccionDetalleEspacio } from '@/modulos/espacios/componentes/SeccionDetalleEspacio';
import type { PropiedadesSeccionDetalleEspacio } from '@/modulos/espacios/interfaces/espacioInterfaces';

export const PaginaEspacioDetalle = ({
    space,
    similarSpaces = [],
}: PropiedadesSeccionDetalleEspacio) => {
    return (
        <>
            <Head>
                <title>{`${space?.nombre || 'Espacio'} — Hotel Bugambilias Estelí`}</title>
                <meta
                    name="description"
                    content={
                        space?.descripcion ||
                        'Reserva de salones corporativos, terrazas y espacios para eventos en Hotel Bugambilias Estelí.'
                    }
                />
            </Head>
            <SeccionDetalleEspacio
                space={space}
                similarSpaces={similarSpaces}
            />
        </>
    );
};

export default PaginaEspacioDetalle;
