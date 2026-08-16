import { AlertTriangle, CalendarDays } from 'lucide-react';
import React from 'react';
import type { DateRange } from 'react-day-picker';
import { Calendar } from '@/modulos/compartido/ui/calendario';
import {
    CampoGrupo,
    Campo,
    EtiquetaCampo,
} from '@/modulos/compartido/ui/campo';
import { Input } from '@/modulos/compartido/ui/entrada';

interface PropiedadesSelectorFechas {
    fechaCheckIn: string;
    fechaCheckOut: string;
    nombreCliente: string;
    telefonoCliente: string;
    emailCliente: string;
    nochesCalculadas: number;
    totalHabitacionesCategoria: number;
    disponibilidadEntrada: {
        ocupadas: number;
        total: number;
        disponibles: number;
        agotado: boolean;
    } | null;
    rangoExactoDisponible: boolean | null;
    recomendacionesDisponibilidad: Array<{
        fecha_check_in: string;
        fecha_check_out: string;
        noches: number;
        disponibles_minimos: number;
    }>;
    mesesCalendario: number;
    fechaSeleccionada: DateRange | undefined;
    esFechaDeshabilitada: (date: Date) => boolean;
    fechaEstaAgotada: (date: Date) => boolean;
    onSelectRangoFechas: (rango?: DateRange) => void;
    onLimpiarFechas: () => void;
    onAplicarRecomendacion: (checkIn: string, checkOut: string) => void;
    onNombreChange: (val: string) => void;
    onTelefonoChange: (val: string) => void;
    onEmailChange: (val: string) => void;
}

