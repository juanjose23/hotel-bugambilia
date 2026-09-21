import {
    AlertCircle,
    ArrowRight,
    Loader2,
    UtensilsCrossed,
} from 'lucide-react';
import { ReservaConfirmada } from '@/modules/reservas/components/ReservaConfirmada';
import { ReservaPasoCliente } from '@/modules/reservas/components/ReservaPasoCliente';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/modules/shared/components/ui/sheet';
import { OCASIONES_RAPIDAS } from '../constants';
import { useReservaMesaForm } from '../hooks/useReservaMesaForm';
import type { RestauranteData, MesaData } from '../types';
import { RestaurantePasoMesaHorario } from './reserva/RestaurantePasoMesaHorario';

export interface PropsRestauranteReservaMesaModal {
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

    return (
        <Sheet open={abierto} onOpenChange={alCambiarAbierto}>
            <SheetContent
                side="right"
                className="flex w-full flex-col overflow-y-auto bg-card p-6 font-sans text-foreground sm:max-w-xl"
            >
                <SheetHeader className="border-b border-border/80 pb-4">
                    <div className="flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary shadow-xs dark:text-rose-400">
                            <UtensilsCrossed className="size-5" />
                        </div>
                        <div>
                            <SheetTitle className="text-base font-black tracking-tight text-foreground">
                                Reservar Mesa en {restaurante.nombre}
                            </SheetTitle>
                            <SheetDescription className="mt-0.5 text-xs text-muted-foreground">
                                Reserva instantánea por turno horario con
                                asignación inteligente de mesas.
                            </SheetDescription>
                        </div>
                    </div>
                </SheetHeader>

                {reservaCreada ? (
                    <div className="flex grow flex-col justify-center">
                        <ReservaConfirmada
                            codigoReserva={reservaCreada.codigo_reserva}
                            reservaId={reservaCreada.reserva_id}
                            titulo={`¡Mesa Reservada en ${restaurante.nombre}!`}
                            subtitulo="Tu mesa ha sido reservada con éxito. Te esperamos puntualmente."
                            detalles={[
                                { label: 'Fecha', valor: reservaCreada.fecha },
                                { label: 'Horario', valor: reservaCreada.hora },
                                {
                                    label: 'Mesa(s)',
                                    valor: reservaCreada.mesas_unidas.join(
                                        ' + ',
                                    ),
                                },
                            ]}
                            whatsappUrl={reservaCreada.whatsapp_url}
                            urlRetorno="/restaurante"
                            textoRetorno="Cerrar y volver al menú"
                            variante="modal"
                            alCerrar={reiniciar}
                        />
                    </div>
                ) : (
                    <form onSubmit={onSubmit} className="mt-4 space-y-5">
                        <RestaurantePasoMesaHorario
                            register={register}
                            errors={errors}
                            disponibilidad={disponibilidad}
                            cargandoDisponibilidad={cargandoDisponibilidad}
                            horaSeleccionada={horaSeleccionada}
                            adultosSeleccionados={adultosSeleccionados}
                            espacioIdSeleccionado={espacioIdSeleccionado}
                            mesas={mesas}
                            cambiarFecha={cambiarFecha}
                            seleccionarHora={seleccionarHora}
                            ajustarComensales={ajustarComensales}
                            seleccionarComensales={seleccionarComensales}
                            seleccionarMesaEnPlano={seleccionarMesaEnPlano}
                            variante="modal"
                        />

                        <ReservaPasoCliente
                            register={register}
                            errors={errors}
                            ocasionesRapidas={OCASIONES_RAPIDAS}
                            alAgregarNotaRapida={agregarNotaRapida}
                            titulo="Datos de Contacto"
                            subtitulo="Para confirmar tu mesa y notificarte inmediatamente."
                            placeholderNotas="Petición especial u ocasión (cumpleaños, terraza, etc.)..."
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
