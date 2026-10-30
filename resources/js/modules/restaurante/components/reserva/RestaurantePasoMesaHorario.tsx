import {
    Minus,
    Plus,
    Users,
    Loader2,
    MapPin,
    CheckCircle2,
    Sparkles,
    ChevronDown,
    ChevronUp,
    UtensilsCrossed,
} from 'lucide-react';
import { useState } from 'react';
import type { FieldErrors, UseFormRegister } from 'react-hook-form';
import { AvailabilitySkeleton } from '@/modules/shared/components/skeletons/PageSkeleton';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Field,
    FieldError,
    FieldLabel,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import {
    iconoTurnoRapido,
    OPCIONES_COMENSALES,
    TURNOS_RESTAURANTE,
} from '../../constants';
import type { FormReservaMesa } from '../../schemas/reservaMesaSchema';
import type { DisponibilidadMesasResponse } from '../../services/restauranteService';
import type { MesaData } from '../../types';
import { RestaurantePlanoMesasVisual } from '../RestaurantePlanoMesasVisual';

export type TurnoFiltro = 'todos' | 'Desayuno' | 'Almuerzo' | 'Cena';

export interface PasoMesaHorarioProps {
    register: UseFormRegister<FormReservaMesa>;
    errors: FieldErrors<FormReservaMesa>;
    disponibilidad: DisponibilidadMesasResponse | null;
    cargandoDisponibilidad: boolean;
    horaSeleccionada: string;
    adultosSeleccionados: number;
    espacioIdSeleccionado: number | null | undefined;
    mesas: MesaData[];
    cambiarFecha: (fecha: string) => void;
    seleccionarHora: (hora: string) => void;
    ajustarComensales: (delta: number) => void;
    seleccionarComensales: (numero: number) => void;
    seleccionarMesaEnPlano: (mesa: MesaData) => void;
    variante?: 'pagina' | 'modal';
}

