import { usePage, Link } from '@inertiajs/react';
import { BedDouble } from 'lucide-react';
import React from 'react';
import { PortadaHeroGeneral } from '@/modules/shared/components/PortadaHeroGeneral';
import { Button } from '@/modules/shared/ui/boton';

const FONDOS_HABITACIONES = [
    '/images/pool-front-view.webp',
    '/images/main-room.webp',
    '/images/group-room.webp',
];

const PortadaHabitaciones: React.FC = () => {
    const { hotel } = usePage().props;

    return (
        <PortadaHeroGeneral
            imagenFondo={FONDOS_HABITACIONES[0]}
            carruselImagenes={FONDOS_HABITACIONES}
            alturaClass="h-[58vh] max-h-[620px] min-h-[460px]"
            badgeLabel="Habitaciones & Suites 5 Estrellas"
            badgeIcon={BedDouble}
            badgeStyle="border-amber-400/40 bg-amber-500/20 text-amber-300"
            titulo="Nuestras"
            tituloEnfasis="Habitaciones"
            subtitulo="Comodidad y elegancia en Estelí"
            descripcion="Descubre nuestras suites y habitaciones diseñadas con acabados artesanales y tecnología moderna para brindarte el máximo confort en Nicaragua."
            ubicacion={`${hotel?.name || 'Hotel Bugambilias'} • Estelí, Nicaragua`}
            acciones={
                <>
                    <Button
                        size="lg"
                        className="petal-shadow cursor-pointer rounded-2xl bg-bugambilia-600 px-8 py-3.5 text-xs font-black tracking-wider text-white uppercase transition-all duration-300 hover:scale-105 hover:bg-bugambilia-700"
                        asChild
                    >
                        <Link href="#habitaciones">Explorar Habitaciones</Link>
                    </Button>
                    <Button
                        variant="outline"
                        size="lg"
                        className="cursor-pointer rounded-2xl border-white/30 bg-white/10 px-8 py-3.5 text-xs font-black tracking-wider text-white uppercase backdrop-blur-md transition-all hover:bg-white/20"
                        asChild
                    >
                        <Link href="/contacto">Consultar Disponibilidad</Link>
                    </Button>
                </>
            }
        />
    );
};

export default PortadaHabitaciones;
