import { Hotel, UtensilsCrossed, Waves, Users, Activity } from 'lucide-react';

export const ServicioHero = () => {
    return (
        <section
            aria-label="Portada de Servicios y Experiencias"
            className="relative overflow-hidden border-b border-border font-sans"
        >
            {/* Fotografía de fondo con overlay elegante */}
            <div className="relative h-[280px] w-full sm:h-[340px] lg:h-[380px]">
                <img
                    src="/images/service-kitchen.webp"
                    alt="Servicios gastronómicos y amenidades de Hotel Bugambilias Estelí"
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
                        <span>Experiencias & Hospitalidad Boutique</span>
                    </div>

                    <h1 className="mt-3.5 max-w-2xl text-2xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl">
                        Servicios & Experiencias Exclusivas
                    </h1>

                    <p className="mt-2 max-w-lg text-xs font-medium text-white/90 sm:text-sm">
                        Deleita tus sentidos con nuestra gastronomía de autor,
                        piscina tropical, coctelería artesanal y salones
                        climatizados para tus eventos en Estelí.
                    </p>

                    {/* Micro-puntos de valor */}
                    <div className="mt-5 flex flex-wrap items-center justify-center gap-3 text-xs font-bold text-white/95 sm:gap-6">
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3.5 py-1 backdrop-blur-xs">
                            <UtensilsCrossed className="size-3.5 text-rose-300" />
                            <span>Restaurante Absoluto</span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3.5 py-1 backdrop-blur-xs">
                            <Waves className="size-3.5 text-rose-300" />
                            <span>Piscina & Solárium</span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3.5 py-1 backdrop-blur-xs">
                            <Activity className="size-3.5 text-rose-300" />
                            <span>Gimnasio Equipado</span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3.5 py-1 backdrop-blur-xs">
                            <Users className="size-3.5 text-rose-300" />
                            <span>Eventos & Bodas</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default ServicioHero;
