import { usePage, Link } from '@inertiajs/react';
import { BedDouble } from 'lucide-react';
import { PortadaHeroGeneral } from '@/modulos/compartido/componentes/PortadaHeroGeneral';
import { Button } from '@/modulos/compartido/ui/boton';

const FONDOS_HABITACIONES = [
    '/images/pool-front-view.webp',
    '/images/main-room.webp',
    '/images/group-room.webp',
];

export const PortadaHabitaciones = () => {
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
            ubicacion={`${(hotel as { name?: string })?.name || 'Hotel Bugambilias'} • Estelí, Nicaragua`}
            acciones={
                <>
                    <Button
                        size="lg"
                        className="rounded-2xl bg-bugambilia-600 px-8 py-3.5 text-xs font-black tracking-wider text-white uppercase hover:bg-bugambilia-700"
                        asChild
                    >
                        <Link href="#habitaciones" prefetch>
                            Explorar Habitaciones
                        </Link>
                    </Button>
                    <Button
                        variant="outline"
                        size="lg"
                        className="rounded-2xl border-white/30 bg-white/10 px-8 py-3.5 text-xs font-black tracking-wider text-white uppercase backdrop-blur-md hover:bg-white/20"
                        asChild
                    >
                        <Link href="/contacto" prefetch>
                            Consultar Disponibilidad
                        </Link>
                    </Button>
                </>
            }
        />
    );
};

export default PortadaHabitaciones;