export function SelectorFechasEstancia({
    fechaCheckIn,
    fechaCheckOut,
    nombreCliente,
    telefonoCliente,
    emailCliente,
    nochesCalculadas,
    totalHabitacionesCategoria,
    disponibilidadEntrada,
    rangoExactoDisponible,
    recomendacionesDisponibilidad,
    fechaSeleccionada,
    esFechaDeshabilitada,
    fechaEstaAgotada,
    onSelectRangoFechas,
    onLimpiarFechas,
    onAplicarRecomendacion,
    onNombreChange,
    onTelefonoChange,
    onEmailChange,
}: PropiedadesSelectorFechas) {
    return (
        <div className="animate-in fade-in-50 space-y-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
            <div className="flex flex-col gap-1 border-b border-border/40 pb-4">
                <div className="flex items-center gap-2">
                    <span className="flex size-7 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <CalendarDays className="size-4" />
                    </span>
                    <h2 className="text-lg font-black text-foreground md:text-xl">
                        Fechas de Estancia &{' '}
                        <span className="font-serif font-normal text-primary italic">
                            Titular
                        </span>
                    </h2>
                </div>
                <p className="text-xs text-muted-foreground">
                    Seleccione los días de su visita y los datos de quien
                    coordina la reserva
                </p>
            </div>

            {totalHabitacionesCategoria === 0 && (
                <div className="flex items-center gap-3 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs font-semibold text-rose-700 dark:text-rose-300">
                    <AlertTriangle className="size-5 shrink-0 text-rose-600 dark:text-rose-400" />
                    <span>
                        Actualmente esta habitación no está disponible para
                        nuevas reservas. El calendario ha sido deshabilitado
                        automáticamente.
                    </span>
                </div>
            )}

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_300px]">
                {/* Bloque Calendario */}
                <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
                    {/* Header con leyenda y botón de limpiar */}
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-border/60 bg-muted/20 px-5 py-3.5">
                        <div className="flex items-center gap-2">
                            <span className="text-xs font-black text-foreground">
                                Calendario de Disponibilidad
                            </span>
                            {(fechaCheckIn || fechaCheckOut) && (
                                <button
                                    type="button"
                                    onClick={onLimpiarFechas}
                                    className="text-[11px] font-bold text-primary hover:underline"
                                >
                                    (Limpiar)
                                </button>
                            )}
                        </div>

                        <div className="flex flex-wrap items-center gap-4 text-[11px] font-bold text-muted-foreground">
                            <span className="inline-flex items-center gap-1.5">
                                <span className="h-3 w-3 rounded-md bg-primary shadow-xs" />
                                Seleccionado
                            </span>
                            <span className="inline-flex items-center gap-1.5">
                                <span className="font-black text-primary underline underline-offset-2">
                                    Hoy
                                </span>
                            </span>
                            <span className="inline-flex items-center gap-1.5">
                                <span className="relative flex h-3 w-3 items-center justify-center rounded-md border border-border/80 bg-muted opacity-40">
                                    <span className="h-0.5 w-full rotate-45 bg-muted-foreground" />
                                </span>
                                No disponible
                            </span>
                        </div>
                    </div>

                    {/* Componente Calendario estándar */}
                    <Calendar
                        mode="range"
                        selected={fechaSeleccionada}
                        onSelect={onSelectRangoFechas}
                        numberOfMonths={1}
                        disabled={esFechaDeshabilitada}
                        modifiers={{
                            agotado: fechaEstaAgotada,
                        }}
                        modifiersClassNames={{
                            agotado:
                                '!cursor-not-allowed !pointer-events-none !opacity-25 !text-muted-foreground/40 !line-through decoration-muted-foreground/50 !bg-muted/30',
                        }}
                    />
                </div>

                {/* Panel lateral de información */}
                <div className="flex flex-col gap-3">
                    {/* Check-in */}
                    <div className="rounded-2xl border border-border/60 bg-card p-4 shadow-xs">
                        <span className="mb-0.5 block text-[10px] font-black tracking-widest text-muted-foreground uppercase">
                            Check-in
                        </span>
                        <span className="text-sm font-black text-foreground">
                            {fechaCheckIn || 'Por seleccionar'}
                        </span>
                    </div>

                    {/* Check-out */}
                    <div className="rounded-2xl border border-border/60 bg-card p-4 shadow-xs">
                        <span className="mb-0.5 block text-[10px] font-black tracking-widest text-muted-foreground uppercase">
                            Check-out
                        </span>
                        <span className="text-sm font-black text-foreground">
                            {fechaCheckOut || 'Por seleccionar'}
                        </span>
                    </div>

                    {/* Disponibilidad — badge de alta legibilidad */}
                    {(() => {
                        const disp = disponibilidadEntrada;
                        const disponibles = disp
                            ? disp.disponibles
                            : totalHabitacionesCategoria;
                        const total = disp
                            ? disp.total
                            : totalHabitacionesCategoria;
                        const pct = total > 0 ? disponibles / total : 0;
                        const colorClass =
                            pct === 0
                                ? 'border-rose-500/25 bg-rose-500/10 text-rose-700 dark:text-rose-300'
                                : pct <= 0.35
                                  ? 'border-amber-500/25 bg-amber-500/10 text-amber-700 dark:text-amber-300'
                                  : 'border-emerald-500/25 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300';
                        const dotClass =
                            pct === 0
                                ? 'bg-rose-500'
                                : pct <= 0.35
                                  ? 'bg-amber-500'
                                  : 'bg-emerald-500';

                        return (
                            <div
                                className={`rounded-2xl border p-4 shadow-xs ${colorClass}`}
                            >
                                <span className="mb-1 block text-[10px] font-black tracking-widest text-muted-foreground uppercase">
                                    Disponibilidad
                                </span>
                                <span className="flex items-center gap-2 text-sm font-black text-foreground">
                                    <span
                                        className={`h-2.5 w-2.5 shrink-0 rounded-full ${dotClass}`}
                                    />
                                    {disponibles > 0
                                        ? 'Habitación disponible'
                                        : 'Sin disponibilidad'}
                                </span>
                            </div>
                        );
                    })()}

                    {rangoExactoDisponible === false &&
                        recomendacionesDisponibilidad.length > 0 && (
                            <div className="rounded-2xl border border-amber-500/25 bg-amber-500/8 p-4 text-xs shadow-xs">
                                <span className="mb-2 block font-black text-amber-800 dark:text-amber-200">
                                    Alternativas disponibles
                                </span>
                                <div className="space-y-2">
                                    {recomendacionesDisponibilidad.map(
                                        (recomendacion) => (
                                            <button
                                                key={`${recomendacion.fecha_check_in}-${recomendacion.fecha_check_out}`}
                                                type="button"
                                                onClick={() =>
                                                    onAplicarRecomendacion(
                                                        recomendacion.fecha_check_in,
                                                        recomendacion.fecha_check_out,
                                                    )
                                                }
                                                className="w-full rounded-xl border border-amber-500/25 bg-background px-3 py-2 text-left font-bold text-foreground transition hover:border-amber-500/50 hover:bg-amber-500/10"
                                            >
                                                <span className="block">
                                                    {
                                                        recomendacion.fecha_check_in
                                                    }{' '}
                                                    al{' '}
                                                    {
                                                        recomendacion.fecha_check_out
                                                    }
                                                </span>
                                                <span className="text-[11px] font-semibold text-muted-foreground">
                                                    {recomendacion.noches}{' '}
                                                    noche(s),{' '}
                                                    {
                                                        recomendacion.disponibles_minimos
                                                    }{' '}
                                                    hab. disponibles
                                                </span>
                                            </button>
                                        ),
                                    )}
                                </div>
                            </div>
                        )}

                    {/* Nota informativa inteligente */}
                    <div className="rounded-2xl border border-border/80 bg-muted/40 p-4 text-xs text-muted-foreground shadow-xs">
                        <span className="mb-1 block font-black text-foreground">
                            Selección Inteligente
                        </span>
                        Los días ocupados de esta habitación se bloquean
                        automáticamente para evitar cruces de reservas.
                    </div>
                </div>
            </div>

            <div className="flex items-center justify-between rounded-2xl bg-muted/50 p-3.5 text-xs font-medium">
                <span className="text-muted-foreground">
                    Duración de la Estancia:
                </span>
                <span className="rounded-xl bg-background px-3 py-1 font-extrabold text-foreground shadow-xs">
                    {nochesCalculadas} noche(s)
                </span>
            </div>

            <CampoGrupo className="border-t border-border/40 pt-4">
                <Campo>
                    <EtiquetaCampo htmlFor="nombre-titular">
                        Nombre Completo del Titular *
                    </EtiquetaCampo>
                    <Input
                        id="nombre-titular"
                        type="text"
                        required
                        value={nombreCliente}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                            onNombreChange(e.target.value)
                        }
                        placeholder="Ej. Ana María Rodríguez"
                    />
                </Campo>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Campo>
                        <EtiquetaCampo htmlFor="telefono-titular">
                            Teléfono Móvil *
                        </EtiquetaCampo>
                        <Input
                            id="telefono-titular"
                            type="text"
                            required
                            value={telefonoCliente}
                            onChange={(
                                e: React.ChangeEvent<HTMLInputElement>,
                            ) => onTelefonoChange(e.target.value)}
                            placeholder="+505 8888 8888"
                        />
                    </Campo>

                    <Campo>
                        <EtiquetaCampo htmlFor="email-titular">
                            Correo Electrónico
                        </EtiquetaCampo>
                        <Input
                            id="email-titular"
                            type="email"
                            value={emailCliente}
                            onChange={(
                                e: React.ChangeEvent<HTMLInputElement>,
                            ) => onEmailChange(e.target.value)}
                            placeholder="correo@ejemplo.com"
                        />
                    </Campo>
                </div>
            </CampoGrupo>
        </div>
    );
}
