import { Hotel, Gift, Percent, ShieldCheck } from 'lucide-react';

interface PromocionHeroProps {
    totalPromociones?: number;
}

export const PromocionHero = ({ totalPromociones = 0 }: PromocionHeroProps) => {
    return (
        <section
            aria-label="Portada de Promociones y Paquetes Especiales"
            className="relative overflow-hidden border-b border-border font-sans"
        >
            {/* Fotografía de Fondo con Overlay */}
            <div className="relative h-[280px] w-full sm:h-[340px] lg:h-[380px]">
                <img
                    src="/images/hero-main.webp"
                    alt="Promociones y ofertas de Hotel Bugambilias Estelí"
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
                        <Gift className="size-3.5 text-rose-300" />
                        <span>Ofertas de Temporada & Paquetes VIP</span>
                    </div>

                    <h1 className="mt-3.5 max-w-2xl text-2xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl">
                        Promociones & Paquetes Exclusivos
                    </h1>

                    <p className="mt-2 max-w-lg text-xs font-medium text-white/90 sm:text-sm">
                        Tarifas especiales, escapadas de fin de semana, paquetes
                        corporativos y beneficios de cortesía para vivir la
                        mejor experiencia en Estelí.
                    </p>

                    {/* Micro-puntos de valor */}
                    <div className="mt-5 flex flex-wrap items-center justify-center gap-3 text-xs font-bold text-white/95 sm:gap-6">
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3.5 py-1 backdrop-blur-xs">
                            <Percent className="size-3.5 text-rose-300" />
                            <span>Descuentos de hasta 30%</span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3.5 py-1 backdrop-blur-xs">
                            <Hotel className="size-3.5 text-rose-300" />
                            <span>{totalPromociones} Paquetes Disponibles</span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full bg-black/40 px-3.5 py-1 backdrop-blur-xs">
                            <ShieldCheck className="size-3.5 text-rose-300" />
                            <span>Reserva Directa Garantizada</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default PromocionHero;
