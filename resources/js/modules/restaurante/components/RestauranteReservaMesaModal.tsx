import { AlertCircle, ArrowRight, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import { Sheet, SheetContent } from '@/modules/shared/components/ui/sheet';
import { useReservaMesaForm } from '../hooks/useReservaMesaForm';
import type { RestauranteData, MesaData } from '../types';
import { RestauranteCamposContactoReserva } from './reserva/RestauranteCamposContactoReserva';
import { RestauranteReservaExitosa } from './reserva/RestauranteReservaExitosa';
import { RestauranteSelectorFechaComensales } from './reserva/RestauranteSelectorFechaComensales';
import { RestauranteSelectorHorariosTurnos } from './reserva/RestauranteSelectorHorariosTurnos';
import type { TurnoFiltro } from './reserva/RestauranteSelectorHorariosTurnos';
import { RestauranteAlertaMesaAsignada } from './RestauranteAlertaMesaAsignada';
import { RestauranteHeaderReservaSheet } from './RestauranteHeaderReservaSheet';
import { RestaurantePlanoMesasVisual } from './RestaurantePlanoMesasVisual';

interface PropsRestauranteReservaMesaModal {
    abierto: boolean;
    alCambiarAbierto: (abierto: boolean) => void;
    restaurante: RestauranteData;
    mesas?: MesaData[];
}

export const RestauranteReservaMesaModal = ({
    abierto,
    alCambiarAbierto,
    restaurante,
    mesas = [],
}: PropsRestauranteReservaMesaModal) => {
    const {
        register,
        onSubmit,
        errors,
        isSubmitting,
        disponibilidad,
        cargandoDisponibilidad,
        reservaCreada,
        errorEnvio,
        reiniciar,
        horaSeleccionada,
        adultosSeleccionados,
        espacioIdSeleccionado,
        cambiarFecha,
        seleccionarHora,
        ajustarComensales,
        seleccionarComensales,
        seleccionarMesaEnPlano,
        agregarNotaRapida,
    } = useReservaMesaForm({
        alCerrar: () => alCambiarAbierto(false),
    });

    const [filtroTurno, setFiltroTurno] = useState<TurnoFiltro>('todos');
    const [mostrarPlano, setMostrarPlano] = useState(false);
    const [mesaSeleccionadaManual, setMesaSeleccionadaManual] =
        useState<MesaData | null>(null);

    const horariosFiltrados =
        disponibilidad?.horarios_disponibles.filter((slot) => {
            if (filtroTurno === 'todos') {
                return true;
            }

            return slot.turno === filtroTurno;
        }) ?? [];

    const manejarSeleccionMesaEnPlano = (mesa: MesaData) => {
        setMesaSeleccionadaManual(mesa);
        seleccionarMesaEnPlano(mesa);
    };

    const mesaIndividualAsignada = !disponibilidad?.requiere_union
        ? disponibilidad?.mesa_asignada
        : null;

    return (
        <Sheet open={abierto} onOpenChange={alCambiarAbierto}>
            <SheetContent
                side="right"
                className="flex w-full flex-col overflow-y-auto bg-card p-6 font-sans text-foreground sm:max-w-xl"
            >
                <RestauranteHeaderReservaSheet
                    nombreRestaurante={restaurante.nombre}
                />

                {reservaCreada ? (
                    <div className="flex grow flex-col justify-center">
                        <RestauranteReservaExitosa
                            reservaCreada={reservaCreada}
                            nombreRestaurante={restaurante.nombre}
                            variante="modal"
                            alReiniciar={reiniciar}
                        />
                    </div>
                ) : (
                    <form onSubmit={onSubmit} className="mt-4 space-y-5">
                        <RestauranteSelectorFechaComensales
                            register={register}
                            errors={errors}
                            adultosSeleccionados={adultosSeleccionados}
                            alCambiarFecha={cambiarFecha}
                            alAjustarComensales={ajustarComensales}
                            alSeleccionarComensales={seleccionarComensales}
                            variante="modal"
                        />

                        {cargandoDisponibilidad ? (
                            <div className="flex animate-pulse items-center gap-2 rounded-2xl border border-border/60 bg-muted/20 p-3.5 text-xs text-muted-foreground">
                                <Loader2 className="size-4 shrink-0 animate-spin text-primary" />
                                <span>
                                    Verificando disponibilidad de mesas en
                                    restaurante...
                                </span>
                            </div>
                        ) : (
                            <RestauranteAlertaMesaAsignada
                                disponibilidad={disponibilidad}
                                adultosSeleccionados={adultosSeleccionados}
                                mostrarPlano={mostrarPlano}
                                alAlternarPlano={() =>
                                    setMostrarPlano(!mostrarPlano)
                                }
                            />
                        )}

                        {mostrarPlano && (
                            <div className="animate-in fade-in zoom-in-95 rounded-3xl border border-border/80 bg-muted/20 p-4 duration-200">
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
                                    esModoModal={true}
                                    alSeleccionarMesa={
                                        manejarSeleccionMesaEnPlano
                                    }
                                />
                            </div>
                        )}

                        <RestauranteSelectorHorariosTurnos
                            horarios={horariosFiltrados}
                            horaSeleccionada={horaSeleccionada}
                            cargando={cargandoDisponibilidad}
                            errors={errors}
                            filtroTurno={filtroTurno}
                            alCambiarTurno={setFiltroTurno}
                            alSeleccionarHora={seleccionarHora}
                            variante="modal"
                        />

                        <RestauranteCamposContactoReserva
                            register={register}
                            errors={errors}
                            alAgregarNotaRapida={agregarNotaRapida}
                            variante="modal"
                        />

                        {errorEnvio && (
                            <div className="animate-in fade-in flex items-start gap-2 rounded-2xl border border-destructive/30 bg-destructive/10 p-3 text-xs text-destructive duration-300">
                                <AlertCircle className="mt-0.5 size-4 shrink-0" />
                                <span>{errorEnvio}</span>
                            </div>
                        )}

                        <Button
                            type="submit"
                            disabled={isSubmitting}
                            className="mt-4 h-11 w-full cursor-pointer rounded-2xl bg-primary text-xs font-black text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:bg-primary/90 active:scale-95"
                        >
                            {isSubmitting ? (
                                <Loader2 className="size-4 animate-spin" />
                            ) : (
                                <>
                                    <span>Confirmar Reservación de Mesa</span>
                                    <ArrowRight className="ml-1.5 size-4" />
                                </>
                            )}
                        </Button>
                    </form>
                )}
            </SheetContent>
        </Sheet>
    );
};

export default RestauranteReservaMesaModal;
