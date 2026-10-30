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
    Calendar,
    Users,
    Clock,
} from 'lucide-react';
import { ReservaConfirmada } from '@/modules/reservas/components/ReservaConfirmada';
import { ReservaPasoCliente } from '@/modules/reservas/components/ReservaPasoCliente';
import { ReservaPasoFechas } from '@/modules/reservas/components/ReservaPasoFechas';
import { ReservaPasoPago } from '@/modules/reservas/components/ReservaPasoPago';
import { ReservaPasoServicios } from '@/modules/reservas/components/ReservaPasoServicios';
import { StripePaymentForm } from '@/modules/reservas/components/StripePaymentForm';
import { useCrearReservaForm } from '@/modules/reservas/hooks/useCrearReservaForm';
import type {
    BeneficioClienteItem,
    ServicioAdicionalItem,
    PoliticaReserva,
} from '@/modules/reservas/types';
import {
    BookingSummary,
    CompactBookingProgress,
} from '@/modules/shared/components/booking';
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

const PASOS_HABITACION = [
    { num: 1, title: 'Estancia', subtitle: 'Fechas y huéspedes' },
    { num: 2, title: 'Huésped', subtitle: 'Datos de contacto' },
    { num: 3, title: 'Servicios', subtitle: 'Extras opcionales' },
    { num: 4, title: 'Garantía & Pago', subtitle: 'Confirmación' },
];

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

    const getBotonContinuarTexto = () => {
        switch (pasoActual) {
            case 1:
                return 'Continuar a datos del huésped';
            case 2:
                return 'Continuar a servicios y extras';
            case 3:
                return 'Continuar al pago seguro';
            default:
                return 'Continuar';
        }
    };

    const summaryRows = [
        {
            label: 'Check-in',
            value: checkIn || 'Sin seleccionar',
            icon: <Calendar className="size-3.5 text-primary" />,
        },
        {
            label: 'Check-out',
            value: checkOut || 'Sin seleccionar',
            icon: <Calendar className="size-3.5 text-primary" />,
        },
        {
            label: 'Duración',
            value: `${noches} ${noches === 1 ? 'noche' : 'noches'}`,
            icon: <Clock className="size-3.5 text-muted-foreground" />,
        },
        {
            label: 'Huéspedes',
            value: `${adultos} adultos${ninos > 0 ? `, ${ninos} niños` : ''}`,
            icon: <Users className="size-3.5 text-muted-foreground" />,
        },
    ];

    const pricingData = {
        subtotal: subtotalHabitacion,
        subtotalLabel: `Habitación (${moneda}${precioNoche} × ${noches} ${noches === 1 ? 'noche' : 'noches'})`,
        servicesTotal: subtotalServicios,
        servicesLabel: 'Servicios adicionales',
        discount: montoDescuento,
        discountLabel: 'Beneficio exclusivo aplicado',
        total: totalNeto,
        dueToday: montoACobrarAhora,
        dueTodayLabel: 'Monto a pagar hoy:',
        currency: moneda,
    };

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

                {/* Stepper Superior Compacto */}
                <div className="mb-8">
                    <CompactBookingProgress
                        steps={PASOS_HABITACION}
                        currentStep={pasoActual}
                        onStepClick={(p) => irAlPaso(p as 1 | 2 | 3 | 4)}
                    />
                </div>

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
                                    <ReservaPasoCliente
                                        beneficiosCliente={beneficiosCliente}
                                        register={register}
                                        errors={errors}
                                        titulo="Información del Huésped Principal"
                                        subtitulo="Enviaremos los detalles de confirmación y factura electrónica a estos contactos."
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
                                                (tieneConflictoFechas ||
                                                    !checkIn ||
                                                    !checkOut)
                                            }
                                            onClick={() =>
                                                irAlPaso(
                                                    (pasoActual + 1) as
                                                        1 | 2 | 3 | 4,
                                                )
                                            }
                                            className="h-11 rounded-2xl bg-primary px-8 text-xs font-black text-primary-foreground shadow-lg hover:bg-primary/90 disabled:opacity-50"
                                        >
                                            <span>
                                                {getBotonContinuarTexto()}
                                            </span>
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
                                                        Proceder al Pago Seguro
                                                        ({moneda}
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
                                                        Confirmar Reserva de
                                                        Suite
                                                    </span>
                                                </>
                                            )}
                                        </Button>
                                    )}
                                </div>
                            </form>
                        )}
                    </div>

                    {/* Columna Derecha: Sticky BookingSummary & Mobile Bottom Drawer */}
                    <div className="lg:col-span-5 xl:col-span-4">
                        <BookingSummary
                            title="Tu Estancia"
                            resourceName={room.nombre}
                            resourceCategory={
                                room.categoria || 'Suite Boutique'
                            }
                            resourceImage={imagenPrincipal}
                            rows={summaryRows}
                            pricing={pricingData}
                        />
                    </div>
                </div>
            </div>

            {/* Modal de Reserva Confirmada Exitosamente con "Añadir a mi estancia" */}
            {reservaConfirmada && !stripeData && (
                <div className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-md">
                    <div className="my-auto max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-border bg-card p-6 shadow-2xl">
                        <ReservaConfirmada
                            codigoReserva={reservaConfirmada.codigo_reserva}
                            reservaId={reservaConfirmada.id}
                            titulo="¡Te esperamos en Hotel Bugambilias!"
                            subtitulo="Hemos registrado tu estancia. Guarda tu código de confirmación o descarga tu voucher para el check-in."
                            checkInDate={
                                reservaConfirmada.fecha_check_in || checkIn
                            }
                            checkOutDate={
                                reservaConfirmada.fecha_check_out || checkOut
                            }
                            guestCount={adultos}
                            detalles={[
                                ...(reservaConfirmada.habitacion_nombre
                                    ? [
                                          {
                                              label: 'Suite / Habitación',
                                              valor: reservaConfirmada.habitacion_nombre,
                                          },
                                      ]
                                    : []),
                                ...(reservaConfirmada.fecha_check_in &&
                                reservaConfirmada.fecha_check_out
                                    ? [
                                          {
                                              label: 'Fechas de Estancia',
                                              valor: `${reservaConfirmada.fecha_check_in} — ${reservaConfirmada.fecha_check_out}`,
                                          },
                                      ]
                                    : []),
                                {
                                    label: 'Total de la Reserva',
                                    valor: `${moneda}${totalNeto.toFixed(2)}`,
                                },
                            ]}
                            urlRetorno="/habitaciones"
                            textoRetorno="Explorar más Suites"
                            variante="modal"
                            alCerrar={() => {}}
                        />
                    </div>
                </div>
            )}
        </div>
    );
};

export default ReservarHabitacion;
