import { Link } from '@inertiajs/react';
import { ShieldCheck, Calendar, ArrowRight } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { BotonReservaWhatsApp } from './BotonReservaWhatsApp';

interface PropiedadesTarjetaFlotanteReserva {
    nombreItem: string;
    codigoItem?: string;
    tipoItem: 'habitacion' | 'espacio' | 'servicio';
    precioPrincipal?: number | string;
    precioPorHora?: number | string;
    precioBase?: number | string;
    precio?: number | string;
    precio_desde?: number | string;
    moneda?: string;
    tipoTarifaLabel?: string;
    esOferta?: boolean;
    reservable?: boolean;
    rutaReserva?: string;
    labelBoton?: string;
    className?: string;
}

const formatearMoneda = (val?: number | string) => {
    if (val === undefined || val === null || val === '') {
        return '0.00';
    }

    const num = typeof val === 'string' ? parseFloat(val) : Number(val);

    if (isNaN(num)) {
        return '0.00';
    }

    return num.toLocaleString('es-NI', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

export const TarjetaFlotanteReserva = ({
    nombreItem,
    tipoItem,
    precioPrincipal,
    precioPorHora = 0,
    precioBase = 0,
    precio,
    precio_desde,
    moneda = '$',
    tipoTarifaLabel = '',
    esOferta = false,
    reservable = true,
    rutaReserva,
    labelBoton = 'Reservar Ahora',
    className = '',
}: PropiedadesTarjetaFlotanteReserva) => {
    // Extracción de precio efectiva para evitar $0 desautorizados
    const rawPrice =
        precioPrincipal !== undefined &&
        precioPrincipal !== null &&
        Number(precioPrincipal) > 0
            ? precioPrincipal
            : precio !== undefined && precio !== null && Number(precio) > 0
              ? precio
              : precio_desde !== undefined &&
                  precio_desde !== null &&
                  Number(precio_desde) > 0
                ? precio_desde
                : precioBase !== undefined &&
                    precioBase !== null &&
                    Number(precioBase) > 0
                  ? precioBase
                  : 0;

    const numPrecio =
        typeof rawPrice === 'string'
            ? parseFloat(rawPrice) || 0
            : Number(rawPrice) || 0;
    const numHora =
        typeof precioPorHora === 'string'
            ? parseFloat(precioPorHora) || 0
            : Number(precioPorHora) || 0;
    const numBase =
        typeof precioBase === 'string'
            ? parseFloat(precioBase) || 0
            : Number(precioBase) || 0;

    return (
        <div
            className={`space-y-6 rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-xl md:p-8 ${className}`}
        >
            {/* Cabecera Tarjeta */}
            <div className="flex items-baseline justify-between border-b border-border/40 pb-5">
                <div>
                    <div className="flex items-center gap-2">
                        <span className="block text-[10px] font-extrabold tracking-widest text-muted-foreground uppercase">
                            Tarifa Confirmada
                        </span>
                        {esOferta && (
                            <Badge
                                variant="default"
                                className="rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-extrabold text-white"
                            >
                                ¡Oferta Especial!
                            </Badge>
                        )}
                    </div>

                    <div className="mt-2 space-y-1">
                        {numHora > 0 ? (
                            <div className="flex items-baseline gap-1.5">
                                <span className="text-3xl font-black text-foreground">
                                    {moneda} {formatearMoneda(numHora)}
                                </span>
                                <span className="text-xs font-bold text-bugambilia-600 uppercase dark:text-bugambilia-400">
                                    / hora
                                </span>
                            </div>
                        ) : null}

                        {numBase > 0 ? (
                            <div className="flex items-baseline gap-1.5">
                                <span
                                    className={
                                        numHora > 0
                                            ? 'text-xs font-semibold text-muted-foreground'
                                            : 'text-3xl font-black text-foreground'
                                    }
                                >
                                    {moneda} {formatearMoneda(numBase)}
                                </span>
                                <span className="text-xs font-semibold text-muted-foreground">
                                    {numHora > 0
                                        ? '/ tarifa base'
                                        : tipoTarifaLabel || '/ noche'}
                                </span>
                            </div>
                        ) : null}

                        {numHora <= 0 && numBase <= 0 && numPrecio > 0 && (
                            <div className="flex items-baseline gap-1.5">
                                <span className="text-3xl font-black text-bugambilia-600 dark:text-bugambilia-400">
                                    {moneda} {formatearMoneda(numPrecio)}
                                </span>
                                <span className="text-xs font-bold text-muted-foreground uppercase">
                                    {tipoTarifaLabel || '/ noche'}
                                </span>
                            </div>
                        )}

                        {numHora <= 0 && numBase <= 0 && numPrecio <= 0 && (
                            <span className="text-2xl font-black text-foreground">
                                Consultar Tarifa
                            </span>
                        )}
                    </div>
                </div>

                <Badge
                    variant="outline"
                    className="border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-extrabold text-emerald-600 dark:text-emerald-400"
                >
                    <ShieldCheck
                        className="mr-1 size-3.5"
                        data-icon="inline-start"
                    />{' '}
                    Disponible
                </Badge>
            </div>

            {/* Acciones de Reserva */}
            <div className="space-y-3">
                {reservable && rutaReserva ? (
                    <div className="space-y-3">
                        <div className="rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 p-3.5 text-center">
                            <div className="flex items-center justify-center gap-1.5 text-xs font-black text-bugambilia-600 dark:text-bugambilia-400">
                                <ShieldCheck className="size-4" />
                                <span>Reserva Garantizada en Línea</span>
                            </div>
                        </div>

                        <Button
                            asChild
                            size="lg"
                            className="w-full rounded-2xl bg-bugambilia-600 py-6 text-xs font-black tracking-wider text-white uppercase hover:bg-bugambilia-700"
                        >
                            <Link href={rutaReserva} prefetch>
                                <Calendar
                                    className="mr-1.5 size-4"
                                    data-icon="inline-start"
                                />
                                {labelBoton}
                                <ArrowRight
                                    className="ml-1.5 size-4"
                                    data-icon="inline-end"
                                />
                            </Link>
                        </Button>
                    </div>
                ) : (
                    <BotonReservaWhatsApp
                        nombreItem={nombreItem}
                        tipo={tipoItem}
                    />
                )}
            </div>

            <p className="text-center text-[11px] font-medium text-muted-foreground">
                Sin cargos ocultos • Check-in garantizado en Estelí
            </p>
        </div>
    );
};

export default TarjetaFlotanteReserva;
