import { Award } from 'lucide-react';
import { useState, useEffect } from 'react';
import { PortadaHeroGeneral } from '@/modulos/compartido/componentes/PortadaHeroGeneral';
import type { InformacionHotelInicio } from '../interfaces/inicio';
import { BuscadorDisponibilidadInicio } from './secciones/BuscadorDisponibilidadInicio';

const FONDOS_HERO = [
    '/images/hero-main.webp',
    '/images/pool-front-view.webp',
    '/images/terrace.webp',
    '/images/group-room.webp',
];

interface PropiedadesSeccionPortada {
    hotelInfo?: InformacionHotelInicio;
}

export const SeccionPortada = ({ hotelInfo }: PropiedadesSeccionPortada) => {
    const hotelName = hotelInfo?.nombre || 'Hotel Bugambilias';
    const [currentBgIndex, setCurrentBgIndex] = useState(0);

    useEffect(() => {
        const interval = setInterval(() => {
            setCurrentBgIndex((prev) => (prev + 1) % FONDOS_HERO.length);
        }, 5500);

        return () => clearInterval(interval);
    }, []);

    return (
        <section className="relative font-sans">
            <PortadaHeroGeneral
                imagenFondo={FONDOS_HERO[currentBgIndex]}
                carruselImagenes={FONDOS_HERO}
                indiceImagenActiva={currentBgIndex}
                alSeleccionarImagenCarrusel={(idx) => setCurrentBgIndex(idx)}
                alturaClass="h-[88vh] max-h-[900px] min-h-[640px]"
                badgeLabel="Estelí, Nicaragua • Confort Boutique"
                badgeIcon={Award}
                badgeStyle="border-amber-400/40 bg-amber-500/20 text-amber-300"
                titulo="Un Refugio de Elegancia &"
                tituloEnfasis="Tranquilidad"
                descripcion={`Experimente la auténtica hospitalidad en ${hotelName}. Habitaciones confortables, piscina tropical y atención 5 estrellas.`}
            />

            <div className="relative z-20 container mx-auto -mt-20 px-4 sm:px-6 lg:px-8">
                <BuscadorDisponibilidadInicio />
            </div>
        </section>
    );
};

export default SeccionPortada;
