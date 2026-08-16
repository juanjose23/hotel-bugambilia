import { Gift, Sparkles, Check, Building2, Coins } from 'lucide-react';
import React, { useState } from 'react';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';
import type { OpcionesReserva } from '@/modulos/reservas/interfaces/opcionesReserva';

interface PropiedadesSeccionesAdicionalesReserva {
    opciones: OpcionesReserva;
    serviciosSeleccionados: number[];
    espaciosSeleccionados: number[];
    promocionId: number | null;
    onServiciosChange: (ids: number[]) => void;
    onEspaciosChange: (ids: number[]) => void;
    onPromocionChange: (id: number | null) => void;
}

export const SeccionesAdicionalesReserva = ({
    opciones,
    serviciosSeleccionados,
    espaciosSeleccionados,
    promocionId,
    onServiciosChange,
    onEspaciosChange,
    onPromocionChange,
}: PropiedadesSeccionesAdicionalesReserva) => {
    const [tabActivo, setTabActivo] = useState<
        'servicios' | 'espacios' | 'promociones'
    >('servicios');

    const alternar = (
        ids: number[],
        id: number,
        cambiar: (ids: number[]) => void,
    ) =>
        cambiar(
            ids.includes(id)
                ? ids.filter((actual) => actual !== id)
                : [...ids, id],
        );

    const costoServicios = opciones.servicios
        .filter((s) => serviciosSeleccionados.includes(s.id))
        .reduce((sum, s) => sum + (s.precio || 0), 0);
    const costoEspacios = opciones.espacios
        .filter((e) => espaciosSeleccionados.includes(e.id))
        .reduce((sum, e) => sum + (e.precio || 0), 0);
    const totalAdicionales = costoServicios + costoEspacios;
    const monedaSimbolo =
        opciones.servicios[0]?.moneda || opciones.espacios[0]?.moneda || 'C$';

    return (
        <div className="flex flex-col gap-6 font-sans">
            {/* Total acumulado en vivo */}
            <div className="flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-primary/20 bg-primary/5 p-4 shadow-xs">
                <div className="flex items-center gap-2">
                    <span className="flex size-8 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <Coins className="size-4" />
                    </span>
                    <div className="flex flex-col">
                        <span className="text-xs font-black text-foreground">
                            Experiencias & Complementos
                        </span>
                        <span className="text-[11px] text-muted-foreground">
                            {serviciosSeleccionados.length +
                                espaciosSeleccionados.length}{' '}
                            adicional(es) seleccionado(s)
                        </span>
                    </div>
                </div>
                <div className="text-right">
                    <span className="block text-[10px] font-black tracking-widest text-muted-foreground uppercase">
                        Subtotal Adicionales
                    </span>
                    <span className="text-base font-black text-primary">
                        + {monedaSimbolo} {formatearNumero(totalAdicionales)}
                    </span>
                </div>
            </div>

            {/* Pestañas de navegación elegantes */}
            <div className="flex rounded-2xl border border-border/40 bg-muted/50 p-1.5">
                <button
                    type="button"
                    onClick={() => setTabActivo('servicios')}
                    className={`flex flex-1 items-center justify-center gap-2 rounded-xl py-2.5 text-xs font-black transition-all ${
                        tabActivo === 'servicios'
                            ? 'bg-card text-foreground shadow-xs'
                            : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    <Sparkles className="size-3.5 text-primary" />
                    <span>Servicios ({opciones.servicios.length})</span>
                </button>

                <button
                    type="button"
                    onClick={() => setTabActivo('espacios')}
                    className={`flex flex-1 items-center justify-center gap-2 rounded-xl py-2.5 text-xs font-black transition-all ${
                        tabActivo === 'espacios'
                            ? 'bg-card text-foreground shadow-xs'
                            : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    <Building2 className="size-3.5 text-primary" />
                    <span>Espacios ({opciones.espacios.length})</span>
                </button>

                <button
                    type="button"
                    onClick={() => setTabActivo('promociones')}
                    className={`flex flex-1 items-center justify-center gap-2 rounded-xl py-2.5 text-xs font-black transition-all ${
                        tabActivo === 'promociones'
                            ? 'bg-card text-foreground shadow-xs'
                            : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    <Gift className="size-3.5 text-amber-500" />
                    <span>Promociones ({opciones.promociones.length})</span>
                </button>
            </div>

            {/* CONTENIDO TAB 1: SERVICIOS */}
            {tabActivo === 'servicios' && (
                <div className="flex flex-col gap-3">
                    {opciones.servicios.length === 0 ? (
                        <p className="rounded-2xl border border-border/40 bg-background p-6 text-center text-xs font-medium text-muted-foreground">
                            No hay servicios adicionales configurados
                            actualmente.
                        </p>
                    ) : (
                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            {opciones.servicios.map((item) => {
                                const isSelected =
                                    serviciosSeleccionados.includes(item.id);

                                return (
                                    <div
                                        key={item.id}
                                        onClick={() =>
                                            alternar(
                                                serviciosSeleccionados,
                                                item.id,
                                                onServiciosChange,
                                            )
                                        }
                                        className={`group relative flex cursor-pointer items-start justify-between gap-3 rounded-2xl border p-4 transition-all duration-200 ${
                                            isSelected
                                                ? 'border-primary bg-primary/10 shadow-xs ring-2 ring-primary/20'
                                                : 'border-border/80 bg-background hover:border-primary/50 hover:bg-primary/5'
                                        }`}
                                    >
                                        <div className="flex items-start gap-3">
                                            <div
                                                className={`mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-lg border transition-all ${
                                                    isSelected
                                                        ? 'border-primary bg-primary text-primary-foreground'
                                                        : 'border-muted-foreground/40 bg-background'
                                                }`}
                                            >
                                                {isSelected && (
                                                    <Check className="size-3.5 stroke-[3]" />
                                                )}
                                            </div>
                                            <div className="flex flex-col gap-0.5">
                                                <span className="text-xs font-black text-foreground group-hover:text-primary">
                                                    {item.nombre}
                                                </span>
                                                {item.descripcion && (
                                                    <span className="line-clamp-2 text-[11px] leading-relaxed text-muted-foreground">
                                                        {item.descripcion}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        <span className="shrink-0 text-xs font-black text-primary">
                                            + {item.moneda || 'C$'}{' '}
                                            {formatearNumero(item.precio || 0)}
                                        </span>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            )}

            {/* CONTENIDO TAB 2: ESPACIOS */}
            {tabActivo === 'espacios' && (
                <div className="flex flex-col gap-3">
                    {opciones.espacios.length === 0 ? (
                        <p className="rounded-2xl border border-border/40 bg-background p-6 text-center text-xs font-medium text-muted-foreground">
                            No hay espacios adicionales configurados
                            actualmente.
                        </p>
                    ) : (
                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            {opciones.espacios.map((item) => {
                                const isSelected =
                                    espaciosSeleccionados.includes(item.id);

                                return (
                                    <div
                                        key={item.id}
                                        onClick={() =>
                                            alternar(
                                                espaciosSeleccionados,
                                                item.id,
                                                onEspaciosChange,
                                            )
                                        }
                                        className={`group relative flex cursor-pointer items-start justify-between gap-3 rounded-2xl border p-4 transition-all duration-200 ${
                                            isSelected
                                                ? 'border-primary bg-primary/10 shadow-xs ring-2 ring-primary/20'
                                                : 'border-border/80 bg-background hover:border-primary/50 hover:bg-primary/5'
                                        }`}
                                    >
                                        <div className="flex items-start gap-3">
                                            <div
                                                className={`mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-lg border transition-all ${
                                                    isSelected
                                                        ? 'border-primary bg-primary text-primary-foreground'
                                                        : 'border-muted-foreground/40 bg-background'
                                                }`}
                                            >
                                                {isSelected && (
                                                    <Check className="size-3.5 stroke-[3]" />
                                                )}
                                            </div>
                                            <div className="flex flex-col gap-0.5">
                                                <span className="text-xs font-black text-foreground group-hover:text-primary">
                                                    {item.nombre}
                                                </span>
                                                {item.descripcion && (
                                                    <span className="line-clamp-2 text-[11px] leading-relaxed text-muted-foreground">
                                                        {item.descripcion}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        <span className="shrink-0 text-xs font-black text-primary">
                                            + {item.moneda || 'C$'}{' '}
                                            {formatearNumero(item.precio || 0)}
                                        </span>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            )}

            {/* CONTENIDO TAB 3: PROMOCIONES */}
            {tabActivo === 'promociones' && (
                <div className="flex flex-col gap-3">
                    {opciones.promociones.length === 0 ? (
                        <p className="rounded-2xl border border-border/40 bg-background p-6 text-center text-xs font-medium text-muted-foreground">
                            No hay promociones vigentes aplicables en este
                            momento.
                        </p>
                    ) : (
                        <div className="flex flex-col gap-3">
                            {opciones.promociones.map((item) => {
                                const isSelected = promocionId === item.id;

                                return (
                                    <div
                                        key={item.id}
                                        onClick={() =>
                                            onPromocionChange(
                                                isSelected ? null : item.id,
                                            )
                                        }
                                        className={`flex cursor-pointer items-center justify-between rounded-2xl border p-4 transition-all duration-200 ${
                                            isSelected
                                                ? 'border-amber-500 bg-amber-500/10 shadow-xs ring-2 ring-amber-500/20'
                                                : 'border-amber-500/20 bg-amber-500/5 hover:border-amber-500/40'
                                        }`}
                                    >
                                        <div className="flex items-center gap-3">
                                            <div
                                                className={`flex size-5 shrink-0 items-center justify-center rounded-lg border transition-all ${
                                                    isSelected
                                                        ? 'border-amber-600 bg-amber-600 text-white'
                                                        : 'border-muted-foreground/40 bg-background'
                                                }`}
                                            >
                                                {isSelected && (
                                                    <Check className="size-3.5 stroke-[3]" />
                                                )}
                                            </div>
                                            <div className="flex flex-col gap-0.5">
                                                <span className="text-xs font-black text-foreground">
                                                    {item.nombre}
                                                </span>
                                                <span className="text-[11px] font-medium text-muted-foreground">
                                                    Código:{' '}
                                                    <strong className="font-mono text-foreground">
                                                        {item.codigo}
                                                    </strong>{' '}
                                                    · {item.descuento}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}

                            {promocionId !== null && (
                                <button
                                    type="button"
                                    onClick={() => onPromocionChange(null)}
                                    className="self-start text-[11px] font-bold text-muted-foreground underline hover:text-foreground"
                                >
                                    Limpiar promoción seleccionada
                                </button>
                            )}
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};