export const RestaurantePasoMesaHorario = ({
    register,
    errors,
    disponibilidad,
    cargandoDisponibilidad,
    horaSeleccionada,
    adultosSeleccionados,
    espacioIdSeleccionado,
    mesas,
    cambiarFecha,
    seleccionarHora,
    ajustarComensales,
    seleccionarComensales,
    seleccionarMesaEnPlano,
    variante = 'pagina',
}: PasoMesaHorarioProps) => {
    const [filtroTurno, setFiltroTurno] = useState<TurnoFiltro>('todos');
    const [mostrarPlanoAvanzado, setMostrarPlanoAvanzado] = useState(false);
    const [zonaPreferencia, setZonaPreferencia] =
        useState<string>('cualquiera');
    const [mesaSeleccionadaManual, setMesaSeleccionadaManual] =
        useState<MesaData | null>(null);

    const esModal = variante === 'modal';
    const hoy = new Date().toISOString().split('T')[0];
    const campoFecha = register('fecha');

    const mesaAsignada = !disponibilidad?.requiere_union
        ? disponibilidad?.mesa_asignada
        : null;

    const horariosFiltrados =
        disponibilidad?.horarios_disponibles.filter((slot) => {
            if (filtroTurno === 'todos') {
                return true;
            }

            return slot.turno === filtroTurno;
        }) ?? [];

    const manejarSeleccionMesa = (mesa: MesaData) => {
        setMesaSeleccionadaManual(mesa);
        seleccionarMesaEnPlano(mesa);
    };

    return (
        <div className="space-y-6">
            {/* 1. "¿Cuándo quieres venir?" - Fecha, Comensales y Horarios */}
            <div
                className={`space-y-6 rounded-3xl border border-border/80 bg-card shadow-xs ${
                    esModal ? 'p-4' : 'p-6'
                }`}
            >
                <div className="flex items-center gap-3 border-b border-border/60 pb-4">
                    <div className="flex size-10 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary dark:text-rose-400">
                        <UtensilsCrossed className="size-5" />
                    </div>
                    <div>
                        <h3 className="text-base font-black text-foreground">
                            ¿Cuándo quieres venir?
                        </h3>
                        <p className="text-xs text-muted-foreground">
                            Indica la fecha, cantidad de comensales y tu horario
                            preferido.
                        </p>
                    </div>
                </div>

                {/* Fecha y Cantidad de Personas */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel className="text-xs font-bold text-foreground">
                            Fecha de Visita *
                        </FieldLabel>
                        <Input
                            type="date"
                            min={hoy}
                            className="h-11 rounded-2xl text-xs font-bold"
                            {...campoFecha}
                            onChange={(e) => {
                                campoFecha.onChange(e);
                                cambiarFecha(e.target.value);
                            }}
                        />
                        {errors.fecha && (
                            <FieldError className="text-[10px]">
                                {errors.fecha.message}
                            </FieldError>
                        )}
                    </Field>

                    <Field>
                        <FieldLabel className="flex items-center justify-between text-xs font-bold text-foreground">
                            <span className="flex items-center gap-1.5">
                                <Users className="size-3.5 text-primary dark:text-rose-400" />
                                Personas *
                            </span>
                            <span className="text-[11px] font-normal text-muted-foreground">
                                {adultosSeleccionados === 1
                                    ? '1 comensal'
                                    : `${adultosSeleccionados} comensales`}
                            </span>
                        </FieldLabel>
                        <div className="flex items-center gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                onClick={() => ajustarComensales(-1)}
                                disabled={adultosSeleccionados <= 1}
                                className="h-11 w-11 shrink-0 rounded-2xl"
                            >
                                <Minus className="size-4" />
                            </Button>
                            <Input
                                type="number"
                                min={1}
                                max={30}
                                className="h-11 rounded-2xl text-center text-xs font-black"
                                {...register('adultos', {
                                    valueAsNumber: true,
                                })}
                            />
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                onClick={() => ajustarComensales(1)}
                                disabled={adultosSeleccionados >= 30}
                                className="h-11 w-11 shrink-0 rounded-2xl"
                            >
                                <Plus className="size-4" />
                            </Button>
                        </div>
                        {errors.adultos && (
                            <FieldError className="text-[10px]">
                                {errors.adultos.message}
                            </FieldError>
                        )}
                    </Field>
                </div>

                {/* Atajos de Comensales */}
                <div className="flex flex-wrap items-center gap-2 pt-1">
                    <span className="text-xs font-bold text-muted-foreground">
                        Atajos rápidos:
                    </span>
                    {OPCIONES_COMENSALES.map((num) => (
                        <Button
                            key={num}
                            type="button"
                            variant={
                                adultosSeleccionados === num
                                    ? 'default'
                                    : 'outline'
                            }
                            size="sm"
                            onClick={() => seleccionarComensales(num)}
                            className={`h-8 rounded-xl px-3 text-xs font-bold ${
                                adultosSeleccionados === num
                                    ? 'bg-primary text-primary-foreground shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            {num} {num === 1 ? 'persona' : 'personas'}
                        </Button>
                    ))}
                </div>

                {/* Horarios de Llegada */}
                <div className="space-y-3 border-t border-border/60 pt-3">
                    <div className="flex items-center justify-between">
                        <FieldLabel className="text-xs font-bold text-foreground">
                            Hora de Llegada *
                        </FieldLabel>
                        {cargandoDisponibilidad && (
                            <span className="inline-flex items-center gap-1.5 text-xs font-bold text-primary">
                                <Loader2 className="size-3.5 animate-spin" />
                                <span>Verificando disponibilidad...</span>
                            </span>
                        )}
                    </div>

                    {/* Selector de Turno */}
                    <div className="flex items-center gap-1.5 rounded-2xl border border-border/60 bg-muted/40 p-1 text-xs">
                        {TURNOS_RESTAURANTE.map((turno) => {
                            const activo = filtroTurno === turno.id;
                            const Icon = turno.icono;

                            return (
                                <Button
                                    key={turno.id}
                                    type="button"
                                    variant={activo ? 'default' : 'ghost'}
                                    size="sm"
                                    onClick={() =>
                                        setFiltroTurno(turno.id as TurnoFiltro)
                                    }
                                    className={`flex h-auto flex-1 items-center justify-center gap-1.5 rounded-xl py-2 text-xs font-bold transition-all ${
                                        activo
                                            ? 'bg-background font-black text-foreground shadow-xs'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    <Icon className="size-3.5" />
                                    <span>{turno.label}</span>
                                </Button>
                            );
                        })}
                    </div>

                    {/* Chips de Horarios */}
                    {cargandoDisponibilidad ? (
                        <AvailabilitySkeleton />
                    ) : (
                        <div className="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-6">
                            {horariosFiltrados.map((slot) => {
                                const esSeleccionado =
                                    horaSeleccionada === slot.hora;
                                const IconoTurno = iconoTurnoRapido(slot.turno);

                                return (
                                    <Button
                                        key={slot.hora}
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        disabled={!slot.disponible}
                                        onClick={() =>
                                            seleccionarHora(slot.hora)
                                        }
                                        className={`flex h-auto flex-col items-center justify-center rounded-2xl p-2.5 text-xs transition-all ${
                                            esSeleccionado
                                                ? 'border-2 border-primary bg-primary font-black text-primary-foreground shadow-md ring-2 ring-primary/20 hover:bg-primary/90'
                                                : slot.disponible
                                                  ? 'border border-border/80 bg-card font-bold text-foreground hover:border-primary/50 hover:bg-muted'
                                                  : 'cursor-not-allowed border border-border/30 bg-muted/20 text-muted-foreground/30 line-through opacity-40'
                                        }`}
                                    >
                                        <div className="flex items-center gap-1">
                                            {!esSeleccionado && IconoTurno && (
                                                <IconoTurno className="size-3 text-primary" />
                                            )}
                                            <span className="text-xs font-black">
                                                {slot.hora}
                                            </span>
                                        </div>
                                        <span className="mt-0.5 text-[10px] opacity-80">
                                            {slot.disponible
                                                ? slot.turno
                                                : 'Completo'}
                                        </span>
                                    </Button>
                                );
                            })}
                        </div>
                    )}
                    {errors.hora && (
                        <FieldError className="text-[10px]">
                            {errors.hora.message}
                        </FieldError>
                    )}
                </div>
            </div>

            {/* 2. "Disponibilidad de Mesa Confirmada" - Asignación Inteligente */}
            <div
                className={`space-y-4 rounded-3xl border border-border/80 bg-card shadow-xs ${
                    esModal ? 'p-4' : 'p-6'
                }`}
            >
                <div className="flex items-center justify-between">
                    <div>
                        <span className="text-[11px] font-black tracking-wider text-primary uppercase dark:text-rose-400">
                            Disponibilidad Confirmada
                        </span>
                        <h4 className="mt-0.5 text-base font-black text-foreground">
                            Asignación Automática de Mesa
                        </h4>
                    </div>

                    <div className="flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-black text-emerald-600 dark:text-emerald-400">
                        <CheckCircle2 className="size-4" />
                        <span>Mesa Garantizada</span>
                    </div>
                </div>

                {/* Tarjeta de Mesa Asignada */}
                <div className="rounded-2xl border border-primary/20 bg-primary/5 p-4 dark:bg-rose-950/20">
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div className="flex items-center gap-2">
                                <Sparkles className="size-4 text-primary dark:text-rose-400" />
                                <span className="text-sm font-black text-foreground">
                                    {disponibilidad?.requiere_union
                                        ? `Mesa unida para ${adultosSeleccionados} personas`
                                        : mesaAsignada?.nombre
                                          ? `Mesa: ${mesaAsignada.nombre}`
                                          : `Mesa lista para ${adultosSeleccionados} comensales`}
                                </span>
                            </div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {disponibilidad?.requiere_union
                                    ? `Unión de mesas (${disponibilidad.mesas_sugeridas_union.map((m) => m.nombre).join(' + ')}) preparada para tu grupo.`
                                    : 'Mesa compatible con tu horario y grupo sin demoras a la llegada.'}
                            </p>
                        </div>

                        {/* Preferencia de Ambiente */}
                        <div className="flex flex-wrap items-center gap-1.5 self-start sm:self-center">
                            <span className="mr-1 text-[11px] font-bold text-muted-foreground">
                                Ambiente:
                            </span>
                            {['Cualquiera', 'Interior', 'Terraza'].map(
                                (zona) => (
                                    <button
                                        key={zona}
                                        type="button"
                                        onClick={() =>
                                            setZonaPreferencia(
                                                zona.toLowerCase(),
                                            )
                                        }
                                        className={`rounded-xl px-2.5 py-1 text-[11px] font-bold transition-all ${
                                            zonaPreferencia ===
                                            zona.toLowerCase()
                                                ? 'bg-primary text-primary-foreground shadow-xs'
                                                : 'border border-border bg-background text-muted-foreground hover:text-foreground'
                                        }`}
                                    >
                                        {zona}
                                    </button>
                                ),
                            )}
                        </div>
                    </div>
                </div>

                {/* Selección Manual Avanzada Opcional (Toggle) */}
                <div className="pt-2">
                    <Button
                        type="button"
                        variant="ghost"
                        onClick={() =>
                            setMostrarPlanoAvanzado(!mostrarPlanoAvanzado)
                        }
                        className="h-auto w-full justify-between rounded-2xl border border-dashed border-border/80 bg-background/50 p-3 text-xs font-bold text-muted-foreground hover:bg-muted hover:text-foreground"
                    >
                        <div className="flex items-center gap-2">
                            <MapPin className="size-4 text-primary" />
                            <span>
                                ¿Deseas elegir una mesa específica en el plano?
                                (Opcional)
                            </span>
                        </div>
                        {mostrarPlanoAvanzado ? (
                            <ChevronUp className="size-4" />
                        ) : (
                            <ChevronDown className="size-4" />
                        )}
                    </Button>

                    {mostrarPlanoAvanzado && (
                        <div className="animate-in fade-in mt-4 rounded-2xl border border-border bg-background p-4 duration-200">
                            <p className="mb-3 text-xs text-muted-foreground">
                                Haz clic sobre la mesa de tu preferencia para
                                seleccionarla.
                            </p>
                            <RestaurantePlanoMesasVisual
                                mesas={mesas}
                                mesaSeleccionadaId={
                                    mesaSeleccionadaManual?.id ||
                                    mesaAsignada?.id ||
                                    espacioIdSeleccionado ||
                                    null
                                }
                                mesasUnidasIds={
                                    disponibilidad?.requiere_union
                                        ? disponibilidad.mesas_sugeridas_union.map(
                                              (m) => m.id,
                                          )
                                        : []
                                }
                                alSeleccionarMesa={manejarSeleccionMesa}
                                esModoModal={esModal}
                            />
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default RestaurantePasoMesaHorario;
