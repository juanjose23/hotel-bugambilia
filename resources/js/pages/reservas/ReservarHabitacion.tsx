import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    CreditCard,
    CheckCircle2,
    Lock,
    Loader2,
    AlertCircle,
    Hotel,
} from 'lucide-react';
import { ReservaConfirmadaModal } from '@/modules/reservas/components/ReservaConfirmadaModal';
import { ReservaPasoFechas } from '@/modules/reservas/components/ReservaPasoFechas';
import { ReservaPasoHuesped } from '@/modules/reservas/components/ReservaPasoHuesped';
import { ReservaPasoPago } from '@/modules/reservas/components/ReservaPasoPago';
import { ReservaPasoServicios } from '@/modules/reservas/components/ReservaPasoServicios';
import { ReservaResumenSidebar } from '@/modules/reservas/components/ReservaResumenSidebar';
import { ReservaStepperHeader } from '@/modules/reservas/components/ReservaStepperHeader';
import { StripePaymentForm } from '@/modules/reservas/components/StripePaymentForm';
import { useCrearReservaForm } from '@/modules/reservas/hooks/useCrearReservaForm';
import type {
    BeneficioClienteItem,
    ServicioAdicionalItem,
    PoliticaReserva,
} from '@/modules/reservas/types';
import { Button } from '@/modules/shared/components/ui/button';
import type { RoomItem } from '@/modules/shared/types';

interface ReservarHabitacionProps {
    room: RoomItem & {
        precio?: number | string;
        precio_desde?: number | string;
        moneda?: string;
        categoria_id?: number;
        politicas?: PoliticaReserva[];
        imagenes?: string[];
        imagen?: string;
        capacidad?: number;
        categoria?: string;
        slug?: string;
    };
    serviciosDisponibles?: ServicioAdicionalItem[];
    beneficiosCliente?: BeneficioClienteItem[];
    diasAgotados?: string[];
    initialCheckIn?: string;
    initialCheckOut?: string;
    initialHuespedes?: string | number;
}

