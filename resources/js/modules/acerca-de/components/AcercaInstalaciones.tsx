import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import type { InstalacionItem } from '../types';

const INSTALACIONES: InstalacionItem[] = [
    {
        id: '1',
        nombre: 'Jardines Coloniales & Terraza',
        categoria: 'Naturaleza & Descanso',
        descripcion:
            'Espacios abiertos rodeados de vegetación tropical y bugambilias, perfectos para el café matutino o una charla al atardecer.',
        imagen: '/images/service-events.webp',
    },
    {
        id: '2',
        nombre: 'Restaurante & Sabor Norteño',
        categoria: 'Gastronomía',
        descripcion:
            'Menú variado con especialidades tradicionales nicaragüenses, platos internacionales y coctelería seleccionada.',
        imagen: '/images/service-kitchen.webp',
    },
    {
        id: '3',
        nombre: 'Piscina & Solárium Tropical',
        categoria: 'Recreación & Bienestar',
        descripcion:
            'Área al aire libre con piscina cristalina, camastros y toallas de cortesía para una tarde relajante bajo el sol de Estelí.',
        imagen: '/images/service-pool.webp',
    },
    {
        id: '4',
        nombre: 'Habitaciones & Suites Confort',
        categoria: 'Hospedaje',
        descripcion:
            'Espacios amplios y luminosos con acabados de madera, aire acondicionado silencioso, WiFi de alta velocidad y baño privado.',
        imagen: '/images/main-room.webp',
    },
];

export const AcercaInstalaciones = () => {
    return (
        <section
            aria-labelledby="titulo-instalaciones"
            className="bg-background py-10 md:py-16"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <span className="text-xs font-black tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                            Nuestros Espacios
                        </span>
                        <h2
                            id="titulo-instalaciones"
                            className="mt-1 text-2xl font-black tracking-tight text-foreground sm:text-3xl"
                        >
                            Instalaciones y Áreas Comunes
                        </h2>
                    </div>

                    <Link
                        href="/habitaciones"
                        className="inline-flex shrink-0 items-center gap-1 text-xs font-bold text-bugambilia-600 hover:text-bugambilia-700 dark:text-bugambilia-400"
                    >
                        <span className="hidden sm:inline">
                            Ver habitaciones
                        </span>
                        <span className="sm:hidden">Ver más</span>
                        <ArrowRight className="size-3.5" aria-hidden="true" />
                    </Link>
                </div>

                {/* En Mobile: Scroll Horizontal Táctil tipo App (Snap-x). En Desktop: Grid de 4 */}
                <div className="-mx-4 mt-6 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-4">
                    {INSTALACIONES.map((item) => (
                        <div
                            key={item.id}
                            className="group flex w-[260px] shrink-0 snap-center flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-xs transition-all duration-300 hover:border-bugambilia-500/50 sm:w-auto"
                        >
                            <div className="relative aspect-4/3 w-full overflow-hidden bg-muted">
                                <img
                                    src={item.imagen}
                                    alt={item.nombre}
                                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy"
                                />
                                <div className="absolute top-2.5 left-2.5 rounded-full bg-background/90 px-2.5 py-0.5 text-[10px] font-black text-foreground backdrop-blur-md">
                                    {item.categoria}
                                </div>
                            </div>

                            <div className="flex flex-1 flex-col justify-between p-4">
                                <div>
                                    <h3 className="text-xs font-black text-foreground sm:text-sm">
                                        {item.nombre}
                                    </h3>
                                    <p className="mt-1.5 line-clamp-3 text-[11px] leading-relaxed text-muted-foreground sm:text-xs">
                                        {item.descripcion}
                                    </p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
};

export default AcercaInstalaciones;
