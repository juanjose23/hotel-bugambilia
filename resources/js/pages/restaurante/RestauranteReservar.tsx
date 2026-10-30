import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    CheckCircle2,
    Loader2,
    Calendar,
    Clock,
    Users,
    UtensilsCrossed,
    ArrowRight,
} from 'lucide-react';
import { ReservaConfirmada } from '@/modules/reservas/components/ReservaConfirmada';
import { ReservaPasoCliente } from '@/modules/reservas/components/ReservaPasoCliente';
import { RestaurantePasoMesaHorario } from '@/modules/restaurante/components/reserva/RestaurantePasoMesaHorario';
import { RestaurantePasoPreordenMenu } from '@/modules/restaurante/components/reserva/RestaurantePasoPreordenMenu';
import {
    OCASIONES_RAPIDAS,
    crearRestaurantePorDefecto,
} from '@/modules/restaurante/constants';
import { useReservaFlujoReserva } from '@/modules/restaurante/hooks/useReservaFlujoReserva';
import { useReservaMesaForm } from '@/modules/restaurante/hooks/useReservaMesaForm';
import type { RestaurantePageProps } from '@/modules/restaurante/types';
import {
    BookingSummary,
    CompactBookingProgress,
} from '@/modules/shared/components/booking';
import { Button } from '@/modules/shared/components/ui/button';

const PASOS_RESTAURANTE = [
    { num: 1, title: 'Horario & Mesa', subtitle: 'Fecha y personas' },
    { num: 2, title: 'Pre-ordenar Menú', subtitle: 'Opcional' },
    { num: 3, title: 'Datos & Confirmación', subtitle: 'Contacto del titular' },
];

