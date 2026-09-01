import { Check, Sparkles } from 'lucide-react';
import type { PortalReservaDetalleCompleto } from '../../types';

interface DetalleSuiteCardProps {
    reserva: PortalReservaDetalleCompleto;
}

export const DetalleSuiteCard = ({ reserva }: DetalleSuiteCardProps) => {
    const { recurso } = reserva;
    const portada =
        recurso.imagenes.find((img) => img.es_portada)?.url ||
        recurso.imagenes[0]?.url ||
        '/images/hero-banner.webp';

    return (
        <div className="overflow-hidden rounded-3xl border border-border/70 bg-card shadow-xs">
            {/* Imagen de Cabecera */}
            <div className="relative h-64 w-full sm:h-72">
                <img
                    src={portada}
                    alt={recurso.nombre}
                    className="h-full w-full object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-background via-background/30 to-transparent" />

                <div className="absolute right-4 bottom-4 left-4 flex items-end justify-between gap-4 sm:right-6 sm:bottom-6 sm:left-6">
                    <div className="space-y-1">
                        <span className="inline-flex items-center gap-1 rounded-full bg-primary/90 px-3 py-0.5 text-xs font-bold text-white backdrop-blur-sm">
                            <Sparkles className="size-3" />
                            <span>{recurso.categoria}</span>
                        </span>
                        <h2 className="text-2xl font-black text-white drop-shadow-md sm:text-3xl">
                            {recurso.nombre}
                        </h2>
                    </div>

                    {recurso.codigo && (
                        <span className="hidden rounded-full border border-white/20 bg-black/40 px-3 py-1 font-mono text-xs font-bold text-white backdrop-blur-md sm:inline-block">
                            Código: {recurso.codigo}
                        </span>
                    )}
                </div>
            </div>

            {/* Contenido y Servicios Incluidos */}
            <div className="space-y-6 p-6 sm:p-8">
                <div>
                    <h4 className="text-sm font-bold tracking-wider text-muted-foreground uppercase">
                        Servicios y Comodidades Incluidas
                    </h4>
                    {recurso.servicios_incluidos.length > 0 ? (
                        <div className="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                            {recurso.servicios_incluidos.map((s) => (
                                <div
                                    key={s.id}
                                    className="flex items-center gap-2 rounded-xl border border-border/40 bg-secondary/50 px-3.5 py-2.5 text-xs font-semibold text-foreground"
                                >
                                    <div className="flex size-5 items-center justify-center rounded-full bg-primary/10 text-primary">
                                        <Check className="size-3" />
                                    </div>
                                    <span>{s.nombre}</span>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="mt-2 text-xs text-muted-foreground">
                            Wi-Fi de alta velocidad, aire acondicionado,
                            amenidades de baño premium y desayuno incluido.
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
};
