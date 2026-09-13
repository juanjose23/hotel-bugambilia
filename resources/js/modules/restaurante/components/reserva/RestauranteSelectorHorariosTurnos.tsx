import { Loader2 } from 'lucide-react';
import type { FieldErrors } from 'react-hook-form';
import { FieldError, FieldLabel } from '@/modules/shared/components/ui/field';
import { iconoTurnoRapido, TURNOS_RESTAURANTE } from '../../constants';
import type { FormReservaMesa } from '../../schemas/reservaMesaSchema';

export type TurnoFiltro = 'todos' | 'Desayuno' | 'Almuerzo' | 'Cena';

export interface HorarioSlot {
    hora: string;
    turno: string;
    disponible: boolean;
}

interface RestauranteSelectorHorariosTurnosProps {
    horarios: HorarioSlot[];
    horaSeleccionada: string;
    cargando: boolean;
    errors: FieldErrors<FormReservaMesa>;
    filtroTurno: TurnoFiltro;
    alCambiarTurno: (turno: TurnoFiltro) => void;
    alSeleccionarHora: (hora: string) => void;
    variante?: 'pagina' | 'modal';
}

export const RestauranteSelectorHorariosTurnos = ({
    horarios,
    horaSeleccionada,
    cargando,
    errors,
    filtroTurno,
    alCambiarTurno,
    alSeleccionarHora,
    variante = 'pagina',
}: RestauranteSelectorHorariosTurnosProps) => {
    const esModal = variante === 'modal';

    return (
        <div
            className={
                esModal
                    ? 'space-y-2'
                    : 'space-y-3 border-t border-border/40 pt-2'
            }
        >
            <div className="flex items-center justify-between">
                <FieldLabel
                    className={
                        esModal ? 'text-[11px] font-bold' : 'text-xs font-bold'
                    }
                >
                    {esModal
                        ? 'Turno & Horario Disponible *'
                        : 'Selecciona el Horario *'}
                </FieldLabel>
                {cargando && (
                    <span className="inline-flex items-center gap-1.5 text-[11px] text-muted-foreground">
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
                        <button
                            key={turno.id}
                            type="button"
                            onClick={() => alCambiarTurno(turno.id)}
                            className={`flex flex-1 cursor-pointer items-center justify-center gap-1 rounded-lg py-1.5 text-[11px] font-bold transition-all ${
                                activo
                                    ? 'bg-background font-black text-foreground shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Icon className="size-3" />
                            <span className={esModal ? '' : 'hidden sm:inline'}>
                                {turno.label}
                            </span>
                        </button>
                    );
                })}
            </div>

            <div className="grid grid-cols-3 gap-1.5 sm:grid-cols-4 md:grid-cols-6">
                {horarios.map((slot) => {
                    const esSeleccionado = horaSeleccionada === slot.hora;
                    const IconoTurno = iconoTurnoRapido(slot.turno);

                    return (
                        <button
                            key={slot.hora}
                            type="button"
                            disabled={!slot.disponible}
                            onClick={() => alSeleccionarHora(slot.hora)}
                            className={`flex cursor-pointer flex-col items-center justify-center rounded-xl p-2 text-xs transition-all ${
                                esSeleccionado
                                    ? 'border border-primary bg-primary font-black text-primary-foreground shadow-md ring-2 ring-primary/20'
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
                                {slot.disponible ? slot.turno : 'Completo'}
                            </span>
                        </button>
                    );
                })}
            </div>
            {errors.hora && (
                <FieldError className="text-[10px]">
                    {errors.hora.message}
                </FieldError>
            )}
        </div>
    );
};

export default RestauranteSelectorHorariosTurnos;
