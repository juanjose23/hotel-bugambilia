import { Calendar, MapPin } from 'lucide-react';
import { useState } from 'react';
import type { FieldErrors, UseFormRegister } from 'react-hook-form';
import type { FormReservaMesa } from '../../schemas/reservaMesaSchema';
import type { DisponibilidadMesasResponse } from '../../services/restauranteService';
import type { MesaData } from '../../types';
import { RestaurantePlanoMesasVisual } from '../RestaurantePlanoMesasVisual';
import { RestauranteSelectorFechaComensales } from './RestauranteSelectorFechaComensales';
import { RestauranteSelectorHorariosTurnos } from './RestauranteSelectorHorariosTurnos';
import type { TurnoFiltro } from './RestauranteSelectorHorariosTurnos';

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
}: PasoMesaHorarioProps) => {
    const [filtroTurno, setFiltroTurno] = useState<TurnoFiltro>('todos');
    const [mesaSeleccionadaManual, setMesaSeleccionadaManual] =
        useState<MesaData | null>(null);

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
            <div className="space-y-6 rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
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

                <RestauranteSelectorFechaComensales
                    register={register}
                    errors={errors}
                    adultosSeleccionados={adultosSeleccionados}
                    alCambiarFecha={cambiarFecha}
                    alAjustarComensales={ajustarComensales}
                    alSeleccionarComensales={seleccionarComensales}
                    variante="pagina"
                />

                <RestauranteSelectorHorariosTurnos
                    horarios={horariosFiltrados}
                    horaSeleccionada={horaSeleccionada}
                    cargando={cargandoDisponibilidad}
                    errors={errors}
                    filtroTurno={filtroTurno}
                    alCambiarTurno={setFiltroTurno}
                    alSeleccionarHora={seleccionarHora}
                    variante="pagina"
                />
            </div>

            <div className="space-y-4 rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
                <div className="flex flex-col justify-between gap-2 border-b border-border/60 pb-3 sm:flex-row sm:items-center">
                    <div className="flex items-center gap-2.5">
                        <div className="flex size-9 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary dark:text-rose-400">
                            <MapPin className="size-4" />
                        </div>
                        <div>
                            <h3 className="text-base font-black text-foreground">
                                Plano Interactivo de Mesas
                            </h3>
                            <p className="text-xs text-muted-foreground">
                                Haz clic en la mesa que prefieras para
                                seleccionarla.
                            </p>
                        </div>
                    </div>

                    {(mesaSeleccionadaManual || mesaIndividualAsignada) && (
                        <span className="self-start rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-black text-emerald-600 sm:self-auto dark:text-emerald-400">
                            Mesa:{' '}
                            {mesaSeleccionadaManual?.nombre ??
                                mesaIndividualAsignada?.nombre}{' '}
                            (
                            {mesaSeleccionadaManual?.capacidad ??
                                mesaIndividualAsignada?.capacidad}{' '}
                            personas)
                        </span>
                    )}
                </div>

                <RestaurantePlanoMesasVisual
                    mesas={mesas}
                    mesaSeleccionadaId={
                        mesaSeleccionadaManual?.id ??
                        espacioIdSeleccionado ??
                        mesaIndividualAsignada?.id
                    }
                    mesasUnidasIds={
                        disponibilidad?.mesas_sugeridas_union?.map(
                            (m) => m.id,
                        ) ?? []
                    }
                    alSeleccionarMesa={manejarSeleccionMesa}
                />
            </div>
        </div>
    );
};

export default RestaurantePasoMesaHorario;
