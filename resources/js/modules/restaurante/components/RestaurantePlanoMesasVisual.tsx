import {
    Calendar,
    CheckCircle2,
    Sparkles,
    Users,
    Utensils,
} from 'lucide-react';
import { useState, useMemo } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import {
    mesasPorDefecto,
    normalizarClaveZona,
    ZONAS_RESTAURANTE,
} from '../constants';
import type { ClaveZonaRestaurante, ZonaRestauranteConfig } from '../constants';
import type { MesaData } from '../types';

interface MesaCardInternaProps {
    mesa: MesaData;
    esSeleccionada: boolean;
    esUnida: boolean;
    alSeleccionar: (mesa: MesaData) => void;
}

const RestauranteMesaCardInterna = ({
    mesa,
    esSeleccionada,
    esUnida,
    alSeleccionar,
}: MesaCardInternaProps) => {
    const ordenNum = mesa.orden ?? mesa.id;

    return (
        <div
            onClick={() => alSeleccionar(mesa)}
            className={`group relative flex cursor-pointer flex-col items-center justify-between rounded-2xl border p-3 text-center transition-all duration-200 hover:-translate-y-1 hover:shadow-md ${
                esSeleccionada
                    ? 'border-primary bg-primary/15 ring-2 ring-primary dark:bg-rose-950/40'
                    : esUnida
                      ? 'border-amber-500 bg-amber-500/10 ring-2 ring-amber-500/30'
                      : 'border-border/80 bg-card hover:border-primary/50 dark:hover:border-rose-500/50'
            }`}
        >
            <div className="absolute top-2 left-2 flex size-5 items-center justify-center rounded-full bg-muted text-[10px] font-black text-muted-foreground shadow-xs">
                #{ordenNum}
            </div>

            <div className="absolute top-2 right-2 flex items-center gap-0.5 rounded-full border border-border/40 bg-background/80 px-1.5 py-0.5 text-[10px] font-bold text-muted-foreground backdrop-blur-xs">
                <Users className="size-2.5 text-primary dark:text-rose-400" />
                <span>{mesa.capacidad}</span>
            </div>

            <div className="my-2 mt-4 flex items-center justify-center">
                <div
                    className={`flex size-14 items-center justify-center transition-all duration-300 group-hover:scale-105 ${
                        mesa.tipo_mesa === 'redonda'
                            ? 'rounded-full'
                            : mesa.tipo_mesa === 'rectangular'
                              ? 'aspect-16/10 h-11 w-16 rounded-xl'
                              : 'rounded-xl'
                    } ${
                        esSeleccionada
                            ? 'bg-primary text-primary-foreground shadow-md'
                            : esUnida
                              ? 'bg-amber-500 text-white shadow-md'
                              : 'border border-border/80 bg-muted/80 text-foreground group-hover:border-primary/40'
                    }`}
                >
                    <Utensils className="size-5" />
                </div>
            </div>

            <span className="line-clamp-1 text-xs font-black tracking-tight text-foreground">
                {mesa.nombre}
            </span>
            <span className="text-[10px] text-muted-foreground capitalize">
                {mesa.tipo_mesa || 'Cuadrada'}
            </span>

            <div className="mt-2 flex w-full items-center justify-center gap-1 border-t border-border/40 pt-1.5">
                {esSeleccionada ? (
                    <span className="inline-flex items-center gap-1 text-[10px] font-black text-primary dark:text-rose-400">
                        <CheckCircle2 className="size-3" /> Seleccionada
                    </span>
                ) : esUnida ? (
                    <span className="inline-flex items-center gap-1 text-[10px] font-black text-amber-600 dark:text-amber-400">
                        <Sparkles className="size-3" /> Mesa Unida
                    </span>
                ) : (
                    <span className="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                        Disponible
                    </span>
                )}
            </div>
        </div>
    );
};

const RestaurantePlanoCtaReserva = ({
    alAbrirReserva,
}: {
    alAbrirReserva: () => void;
}) => (
    <div className="mt-2 flex flex-col items-center justify-between gap-4 rounded-3xl border border-primary/20 bg-gradient-to-r from-primary/10 via-background to-primary/5 p-6 shadow-sm sm:flex-row">
        <div className="space-y-1 text-center sm:text-left">
            <h4 className="text-base font-black text-foreground">
                ¿Listo para reservar tu mesa favorita?
            </h4>
            <p className="text-xs text-muted-foreground">
                Garantiza tu espacio con reserva inmediata y asignación
                inteligente.
            </p>
        </div>

        <Button
            type="button"
            onClick={alAbrirReserva}
            className="cursor-pointer rounded-2xl px-6 font-black shadow-md"
        >
            <Calendar className="mr-2 size-4" />
            Reservar Mesa Ahora
        </Button>
    </div>
);