export const RestauranteReservar = ({
    restaurante,
    mesas = [],
    menu = [],
}: RestaurantePageProps) => {
    const datosRestaurante = restaurante || crearRestaurantePorDefecto();

    const {
        register,
        onSubmit,
        errors,
        isSubmitting,
        disponibilidad,
        cargandoDisponibilidad,
        reservaCreada,
        errorEnvio,
        fechaSeleccionada,
        horaSeleccionada,
        adultosSeleccionados,
        espacioIdSeleccionado,
        cambiarFecha,
        seleccionarHora,
        ajustarComensales,
        seleccionarComensales,
        seleccionarMesaEnPlano,
        agregarNotaRapida,
    } = useReservaMesaForm();

    const {
        pasoActual,
        irPaso,
        platosPreorden,
        agregarPlatoPreorden,
        removerPlatoPreorden,
        categoriaMenuFiltro,
        alCambiarCategoria,
        categoriasMenu,
        menuFiltrado,
        subtotalPreorden,
    } = useReservaFlujoReserva(menu);

    const mesaAsignada = disponibilidad?.mesa_asignada?.nombre
        ? disponibilidad.mesa_asignada.nombre
        : disponibilidad?.requiere_union
          ? 'Mesa unida para grupo'
          : 'Mesa asignada automáticamente';

    const summaryRows = [
        {
            label: 'Fecha',
            value: fechaSeleccionada || 'Sin seleccionar',
            icon: <Calendar className="size-3.5 text-primary" />,
        },
        {
            label: 'Horario',
            value: horaSeleccionada || 'Sin seleccionar',
            icon: <Clock className="size-3.5 text-primary" />,
        },
        {
            label: 'Comensales',
            value: `${adultosSeleccionados} ${adultosSeleccionados === 1 ? 'persona' : 'personas'}`,
            icon: <Users className="size-3.5 text-muted-foreground" />,
        },
        {
            label: 'Mesa asignada',
            value: mesaAsignada,
            icon: <UtensilsCrossed className="size-3.5 text-emerald-500" />,
        },
    ];

    const pricingData = {
        subtotal: subtotalPreorden,
        subtotalLabel: 'Reserva de mesa',
        servicesTotal: subtotalPreorden > 0 ? subtotalPreorden : 0,
        servicesLabel: 'Pre-orden de menú',
        total: subtotalPreorden,
        currency: 'C$',
    };

    return (
        <div className="min-h-screen bg-background font-sans text-foreground">
            <Head>
                <title>{`Reservación de Mesa — ${datosRestaurante.nombre}`}</title>
                <meta
                    name="description"
                    content={`Reserva tu mesa en ${datosRestaurante.nombre}. Asignación inmediata, ambiente exclusivo y cocina de autor.`}
                />
            </Head>

            <div className="container mx-auto px-4 py-6 pb-28 sm:px-6 lg:max-w-6xl lg:pb-12">
                {/* Cabecera retorno */}
                <div className="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-border/60 pb-4">
                    <Link
                        href="/restaurante"
                        className="inline-flex items-center gap-2 text-xs font-bold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft className="size-4" />
                        <span>Volver al Restaurante</span>
                    </Link>

                    <span className="rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-black text-primary dark:text-rose-400">
                        Reservación Gastronómica Inmediata
                    </span>
                </div>

                {/* Stepper Compacto */}
                <div className="mb-8">
                    <CompactBookingProgress
                        steps={PASOS_RESTAURANTE}
                        currentStep={pasoActual}
                        onStepClick={(p) => irPaso(p as 1 | 2 | 3)}
                    />
                </div>

                {reservaCreada ? (
                    <ReservaConfirmada
                        codigoReserva={reservaCreada.codigo_reserva}
                        reservaId={reservaCreada.reserva_id}
                        titulo={`¡Mesa Reservada en ${datosRestaurante.nombre}!`}
                        subtitulo={`Hemos asegurado tu reservación en ${datosRestaurante.nombre}. Te esperamos puntualmente.`}
                        checkInDate={reservaCreada.fecha}
                        guestCount={adultosSeleccionados}
                        detalles={[
                            { label: 'Fecha', valor: reservaCreada.fecha },
                            { label: 'Horario', valor: reservaCreada.hora },
                            {
                                label: 'Mesa(s) Asignada(s)',
                                valor: reservaCreada.mesas_unidas.join(' + '),
                            },
                        ]}
                        whatsappUrl={reservaCreada.whatsapp_url}
                        urlRetorno="/restaurante"
                        textoRetorno="Volver al Menú"
                        variante="pagina"
                    />
                ) : (
                    <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                        {/* Columna Izquierda: Pasos de Reserva */}
                        <div className="space-y-6 lg:col-span-8">
                            {pasoActual === 1 && (
                                <div className="space-y-6">
                                    <RestaurantePasoMesaHorario
                                        register={register}
                                        errors={errors}
                                        disponibilidad={disponibilidad}
                                        cargandoDisponibilidad={
                                            cargandoDisponibilidad
                                        }
                                        horaSeleccionada={horaSeleccionada}
                                        adultosSeleccionados={
                                            adultosSeleccionados
                                        }
                                        espacioIdSeleccionado={
                                            espacioIdSeleccionado
                                        }
                                        mesas={mesas}
                                        cambiarFecha={cambiarFecha}
                                        seleccionarHora={seleccionarHora}
                                        ajustarComensales={ajustarComensales}
                                        seleccionarComensales={
                                            seleccionarComensales
                                        }
                                        seleccionarMesaEnPlano={
                                            seleccionarMesaEnPlano
                                        }
                                    />

                                    {/* Botones de Navegación del Paso 1 */}
                                    <div className="flex items-center justify-between gap-3 border-t border-border pt-4">
                                        <div />
                                        <div className="flex items-center gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() => irPaso(3)}
                                                className="h-11 rounded-2xl px-4 text-xs font-bold"
                                            >
                                                <span>Saltar al contacto</span>
                                            </Button>
                                            <Button
                                                type="button"
                                                onClick={() => irPaso(2)}
                                                className="h-11 rounded-2xl bg-primary px-6 text-xs font-black text-primary-foreground shadow-md hover:bg-primary/90"
                                            >
                                                <span>
                                                    Pre-ordenar Menú (Opcional)
                                                </span>
                                                <ArrowRight className="ml-2 size-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {pasoActual === 2 && (
                                <div className="space-y-6">
                                    <RestaurantePasoPreordenMenu
                                        menu={menu}
                                        platosPreorden={platosPreorden}
                                        categoriaMenuFiltro={
                                            categoriaMenuFiltro
                                        }
                                        categoriasMenu={categoriasMenu}
                                        menuFiltrado={menuFiltrado}
                                        agregarPlatoPreorden={
                                            agregarPlatoPreorden
                                        }
                                        removerPlatoPreorden={
                                            removerPlatoPreorden
                                        }
                                        alCambiarCategoria={alCambiarCategoria}
                                    />

                                    {/* Botones de Navegación del Paso 2 */}
                                    <div className="flex items-center justify-between gap-3 border-t border-border pt-4">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => irPaso(1)}
                                            className="h-11 rounded-2xl px-5 text-xs font-bold"
                                        >
                                            <ArrowLeft className="mr-2 size-4" />
                                            <span>Modificar Horario</span>
                                        </Button>

                                        <Button
                                            type="button"
                                            onClick={() => irPaso(3)}
                                            className="h-11 rounded-2xl bg-primary px-8 text-xs font-black text-primary-foreground shadow-md hover:bg-primary/90"
                                        >
                                            <span>Continuar a tus Datos</span>
                                            <ArrowRight className="ml-2 size-4" />
                                        </Button>
                                    </div>
                                </div>
                            )}

                            {pasoActual === 3 && (
                                <form onSubmit={onSubmit} className="space-y-6">
                                    <ReservaPasoCliente
                                        register={register}
                                        errors={errors}
                                        ocasionesRapidas={OCASIONES_RAPIDAS}
                                        alAgregarNotaRapida={agregarNotaRapida}
                                        titulo="Datos del Titular de la Reserva"
                                        subtitulo="Completa tus datos para confirmar tu mesa de inmediato."
                                        placeholderNotas="Indícanos si celebras algún evento especial o tienes alguna preferencia..."
                                        variante="pagina"
                                    />

                                    {errorEnvio && (
                                        <div className="rounded-2xl border border-destructive/40 bg-destructive/10 p-3 text-xs font-bold text-destructive">
                                            {errorEnvio}
                                        </div>
                                    )}

                                    <div className="flex items-center justify-between gap-3 border-t border-border pt-4">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => irPaso(2)}
                                            className="h-11 rounded-2xl px-5 text-xs font-bold"
                                        >
                                            <ArrowLeft className="mr-2 size-4" />
                                            <span>Paso Anterior</span>
                                        </Button>

                                        <Button
                                            type="submit"
                                            disabled={isSubmitting}
                                            className="h-12 rounded-2xl bg-primary px-8 text-xs font-black text-primary-foreground shadow-lg hover:bg-primary/90"
                                        >
                                            {isSubmitting ? (
                                                <>
                                                    <Loader2 className="mr-2 size-4 animate-spin" />
                                                    <span>
                                                        Confirmando reserva...
                                                    </span>
                                                </>
                                            ) : (
                                                <>
                                                    <CheckCircle2 className="mr-2 size-4" />
                                                    <span>
                                                        Confirmar Reservación de
                                                        Mesa
                                                    </span>
                                                </>
                                            )}
                                        </Button>
                                    </div>
                                </form>
                            )}
                        </div>

                        {/* Columna Derecha: Sticky BookingSummary & Mobile Bottom Drawer */}
                        <div className="lg:col-span-4">
                            <BookingSummary
                                title="Tu Mesa"
                                subtitle="Reserva de cortesía en restaurante"
                                resourceName={datosRestaurante.nombre}
                                resourceCategory="Gastronomía & Cocina de Autor"
                                rows={summaryRows}
                                pricing={pricingData}
                                guarantees={[
                                    'Sin costo por reservación de mesa',
                                    'Mesa reservada y lista a tu llegada',
                                    'Tolerancia de 15 minutos de cortesía',
                                ]}
                            />
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default RestauranteReservar;
