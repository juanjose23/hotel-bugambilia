import { useState, useMemo } from 'react';
import {
    mesasPorDefecto,
    normalizarClaveZona,
    ZONAS_RESTAURANTE,
} from '../constants';
import type { ZonaRestauranteConfig } from '../constants';
import type { MesaData } from '../types';
import { RestauranteMesaCardInterna } from './RestauranteMesaCardInterna';
import { RestaurantePlanoCtaReserva } from './RestaurantePlanoCtaReserva';

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
    const [zonaFiltro, setZonaFiltro] = useState<string>('todas');
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
        const grupos: Record<
            'interior' | 'terraza' | 'barra' | 'vip',
            MesaData[]
        > = {
            interior: [],
            terraza: [],
            barra: [],
            vip: [],
        };

        mesasEfectivas.forEach((mesa) => {
            const zKey = normalizarClaveZona(mesa.zona);
            const cap = Number(mesa.capacidad || 2);

            if (comensalesFiltro === null || cap >= comensalesFiltro) {
                grupos[zKey].push(mesa);
            }
        });

        return grupos;
    }, [mesasEfectivas, comensalesFiltro]);

    const zonasVisibles = useMemo((): Array<
        'interior' | 'terraza' | 'barra' | 'vip'
    > => {
        if (zonaFiltro === 'todas') {
            return ['interior', 'terraza', 'barra', 'vip'];
        }

        const keyNorm = normalizarClaveZona(zonaFiltro);

        return [keyNorm];
    }, [zonaFiltro]);

    const idActiva =
        mesaSeleccionadaId !== undefined
            ? mesaSeleccionadaId
            : idSeleccionadaInterna;

    const handleMesaClick = (mesa: MesaData) => {
        setIdSeleccionadaInterna(mesa.id);

        if (alSeleccionarMesa) {
            alSeleccionarMesa(mesa);
        }

        if (alAbrirReserva) {
            alAbrirReserva(mesa);
        }
    };

    return (
        <div className="flex flex-col gap-6">
            {/* Cabecera y Filtros si no es modo modal compacto */}
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {/* Selector de Zonas */}
                <div className="flex flex-wrap items-center gap-1.5 rounded-2xl border border-border/60 bg-muted/60 p-1 dark:bg-muted/30">
                    <button
                        type="button"
                        onClick={() => setZonaFiltro('todas')}
                        className={`cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                            zonaFiltro === 'todas'
                                ? 'bg-primary text-primary-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        Todas las Zonas
                    </button>
                    {(
                        Object.entries(ZONAS_RESTAURANTE) as Array<
                            [string, ZonaRestauranteConfig]
                        >
                    ).map(([key, config]) => {
                        const Icono = config.icono;
                        const esActivo = zonaFiltro === key;

                        return (
                            <button
                                key={key}
                                type="button"
                                onClick={() => setZonaFiltro(key)}
                                className={`flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                    esActivo
                                        ? 'bg-primary text-primary-foreground shadow-xs'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                <Icono className="size-3.5" />
                                <span>{config.nombre.split(' ')[0]}</span>
                            </button>
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
                            <button
                                key={num === null ? 'todos' : num}
                                type="button"
                                onClick={() => setComensalesFiltro(num)}
                                className={`cursor-pointer rounded-lg border px-2.5 py-1 text-[11px] font-bold transition-all ${
                                    comensalesFiltro === num
                                        ? 'border-primary bg-primary/10 font-black text-primary dark:text-rose-400'
                                        : 'border-border/60 text-muted-foreground hover:bg-muted/40'
                                }`}
                            >
                                {num === null ? 'Todos' : `${num}+`}
                            </button>
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
