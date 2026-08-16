import { Head } from '@inertiajs/react';
import { SeccionEspacios } from '@/modulos/espacios/componentes/SeccionEspacios';
import type { PropiedadesSeccionEspacios } from '@/modulos/espacios/interfaces/espacioInterfaces';

export const PaginaEspacios = ({
    espacios = [],
    tipos = [],
    tipoSeleccionado = 'TODOS',
}: PropiedadesSeccionEspacios) => {
    return (
        <>
            <Head>
                <title>
                    Salones & Espacios para Eventos — Hotel Bugambilias
                </title>
                <meta
                    name="description"
                    content="Reserve salones de eventos, terrazas y espacios corporativos en Estelí, Nicaragua con el servicio boutique de Hotel Bugambilias."
                />
            </Head>
            <SeccionEspacios
                espacios={espacios}
                tipos={tipos}
                tipoSeleccionado={tipoSeleccionado}
            />
        </>
    );
};

export default PaginaEspacios;
