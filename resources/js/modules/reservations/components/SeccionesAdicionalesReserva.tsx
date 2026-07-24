import {
    Gift,
    PlusCircle,
    Sparkles,
    CheckCircle2,
    Building2,
} from 'lucide-react';
import React from 'react';
import type { OpcionesReserva } from '@/modules/reservations/types/opcionesReserva';
import { formatearNumero } from '@/modules/shared/utils/formato';

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
    // Calcular costo total acumulado de adicionales seleccionados
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
        <div className="space-y-6 font-sans">
            {/* Total acumulado en vivo */}
            {totalAdicionales > 0 && (
                <div className="animate-in fade-in flex items-center justify-between rounded-2xl border border-bugambilia-500/30 bg-bugambilia-500/10 p-3.5 duration-300">
                    <span className="flex items-center gap-1.5 text-xs font-bold text-foreground">
                        <Sparkles className="h-4 w-4 text-bugambilia-600" />
                        Total Adicionales Añadidos:
                    </span>
                    <span className="text-sm font-black text-bugambilia-600 dark:text-bugambilia-400">
                        + {monedaSimbolo} {formatearNumero(totalAdicionales)}
                    </span>
                </div>
            )}

            {/* 1. SERVICIOS ADICIONALES */}
            <section className="space-y-3">
                <div className="flex items-center justify-between">
                    <h4 className="flex items-center gap-2 text-xs font-extrabold tracking-wider text-foreground uppercase">
                        <PlusCircle className="h-4 w-4 text-bugambilia-600" />
                        Servicios Adicionales
                    </h4>
                    <span className="text-[10px] font-bold text-muted-foreground">
                        {serviciosSeleccionados.length} seleccionado(s)
                    </span>
                </div>

                {opciones.servicios.length === 0 ? (
                    <p className="rounded-2xl bg-muted/40 p-4 text-center text-xs font-medium text-muted-foreground">
                        No hay servicios adicionales configurados actualmente.
                    </p>
                ) : (
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {opciones.servicios.map((item) => {
                            const isSelected = serviciosSeleccionados.includes(
                                item.id,
                            );

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
                                    className={`group relative flex cursor-pointer items-start justify-between gap-3 rounded-2xl border p-3.5 transition-all ${
                                        isSelected
                                            ? 'scale-[1.01] border-bugambilia-600 bg-bugambilia-500/10 shadow-sm'
                                            : 'border-border/80 bg-card hover:border-bugambilia-400/60 hover:bg-muted/30'
                                    }`}
                                >
                                    <div className="flex min-w-0 items-start gap-2.5">
                                        <div
                                            className={`mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border transition-all ${
                                                isSelected
                                                    ? 'border-bugambilia-600 bg-bugambilia-600 text-white'
                                                    : 'border-muted-foreground/40 bg-background'
                                            }`}
                                        >
                                            {isSelected && (
                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                            )}
                                        </div>
                                        <div className="min-w-0">
                                            <span className="block truncate text-xs font-black text-foreground">
                                                {item.nombre}
                                            </span>
                                            {item.descripcion && (
                                                <span className="line-clamp-2 block text-[10px] leading-relaxed text-muted-foreground">
                                                    {item.descripcion}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <span className="shrink-0 text-xs font-black text-bugambilia-600 dark:text-bugambilia-400">
                                        + {item.moneda || 'C$'}{' '}
                                        {formatearNumero(item.precio || 0)}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                )}
            </section>

            {/* 2. ESPACIOS ADICIONALES */}
            <section className="space-y-3 border-t border-border/40 pt-4">
                <div className="flex items-center justify-between">
                    <h4 className="flex items-center gap-2 text-xs font-extrabold tracking-wider text-foreground uppercase">
                        <Building2 className="h-4 w-4 text-bugambilia-600" />
                        Espacios & Ambientes Adicionales
                    </h4>
                    <span className="text-[10px] font-bold text-muted-foreground">
                        {espaciosSeleccionados.length} seleccionado(s)
                    </span>
                </div>

                {opciones.espacios.length === 0 ? (
                    <p className="rounded-2xl bg-muted/40 p-4 text-center text-xs font-medium text-muted-foreground">
                        No hay espacios adicionales adicionales configurados
                        actualmente.
                    </p>
                ) : (
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {opciones.espacios.map((item) => {
                            const isSelected = espaciosSeleccionados.includes(
                                item.id,
                            );

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
                                    className={`group relative flex cursor-pointer items-start justify-between gap-3 rounded-2xl border p-3.5 transition-all ${
                                        isSelected
                                            ? 'scale-[1.01] border-bugambilia-600 bg-bugambilia-500/10 shadow-sm'
                                            : 'border-border/80 bg-card hover:border-bugambilia-400/60 hover:bg-muted/30'
                                    }`}
                                >
                                    <div className="flex min-w-0 items-start gap-2.5">
                                        <div
                                            className={`mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border transition-all ${
                                                isSelected
                                                    ? 'border-bugambilia-600 bg-bugambilia-600 text-white'
                                                    : 'border-muted-foreground/40 bg-background'
                                            }`}
                                        >
                                            {isSelected && (
                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                            )}
                                        </div>
                                        <div className="min-w-0">
                                            <span className="block truncate text-xs font-black text-foreground">
                                                {item.nombre}
                                            </span>
                                            {item.descripcion && (
                                                <span className="line-clamp-2 block text-[10px] leading-relaxed text-muted-foreground">
                                                    {item.descripcion}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <span className="shrink-0 text-xs font-black text-bugambilia-600 dark:text-bugambilia-400">
                                        + {item.moneda || 'C$'}{' '}
                                        {formatearNumero(item.precio || 0)}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                )}
            </section>

            {/* 3. PROMOCIONES VIGENTES */}
            <section className="space-y-3 border-t border-border/40 pt-4">
                <h4 className="flex items-center gap-2 text-xs font-extrabold tracking-wider text-foreground uppercase">
                    <Gift className="h-4 w-4 text-amber-500" />
                    Promociones & Descuentos Aplicables
                </h4>

                {opciones.promociones.length === 0 ? (
                    <p className="rounded-2xl bg-muted/40 p-4 text-center text-xs font-medium text-muted-foreground">
                        No hay promociones vigentes aplicables.
                    </p>
                ) : (
                    <div className="grid grid-cols-1 gap-2.5">
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
                                    className={`flex cursor-pointer items-center justify-between rounded-2xl border p-3.5 transition-all ${
                                        isSelected
                                            ? 'border-amber-500 bg-amber-500/10 shadow-sm'
                                            : 'border-amber-500/20 bg-amber-500/5 hover:border-amber-500/40'
                                    }`}
                                >
                                    <div className="flex min-w-0 items-center gap-3">
                                        <div
                                            className={`flex h-4 w-4 shrink-0 items-center justify-center rounded-full border transition-all ${
                                                isSelected
                                                    ? 'border-amber-600 bg-amber-600 text-white'
                                                    : 'border-muted-foreground/40 bg-background'
                                            }`}
                                        >
                                            {isSelected && (
                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                            )}
                                        </div>
                                        <div className="min-w-0">
                                            <span className="flex items-center gap-1.5 text-xs font-bold text-foreground">
                                                <Sparkles className="h-3.5 w-3.5 shrink-0 text-amber-500" />
                                                {item.nombre}
                                            </span>
                                            <span className="block text-[10px] text-muted-foreground">
                                                Código: {item.codigo} ·{' '}
                                                {item.descuento}
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
            </section>
        </div>
    );
};
