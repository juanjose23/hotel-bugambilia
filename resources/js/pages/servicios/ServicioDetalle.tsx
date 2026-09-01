import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { ServicioConsultaSheet } from '@/modules/servicios/components/ServicioConsultaSheet';
import { ServicioDetalleHero } from '@/modules/servicios/components/ServicioDetalleHero';
import { ServicioPoliticas } from '@/modules/servicios/components/ServicioPoliticas';
import type { ServicioDetalleProps } from '@/modules/servicios/types';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';

export const ServicioDetalle = ({ service }: ServicioDetalleProps) => {
    const [consultaAbierta, setConsultaAbierta] = useState(false);
    const { hotel } = usePropiedadesPagina();

    const telefonoWhatsApp = (hotel?.whatsapp || '+50584842323').replace(
        /\D/g,
        '',
    );

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>{`${service.nombre} — Hotel Bugambilias`}</title>
                <meta
                    name="description"
                    content={`${service.nombre} en Hotel Bugambilias Estelí. ${service.descripcion || ''}`}
                />
            </Head>

            {/* Cabecera y Detalles de Servicio */}
            <ServicioDetalleHero
                service={service}
                alAbrirConsulta={() => setConsultaAbierta(true)}
                telefonoWhatsApp={telefonoWhatsApp}
            />

            {/* Políticas y Condiciones */}
            <div className="container mx-auto px-4 pb-14 sm:px-6 lg:max-w-5xl">
                <ServicioPoliticas politicas={service.politicas} />
            </div>

            {/* Sheet Lateral de Consulta */}
            <ServicioConsultaSheet
                abierto={consultaAbierta}
                alCerrar={() => setConsultaAbierta(false)}
                servicio={service}
            />
        </div>
    );
};

export default ServicioDetalle;