interface PropsRestaurantePlanoMesasVisual {
    mesas?: MesaData[];
    mesaSeleccionadaId?: number | null;
    mesasUnidasIds?: number[];
    alSeleccionarMesa?: (mesa: MesaData) => void;
    alAbrirReserva?: (mesa?: MesaData) => void;
    esModoModal?: boolean;
}

export const RestaurantePlanoMesasVisual = ({
    mesas = [],
    mesaSeleccionadaId,
    mesasUnidasIds = [],
    alSeleccionarMesa,
    alAbrirReserva,
    esModoModal = false,
}: PropsRestaurantePlanoMesasVisual) => {
    const [zonaFiltro, setZonaFiltro] = useState<
        ClaveZonaRestaurante | 'todas'
    >('todas');
    const [comensalesFiltro, setComensalesFiltro] = useState<number | null>(
        null,
    );
    const [idSeleccionadaInterna, setIdSeleccionadaInterna] = useState<
        number | null
    >(mesaSeleccionadaId ?? null);

    // Si no hay mesas recibidas, generar mesas por defecto bien organizadas
    const mesasEfectivas = useMemo(() => {
        if (Array.isArray(mesas) && mesas.length > 0) {
            return [...mesas].sort(
                (a, b) => (a.orden ?? a.id) - (b.orden ?? b.id),
            );
        }

        return mesasPorDefecto;
    }, [mesas]);

    // Agrupar mesas por zona
    const mesasPorZona = useMemo(() => {
        const grupos: Record<ClaveZonaRestaurante, MesaData[]> = {
            interior: [],
            terraza: [],
            barra: [],
            vip: [],
        };

        mesasEfectivas.forEach((mesa) => {
            const zonaNorm = normalizarClaveZona(mesa.zona);
            grupos[zonaNorm].push(mesa);
        });

        return grupos;
    }, [mesasEfectivas]);

    const idActiva = mesaSeleccionadaId ?? idSeleccionadaInterna;

    const handleMesaClick = (mesa: MesaData) => {
        setIdSeleccionadaInterna(mesa.id);

        if (alSeleccionarMesa) {
            alSeleccionarMesa(mesa);
        }
    };

    const zonasVisibles: ClaveZonaRestaurante[] =
        zonaFiltro === 'todas'
            ? (Object.keys(ZONAS_RESTAURANTE) as ClaveZonaRestaurante[])
            : [zonaFiltro];

    return (
        <div className="flex flex-col gap-6">
            {/* Cabecera y Filtros si no es modo modal compacto */}
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {/* Selector de Zonas */}
                <div className="flex flex-wrap items-center gap-1.5 rounded-2xl border border-border/60 bg-muted/60 p-1 dark:bg-muted/30">
                    <Button
                        type="button"
                        variant={zonaFiltro === 'todas' ? 'default' : 'ghost'}
                        size="sm"
                        onClick={() => setZonaFiltro('todas')}
                        className={`cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                            zonaFiltro === 'todas'
                                ? 'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        Todas las Zonas
                    </Button>
                    {(
                        Object.entries(ZONAS_RESTAURANTE) as Array<
                            [ClaveZonaRestaurante, ZonaRestauranteConfig]
                        >
                    ).map(([key, config]) => {
                        const Icono = config.icono;
                        const esActivo = zonaFiltro === key;

                        return (
                            <Button
                                key={key}
                                type="button"
                                variant={esActivo ? 'default' : 'ghost'}
                                size="sm"
                                onClick={() => setZonaFiltro(key)}
                                className={`flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                    esActivo
                                        ? 'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                <Icono className="size-3.5" />
                                <span>{config.nombre.split(' ')[0]}</span>
                            </Button>
                        );
                    })}
                </div>

                {/* Filtro por Capacidad */}
                <div className="flex items-center gap-2">
                    <span className="text-xs font-semibold text-muted-foreground">
                        Comensales:
                    </span>
                    <div className="flex items-center gap-1">
                        {[null, 2, 4, 6].map((num) => (
                            <Button
                                key={num === null ? 'todos' : num}
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => setComensalesFiltro(num)}
                                className={`cursor-pointer rounded-lg border px-2.5 py-1 text-[11px] font-bold transition-all ${
                                    comensalesFiltro === num
                                        ? 'border-primary bg-primary/10 font-black text-primary hover:bg-primary/20 dark:text-rose-400'
                                        : 'border-border/60 text-muted-foreground hover:bg-muted/40'
                                }`}
                            >
                                {num === null ? 'Todos' : `${num}+`}
                            </Button>
                        ))}
                    </div>
                </div>
            </div>

            {/* Leyenda Visual */}
            <div className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-border/60 bg-muted/20 px-4 py-2.5 text-xs">
                <div className="flex flex-wrap items-center gap-4">
                    <div className="flex items-center gap-1.5">
                        <span className="size-3 rounded-full bg-emerald-500 ring-2 ring-emerald-500/20" />
                        <span className="text-muted-foreground">
                            Mesa Individual
                        </span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="size-3 rounded-full bg-amber-500 ring-2 ring-amber-500/20" />
                        <span className="text-muted-foreground">
                            Unión Sugerida
                        </span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="size-3 rounded-full bg-primary ring-2 ring-primary/30" />
                        <span className="text-muted-foreground">
                            Mesa Seleccionada
                        </span>
                    </div>
                </div>

                <span className="text-[11px] text-muted-foreground italic">
                    💡 Haz clic en cualquier mesa para seleccionarla y reservar
                </span>
            </div>

            {/* Cuadrícula de Zonas y Mesas */}
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {zonasVisibles.map((zonaKey) => {
                    const config =
                        ZONAS_RESTAURANTE[zonaKey] ??
                        ZONAS_RESTAURANTE.interior;
                    const mesasZona = mesasPorZona[zonaKey] ?? [];
                    const IconoZona = config.icono;

                    return (
                        <div
                            key={zonaKey}
                            className={`flex flex-col rounded-3xl border ${config.colorBorde} ${config.colorFondo} p-5 shadow-xs transition-all duration-200`}
                        >
                            {/* Cabecera de la Zona */}
                            <div className="flex items-center justify-between border-b border-border/40 pb-3">
                                <div className="flex items-center gap-2.5">
                                    <div
                                        className={`rounded-xl p-2 ${config.colorBadge}`}
                                    >
                                        <IconoZona className="size-4" />
                                    </div>
                                    <div>
                                        <h3
                                            className={`text-sm font-black tracking-tight ${config.colorTitulo}`}
                                        >
                                            {config.nombre}
                                        </h3>
                                        <p className="text-[11px] text-muted-foreground">
                                            {config.descripcion}
                                        </p>
                                    </div>
                                </div>

                                <span
                                    className={`rounded-full border px-2.5 py-0.5 text-[10px] font-black ${config.colorBadge}`}
                                >
                                    {mesasZona.length}{' '}
                                    {mesasZona.length === 1 ? 'mesa' : 'mesas'}
                                </span>
                            </div>

                            {/* Distribución de Mesas en la Zona */}
                            <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                {mesasZona.length > 0 ? (
                                    mesasZona.map((mesa) => {
                                        const esSeleccionada =
                                            idActiva === mesa.id;
                                        const esUnida =
                                            Array.isArray(mesasUnidasIds) &&
                                            mesasUnidasIds.includes(mesa.id);

                                        return (
                                            <RestauranteMesaCardInterna
                                                key={mesa.id}
                                                mesa={mesa}
                                                esSeleccionada={esSeleccionada}
                                                esUnida={esUnida}
                                                alSeleccionar={handleMesaClick}
                                            />
                                        );
                                    })
                                ) : (
                                    <div className="col-span-full py-8 text-center text-xs text-muted-foreground">
                                        No hay mesas disponibles con los filtros
                                        actuales en esta zona.
                                    </div>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* CTA Inferior para Reservar Mesa si está en la página principal */}
            {!esModoModal && alAbrirReserva && (
                <RestaurantePlanoCtaReserva
                    alAbrirReserva={() => alAbrirReserva()}
                />
            )}
        </div>
    );
};
