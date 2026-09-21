import { Calendar, Minus, Plus, Users, Loader2, MapPin } from 'lucide-react';
import { useState } from 'react';
import type { FieldErrors, UseFormRegister } from 'react-hook-form';
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
    const [mesaSeleccionadaManual, setMesaSeleccionadaManual] =
        useState<MesaData | null>(null);

    const esModal = variante === 'modal';
    const hoy = new Date().toISOString().split('T')[0];
    const campoFecha = register('fecha');
    const tamanoInput = esModal ? 'h-10' : 'h-11';
    const etiquetaBase = esModal ? 'text-[11px]' : 'text-xs';

    const mesaIndividualAsignada = !disponibilidad?.requiere_union
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
            <div
                className={`space-y-6 rounded-3xl border border-border/80 bg-card shadow-xs ${
                    esModal ? 'p-4' : 'p-6'
                }`}
            >
                <div className="flex items-center gap-3 border-b border-border/60 pb-4">
                    <div className="flex size-9 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary dark:text-rose-400">
                        <Calendar className="size-4" />
                    </div>
                    <div>
                        <h3 className="text-base font-black text-foreground">
                            Fecha, Comensales y Horario
                        </h3>
                        <p className="text-xs text-muted-foreground">
                            Indica cuándo y con cuántas personas nos visitarás.
                        </p>
                    </div>
                </div>

                {/* Sección 1: Selector de Fecha y Comensales */}
                <div className="space-y-3">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel
                                className={`${etiquetaBase} flex items-center gap-1.5 font-bold`}
                            >
                                {esModal && (
                                    <Calendar className="size-3.5 text-primary" />
                                )}
                                Fecha de Reserva *
                            </FieldLabel>
                            <Input
                                type="date"
                                min={hoy}
                                className={`${tamanoInput} rounded-xl text-xs font-medium`}
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
                            <FieldLabel
                                className={`${etiquetaBase} flex items-center justify-between font-bold`}
                            >
                                <span className="flex items-center gap-1.5">
                                    {esModal && (
                                        <Users className="size-3.5 text-primary" />
                                    )}
                                    Comensales *
                                </span>
                                <span className="text-[10px] font-normal text-muted-foreground">
                                    {adultosSeleccionados === 1
                                        ? '1 persona'
                                        : `${adultosSeleccionados} personas`}
                                </span>
                            </FieldLabel>
                            <div className="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    onClick={() => ajustarComensales(-1)}
                                    disabled={adultosSeleccionados <= 1}
                                    className={`${tamanoInput} w-11 shrink-0 cursor-pointer rounded-xl`}
                                >
                                    <Minus className="size-4" />
                                </Button>
                                <Input
                                    type="number"
                                    min={1}
                                    max={30}
                                    className={`${tamanoInput} rounded-xl text-center text-xs font-black`}
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
                                    className={`${tamanoInput} w-11 shrink-0 cursor-pointer rounded-xl`}
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

                    {/* Atajos de comensales rápidos */}
                    <div className="flex flex-wrap items-center gap-1.5 pt-1">
                        <span className="mr-1 text-[11px] text-muted-foreground">
                            Atajos:
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
                                className={`h-7 px-2.5 text-[11px] font-bold ${
                                    adultosSeleccionados === num
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-muted-foreground'
                                }`}
                            >
                                {num}p
                            </Button>
                        ))}
                    </div>
                </div>

                {/* Sección 2: Selector de Horarios y Turnos */}
                <div className="space-y-3 pt-2">
                    <div className="flex items-center justify-between">
                        <FieldLabel className="text-xs font-bold text-foreground">
                            Hora de Llegada *
                        </FieldLabel>
                        {cargandoDisponibilidad && (
                            <span className="inline-flex items-center gap-1 text-[10px] text-muted-foreground">
                                <Loader2 className="size-3 animate-spin text-primary" />
                                {esModal
                                    ? 'Actualizando...'
                                    : 'Consultando disponibilidad...'}
                            </span>
                        )}
                    </div>

                    <div className="flex items-center gap-1.5 rounded-xl border border-border/50 bg-muted/40 p-1 text-xs">
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
                                    className={`flex h-auto flex-1 cursor-pointer items-center justify-center gap-1 rounded-lg py-1.5 text-[11px] font-bold transition-all ${
                                        activo
                                            ? 'bg-background font-black text-foreground shadow-xs hover:bg-background/90'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    <Icon className="size-3" />
                                    <span
                                        className={
                                            esModal ? '' : 'hidden sm:inline'
                                        }
                                    >
                                        {turno.label}
                                    </span>
                                </Button>
                            );
                        })}
                    </div>

                    <div className="grid grid-cols-3 gap-1.5 sm:grid-cols-4 md:grid-cols-6">
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
                                    onClick={() => seleccionarHora(slot.hora)}
                                    className={`flex h-auto cursor-pointer flex-col items-center justify-center rounded-xl p-2 text-xs transition-all ${
                                        esSeleccionado
                                            ? 'border border-primary bg-primary font-black text-primary-foreground shadow-md ring-2 ring-primary/20 hover:bg-primary/90'
                                            : slot.disponible
                                              ? 'border border-border/80 bg-card font-medium text-foreground hover:border-primary/40 hover:bg-muted'
                                              : 'cursor-not-allowed border border-border/40 bg-muted/20 text-muted-foreground/40 line-through opacity-50'
                                    }`}
                                >
                                    <div className="flex items-center gap-1">
                                        {!esSeleccionado && IconoTurno && (
                                            <IconoTurno className="size-3 text-primary" />
                                        )}
                                        <span className="text-[11px] font-bold">
                                            {slot.hora}
                                        </span>
                                    </div>
                                    <span className="mt-0.5 text-[9px] opacity-80">
                                        {slot.disponible
                                            ? slot.turno
                                            : 'Completo'}
                                    </span>
                                </Button>
                            );
                        })}
                    </div>
                    {errors.hora && (
                        <FieldError className="text-[10px]">
                            {errors.hora.message}
                        </FieldError>
                    )}
                </div>
            </div>

            {/* Sección 3: Plano Visual Interactivo de Mesas */}
            <div
                className={`space-y-4 rounded-3xl border border-border/80 bg-card shadow-xs ${
                    esModal ? 'p-4' : 'p-6'
                }`}
            >
                <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <MapPin className="size-4 text-primary" />
                            <h4 className="text-sm font-black text-foreground">
                                Distribución de Mesas en Planta
                            </h4>
                        </div>
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            Haz clic sobre tu mesa preferida para seleccionarla
                            o cambiarla.
                        </p>
                    </div>

                    {mesaIndividualAsignada && (
                        <span className="inline-flex items-center gap-1.5 self-start rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-bold text-primary dark:text-rose-400">
                            Asignada: {mesaIndividualAsignada.nombre}
                        </span>
                    )}
                </div>

                <RestaurantePlanoMesasVisual
                    mesas={mesas}
                    mesaSeleccionadaId={
                        mesaSeleccionadaManual?.id ||
                        mesaIndividualAsignada?.id ||
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
        </div>
    );
};

export default RestaurantePasoMesaHorario;