export const ReservarHabitacion = ({
    room,
    serviciosDisponibles = [],
    beneficiosCliente = [],
    diasAgotados = [],
    initialCheckIn = '',
    initialCheckOut = '',
    initialHuespedes = 2,
}: ReservarHabitacionProps) => {
    const {
        register,
        handleSubmit,
        setValue,
        errors,
        isSubmitting,
        pasoActual,
        irAlPaso,
        checkIn,
        checkOut,
        adultos,
        ninos,
        canalPago,
        tipoPago,
        noches,
        precioNoche,
        subtotalHabitacion,
        subtotalServicios,
        montoDescuento,
        totalNeto,
        montoACobrarAhora,
        serviciosSeleccionados,
        toggleServicio,
        cambiarCantidadServicio,
        stripeData,
        reservaConfirmada,
        porcentajeAnticipoPolitica,
        cancelarStripe,
        errorServidor,
        handleStripeSuccess,
        esCorporativo,
        tieneBeneficioAnticipoReducido,
        tieneConflictoFechas,
        diasAgotados: diasAgotadosActualizados,
    } = useCrearReservaForm({
        room,
        serviciosDisponibles,
        beneficiosCliente,
        diasAgotados,
        initialCheckIn,
        initialCheckOut,
        initialHuespedes,
    });

    const moneda = room.moneda || '$';
    const imagenPrincipal =
        room.imagenes?.[0] || room.imagen || '/images/main-room.webp';

    return (
        <div className="min-h-screen bg-background font-sans text-foreground">
            <Head>
                <title>{`Reservar ${room.nombre} — Hotel Bugambilias`}</title>
                <meta
                    name="description"
                    content={`Proceso de reserva seguro para la suite ${room.nombre} en Hotel Bugambilias Estelí.`}
                />
            </Head>

            {/* Contenido Principal */}
            <div className="container mx-auto px-4 py-6 pb-28 sm:px-6 lg:max-w-6xl lg:pb-12">
                {/* Subheader con retorno y suite badge */}
                <div className="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-border/60 pb-4">
                    <Link
                        href={`/habitaciones/${room.slug || room.id}`}
                        className="inline-flex items-center gap-2 text-xs font-bold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft className="size-4" />
                        <span>Volver al detalle de la suite</span>
                    </Link>

                    <div className="flex items-center gap-3">
                        <div className="hidden items-center gap-1.5 text-xs font-medium text-muted-foreground sm:flex">
                            <Lock className="size-3.5 text-emerald-600 dark:text-emerald-400" />
                            <span>Reserva Segura SSL 256-bit</span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-black text-primary dark:bg-rose-950/50 dark:text-rose-300">
                            <Hotel className="size-3.5" />
                            <span>{room.nombre}</span>
                        </div>
                    </div>
                </div>

                {/* Stepper Superior */}
                <ReservaStepperHeader
                    pasoActual={pasoActual}
                    onCambiarPaso={irAlPaso}
                />

                {/* Layout Principal: 2 Columnas */}
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    {/* Columna Izquierda: Formulario y Pasos o Pasarela Stripe Integrada */}
                    <div className="space-y-6 lg:col-span-7 xl:col-span-8">
                        {errorServidor && (
                            <div className="flex items-center gap-2.5 rounded-2xl border border-destructive/30 bg-destructive/10 p-4 text-xs font-bold text-destructive">
                                <AlertCircle className="size-5 shrink-0" />
                                <span>{errorServidor}</span>
                            </div>
                        )}

                        {stripeData ? (
                            <div className="rounded-3xl border border-border bg-card p-6 shadow-sm">
                                <div className="mb-4">
                                    <h2 className="text-lg font-black tracking-tight text-foreground sm:text-xl">
                                        Paso 4: Completar Pago Seguro
                                    </h2>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        Ingresa los datos de tu tarjeta para
                                        procesar el cobro y asegurar tu estancia
                                        al instante.
                                    </p>
                                </div>

                                <StripePaymentForm
                                    stripeData={stripeData}
                                    onSuccess={handleStripeSuccess}
                                    onError={() => {}}
                                    onCancel={cancelarStripe}
                                />
                            </div>
                        ) : (
                            <form onSubmit={handleSubmit} className="space-y-6">
                                {pasoActual === 1 && (
                                    <ReservaPasoFechas
                                        checkIn={checkIn}
                                        checkOut={checkOut}
                                        diasAgotados={diasAgotadosActualizados}
                                        capacidadMaxima={room.capacidad || 4}
                                        register={register}
                                        setValue={setValue}
                                        errors={errors}
                                    />
                                )}

                                {pasoActual === 2 && (
                                    <ReservaPasoHuesped
                                        beneficiosCliente={beneficiosCliente}
                                        register={register}
                                        errors={errors}
                                    />
                                )}

                                {pasoActual === 3 && (
                                    <ReservaPasoServicios
                                        serviciosDisponibles={
                                            serviciosDisponibles
                                        }
                                        serviciosSeleccionados={
                                            serviciosSeleccionados
                                        }
                                        toggleServicio={toggleServicio}
                                        cambiarCantidadServicio={
                                            cambiarCantidadServicio
                                        }
                                    />
                                )}

                                {pasoActual === 4 && (
                                    <ReservaPasoPago
                                        politicas={room.politicas}
                                        canalPago={canalPago}
                                        tipoPago={tipoPago}
                                        totalNeto={totalNeto}
                                        porcentajeAnticipoPolitica={
                                            porcentajeAnticipoPolitica
                                        }
                                        moneda={moneda}
                                        esCorporativo={esCorporativo}
                                        tieneBeneficioAnticipoReducido={
                                            tieneBeneficioAnticipoReducido
                                        }
                                        setValue={setValue}
                                    />
                                )}

                                {/* Botones de Navegación del Stepper */}
                                <div className="flex items-center justify-between gap-3 border-t border-border pt-6">
                                    {pasoActual > 1 ? (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() =>
                                                irAlPaso(
                                                    (pasoActual - 1) as
                                                        1 | 2 | 3 | 4,
                                                )
                                            }
                                            className="h-11 rounded-2xl px-5 text-xs font-bold"
                                        >
                                            <ArrowLeft className="mr-2 size-4" />
                                            <span>Paso Anterior</span>
                                        </Button>
                                    ) : (
                                        <div />
                                    )}

                                    {pasoActual < 4 ? (
                                        <Button
                                            type="button"
                                            disabled={
                                                pasoActual === 1 &&
                                                tieneConflictoFechas
                                            }
                                            onClick={() =>
                                                irAlPaso(
                                                    (pasoActual + 1) as
                                                        1 | 2 | 3 | 4,
                                                )
                                            }
                                            className="h-11 rounded-2xl bg-primary px-8 text-xs font-black text-primary-foreground shadow-lg hover:bg-primary/90 disabled:opacity-50"
                                        >
                                            <span>Continuar</span>
                                            <ArrowRight className="ml-2 size-4" />
                                        </Button>
                                    ) : (
                                        <Button
                                            type="submit"
                                            disabled={isSubmitting}
                                            className="h-12 rounded-2xl bg-primary px-8 text-xs font-black text-primary-foreground shadow-xl hover:bg-primary/90"
                                        >
                                            {isSubmitting ? (
                                                <>
                                                    <Loader2 className="mr-2 size-4 animate-spin" />
                                                    <span>
                                                        Procesando reserva...
                                                    </span>
                                                </>
                                            ) : canalPago === 'stripe' ? (
                                                <>
                                                    <CreditCard className="mr-2 size-4" />
                                                    <span>
                                                        Proceder al Pago (
                                                        {moneda}
                                                        {montoACobrarAhora.toFixed(
                                                            2,
                                                        )}
                                                        )
                                                    </span>
                                                </>
                                            ) : (
                                                <>
                                                    <CheckCircle2 className="mr-2 size-4" />
                                                    <span>
                                                        Confirmar Reserva
                                                    </span>
                                                </>
                                            )}
                                        </Button>
                                    )}
                                </div>
                            </form>
                        )}
                    </div>

                    {/* Columna Derecha: Sticky Summary Card */}
                    <div className="lg:col-span-5 xl:col-span-4">
                        <ReservaResumenSidebar
                            room={room}
                            imagenPrincipal={imagenPrincipal}
                            checkIn={checkIn}
                            checkOut={checkOut}
                            noches={noches}
                            adultos={adultos}
                            ninos={ninos}
                            precioNoche={precioNoche}
                            subtotalHabitacion={subtotalHabitacion}
                            subtotalServicios={subtotalServicios}
                            montoDescuento={montoDescuento}
                            totalNeto={totalNeto}
                            montoACobrarAhora={montoACobrarAhora}
                            moneda={moneda}
                        />
                    </div>
                </div>
            </div>

            {/* Modal de Reserva Confirmada Exitosamente (SOLO tras pago completado o reserva sin pago) */}
            {reservaConfirmada && !stripeData && (
                <div className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/70 p-4 backdrop-blur-sm">
                    <div className="my-auto max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl border border-border bg-card p-6 shadow-2xl">
                        <ReservaConfirmadaModal
                            reserva={reservaConfirmada}
                            onClose={() => {}}
                        />
                    </div>
                </div>
            )}
        </div>
    );
};

export default ReservarHabitacion;
