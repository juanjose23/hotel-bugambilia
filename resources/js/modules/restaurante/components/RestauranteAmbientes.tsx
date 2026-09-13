import { Users, Check, LayoutGrid, MapPin } from 'lucide-react';
import { useState } from 'react';
import type { AmbienteData, MesaData } from '../types';
import { RestaurantePlanoMesasVisual } from './RestaurantePlanoMesasVisual';

interface PropsRestauranteAmbientes {
    ambientes: AmbienteData[];
    mesas?: MesaData[];
    alAbrirReserva?: (mesa?: MesaData) => void;
}

export const RestauranteAmbientes = ({
    ambientes,
    mesas = [],
    alAbrirReserva,
}: PropsRestauranteAmbientes) => {
    const [vistaModo, setVistaModo] = useState<'galeria' | 'plano'>('galeria');

    if (!ambientes || ambientes.length === 0) {
        return null;
    }

    return (
        <section
            id="ambientes-y-plano"
            className="container mx-auto px-4 py-16 sm:px-6"
        >
            <div className="flex flex-col items-center justify-between gap-4 text-center md:flex-row md:text-left">
                <div>
                    <span className="text-xs font-black tracking-widest text-primary uppercase dark:text-rose-400">
                        Nuestros Espacios y Distribución
                    </span>
                    <h2 className="mt-1 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                        Ambientes y Plano del Restaurante
                    </h2>
                    <p className="mt-2 max-w-xl text-xs text-muted-foreground sm:text-sm">
                        Conoce nuestros espacios o explora la ubicación exacta
                        de cada mesa antes de reservar.
                    </p>
                </div>

                {/* Switcher de Vista */}
                <div className="flex items-center gap-1.5 rounded-2xl border border-border/80 bg-muted/50 p-1 shadow-xs">
                    <button
                        type="button"
                        onClick={() => setVistaModo('galeria')}
                        className={`flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold transition-all ${
                            vistaModo === 'galeria'
                                ? 'bg-background font-black text-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <LayoutGrid className="size-4 text-primary dark:text-rose-400" />
                        <span>Ambientes</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setVistaModo('plano')}
                        className={`flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold transition-all ${
                            vistaModo === 'plano'
                                ? 'bg-background font-black text-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <MapPin className="size-4 text-primary dark:text-rose-400" />
                        <span>Plano de Mesas</span>
                    </button>
                </div>
            </div>

            {/* Vista Galería de Ambientes */}
            {vistaModo === 'galeria' ? (
                <div className="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    {ambientes.map((ambiente) => (
                        <div
                            key={ambiente.id || ambiente.codigo}
                            className="group flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-xl dark:hover:border-rose-500/50"
                        >
                            <div className="relative aspect-4/3 w-full overflow-hidden bg-muted">
                                <img
                                    src={
                                        ambiente.imagenes &&
                                        ambiente.imagenes.length > 0
                                            ? ambiente.imagenes[0]
                                            : '/images/terrace.jpg'
                                    }
                                    alt={ambiente.nombre}
                                    className="size-full object-cover transition duration-500 group-hover:scale-105"
                                    loading="lazy"
                                />
                                <div className="absolute top-3 right-3 flex items-center gap-1.5 rounded-full border border-white/20 bg-black/70 px-2.5 py-1 text-xs font-bold text-white shadow-xs backdrop-blur-md">
                                    <Users className="size-3 text-amber-300" />
                                    <span>{ambiente.capacidad} pers.</span>
                                    {ambiente.mesas_count > 0 && (
                                        <>
                                            <span className="opacity-40">
                                                &middot;
                                            </span>
                                            <span className="text-amber-200">
                                                {ambiente.mesas_count} mesas
                                            </span>
                                        </>
                                    )}
                                </div>
                                <span className="absolute bottom-3 left-3 rounded-full border border-white/20 bg-background/90 px-2.5 py-0.5 text-[10px] font-black text-foreground uppercase backdrop-blur-md">
                                    {ambiente.zona}
                                </span>
                            </div>

                            <div className="flex grow flex-col p-5">
                                <h3 className="text-base font-black tracking-tight text-foreground transition-colors group-hover:text-primary dark:group-hover:text-rose-400">
                                    {ambiente.nombre}
                                </h3>

                                <p className="mt-2 grow text-xs leading-relaxed text-muted-foreground">
                                    {ambiente.descripcion}
                                </p>

                                {ambiente.caracteristicas &&
                                    ambiente.caracteristicas.length > 0 && (
                                        <div className="mt-4 flex flex-wrap gap-1.5 border-t border-border/60 pt-3">
                                            {ambiente.caracteristicas
                                                .slice(0, 3)
                                                .map((caract, idx) => (
                                                    <span
                                                        key={idx}
                                                        className="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-[10px] font-semibold text-muted-foreground"
                                                    >
                                                        <Check className="size-2.5 text-primary dark:text-rose-400" />
                                                        {caract}
                                                    </span>
                                                ))}
                                        </div>
                                    )}
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                /* Vista Plano Visual de Mesas */
                <div className="mt-8">
                    <RestaurantePlanoMesasVisual
                        mesas={mesas}
                        alAbrirReserva={alAbrirReserva}
                    />
                </div>
            )}
        </section>
    );
};
