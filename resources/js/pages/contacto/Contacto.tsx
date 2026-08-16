import { Head } from '@inertiajs/react';
import { SeccionContacto } from '@/modulos/contacto/componentes/SeccionContacto';

interface PropiedadesPaginaContacto {
    hotel?: {
        name?: string;
        telefono?: string;
        email?: string;
        direccion?: string;
    };
}

export const PaginaContacto = ({ hotel }: PropiedadesPaginaContacto) => {
    return (
        <>
            <Head>
                <title>
                    Contacto Directo & Recepción 24/7 — Hotel Bugambilias Estelí
                </title>
                <meta
                    name="description"
                    content="Contacte directamente con la recepción de Hotel Bugambilias Estelí. Asistencia 24/7, atención telefónica, WhatsApp y reservación directa sin intermediarios."
                />
            </Head>
            <SeccionContacto hotelInfo={hotel} />
        </>
    );
};

export default PaginaContacto;
