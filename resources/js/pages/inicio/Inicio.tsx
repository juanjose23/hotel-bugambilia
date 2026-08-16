import { Head } from '@inertiajs/react';
import React, { Suspense } from 'react';
import SeccionPortada from '@/modulos/inicio/componentes/SeccionPortada';
import type { PropiedadesPaginaInicio } from '@/modulos/inicio/interfaces/inicio';

const HabitacionesDestacadas = React.lazy(
    () => import('@/modulos/habitaciones/componentes/HabitacionesDestacadas'),
);
const SeccionPromociones = React.lazy(
    () => import('@/modulos/inicio/componentes/SeccionPromociones'),
);
const ResumenAcercaDe = React.lazy(
    () => import('@/modulos/acerca-de/componentes/ResumenAcercaDe'),
);
const SeccionTestimonios = React.lazy(
    () => import('@/modulos/inicio/componentes/SeccionTestimonios'),
);

const FallbackCargando = () => (
    <div className="flex items-center justify-center py-24">
        <div className="h-8 w-8 animate-spin rounded-full border-4 border-bugambilia-500 border-t-transparent" />
    </div>
);

export const PaginaInicio = ({
    hotelInfo,
    habitaciones = [],
    promociones = [],
    categoriasHabitacion = [],
}: PropiedadesPaginaInicio) => {
    return (
        <>
            <Head>
                <title>Hotel Bugambilias — Estelí, Nicaragua</title>
                <meta
                    name="description"
                    content="Hotel Bugambilias Estelí — Reserva habitaciones, suites y servicios exclusivos en el corazón de Nicaragua. WiFi, estacionamiento y desayuno incluido."
                />
            </Head>
            <SeccionPortada hotelInfo={hotelInfo} />
            <Suspense fallback={<FallbackCargando />}>
                <HabitacionesDestacadas
                    rooms={habitaciones}
                    categories={categoriasHabitacion}
                />
            </Suspense>
            <Suspense fallback={<FallbackCargando />}>
                <SeccionPromociones promociones={promociones} />
            </Suspense>
            <Suspense fallback={<FallbackCargando />}>
                <ResumenAcercaDe />
            </Suspense>
            <Suspense fallback={<FallbackCargando />}>
                <SeccionTestimonios />
            </Suspense>
        </>
    );
};
export default PaginaInicio;
