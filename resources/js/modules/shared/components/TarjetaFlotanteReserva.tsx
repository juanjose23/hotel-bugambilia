import { Link } from '@inertiajs/react';
import { Calendar, ArrowRight, ShieldCheck } from 'lucide-react';
import React from 'react';
import { Button } from '@/modules/shared/ui/boton';
import { Badge } from '@/modules/shared/ui/insignia';
import { BotonReservaWhatsApp } from './BotonReservaWhatsApp';

interface PropiedadesTarjetaFlotanteReserva {
    nombreItem: string;
    codigoItem?: string;
    tipoItem: 'habitacion' | 'espacio' | 'servicio';
    precioPrincipal?: number;
    precioPorHora?: number;
    precioBase?: number;
    moneda?: string;
    tipoTarifaLabel?: string;
    esOferta?: boolean;
    reservable?: boolean;
    rutaReserva?: string;
    className?: string;
}

const formatearMoneda = (val?: number) => {
    if (val === undefined || val === null) {
        return '0.00';
    }

    return Number(val).toLocaleString('es-NI', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

export const TarjetaFlotanteReserva: React.FC<
    PropiedadesTarjetaFlotanteReserva
> = ({
    nombreItem,
    codigoItem,
    tipoItem,
    precioPrincipal = 0,
    precioPorHora = 0,
    precioBase = 0,
    moneda = '$',
    tipoTarifaLabel = '',
    esOferta = false,
    reservable = true,
    rutaReserva,
    className = '',
}) => {
    return (
        <div
            className={`space-y-6 rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-xs md:p-8 ${className}`}
        >
            {/* Cabecera Tarjeta */}
            <div className="flex items-baseline justify-between border-b border-border/40 pb-5">
                <div>
                    <div className="flex items-center gap-2">
                        <span className="block text-xs font-bold tracking-wider text-muted-foreground uppercase">
                            Tarifa Sugerida
                        </span>
                        {esOferta && (
                            <span className="rounded-full bg-rose-500/10 px-2.5 py-0.5 text-[10px] font-black text-rose-600 dark:text-rose-400">
                                ¡Oferta Especial!
                            </span>
                        )}
                    </div>

                    <div className="mt-1 space-y-1">
                        {precioPorHora > 0 ? (
                            <div className="flex items-baseline gap-1.5">
                                <span className="text-3xl font-black text-foreground">
                                    {moneda} {formatearMoneda(precioPorHora)}
                                </span>
                                <span className="text-xs font-bold text-bugambilia-600 dark:text-bugambilia-400">
                                    / hora
                                </span>
                            </div>
                        ) : null}

                        {precioBase > 0 ? (
                            <div className="flex items-baseline gap-1.5">
                                <span
                                    className={
                                        precioPorHora > 0
                                            ? 'text-xs font-semibold text-muted-foreground'
                                            : 'text-3xl font-black text-foreground'
                                    }
                                >
                                    {moneda} {formatearMoneda(precioBase)}
                                </span>
                                <span className="text-xs font-semibold text-muted-foreground">
                                    {precioPorHora > 0
                                        ? '/ tarifa base'
                                        : tipoTarifaLabel || '/ noche'}
                                </span>
                            </div>
                        ) : null}

                        {precioPorHora <= 0 &&
                            precioBase <= 0 &&
                            precioPrincipal > 0 && (
                                <div className="flex items-baseline gap-1.5">
                                    <span className="text-3xl font-black text-foreground">
                                        {moneda}{' '}
                                        {formatearMoneda(precioPrincipal)}
                                    </span>
                                    <span className="text-xs font-semibold text-muted-foreground">
                                        {tipoTarifaLabel || '/ estancia'}
                                    </span>
                                </div>
                            )}

                        {precioPorHora <= 0 &&
                            precioBase <= 0 &&
                            precioPrincipal <= 0 && (
                                <span className="text-3xl font-black text-foreground">
                                    Acceso Libre
                                </span>
                            )}
                    </div>
                </div>

                <Badge
                    variant="outline"
                    className="border-emerald-500/25 bg-emerald-500/10 px-3 py-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400"
                >
                    <ShieldCheck className="mr-1 h-3.5 w-3.5" />
                    Disponible
                </Badge>
            </div>

            {/* Acciones de Reserva */}
            <div className="space-y-3">
                {reservable && rutaReserva ? (
                    <div className="space-y-2">
                        <div className="rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/5 p-3.5 text-center">
                            <div className="flex items-center justify-center gap-1.5 text-xs font-black text-bugambilia-600 dark:text-bugambilia-400">
                                <ShieldCheck className="h-4 w-4" />
                                <span>Reserva Garantizada en Línea</span>
                            </div>
                        </div>

                        <Button
                            asChild
                            className="w-full cursor-pointer rounded-2xl bg-bugambilia-600 py-6 text-sm font-black text-white shadow-lg transition-all hover:bg-bugambilia-700 hover:shadow-xl"
                        >
                            <Link href={rutaReserva}>
                                <Calendar className="mr-2 h-5 w-5" />
                                Reservar Ahora
                                <ArrowRight className="ml-2 h-4 w-4" />
                            </Link>
                        </Button>
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed border-border bg-muted/40 p-4 text-center text-xs font-semibold text-muted-foreground">
                        Visita abierta durante horarios de atención o previa
                        consulta por WhatsApp.
                    </div>
                )}

                {/* Botón WhatsApp Reutilizable */}
                <BotonReservaWhatsApp
                    nombreItem={nombreItem}
                    codigoItem={codigoItem}
                    tipo={tipoItem}
                />
            </div>
        </div>
    );
};
