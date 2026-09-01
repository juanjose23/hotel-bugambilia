import { Hotel, BedDouble, ShieldCheck, Wifi } from 'lucide-react';

interface HabitacionHeroProps {
    totalHabitaciones?: number;
}

export const HabitacionHero = ({
    totalHabitaciones = 12,
}: HabitacionHeroProps) => {
    return (
        <section
            aria-label="Portada de Habitaciones y Suites"
            className="relative overflow-hidden border-b border-border font-sans"
        >
            {/* Fondo con fotografía y gradientes sutiles */}
            <div className="relative h-[280px] w-full sm:h-[340px] lg:h-[380px]">
                <img
                    src="/images/hero-secondary.webp"
                    alt="Habitaciones y confort colonial de Hotel Bugambilias Estelí"
                    className="h-full w-full object-cover brightness-[0.75] dark:brightness-[0.45]"
                />
                <div
                    aria-hidden="true"
                    className="absolute inset-0 bg-gradient-to-t from-background via-black/40 to-black/60"
                />

                {/* Contenido Central */}
                <div className="absolute inset-0 container mx-auto flex flex-col items-center justify-center px-4 text-center text-white sm:px-6">
                    {/* Badge Institucional */}
                    <div className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1 text-[11px] font-black tracking-wider text-rose-200 uppercase backdrop-blur-md">
                        <Hotel className="size-3.5" aria-hidden="true" />
                        <span>Colección Exclusiva de Suites</span>
                    </div>

                    <h1 className="mt-3.5 max-w-2xl text-2xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl">
                        Nuestras Habitaciones & Suites
                    </h1>

                    <p className="mt-2 max-w-lg text-xs font-medium text-white/90 sm:text-sm">
                        Diseñadas para brindarte descanso absoluto en Estelí.
                        Camas premium, aire acondicionado, WiFi de alta
                        velocidad y servicio personalizado.
                    </p>

                    {/* Micro-puntos de valor */}
                    <div className="mt-5 flex flex-wrap items-center justify-center gap-4 text-xs font-bold text-white/95 sm:gap-8">
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3 py-1 backdrop-blur-xs">
                            <BedDouble className="size-3.5 text-rose-300" />
                            <span>
                                {totalHabitaciones} Opciones Disponibles
                            </span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3 py-1 backdrop-blur-xs">
                            <Wifi className="size-3.5 text-rose-300" />
                            <span>WiFi Fibra Óptica 100 Mbps</span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3 py-1 backdrop-blur-xs">
                            <ShieldCheck className="size-3.5 text-rose-300" />
                            <span>Parqueo Privado 24/7</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default HabitacionHero;
