import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, CheckCircle2, Loader2 } from 'lucide-react';
import { ReservaConfirmada } from '@/modules/reservas/components/ReservaConfirmada';
import { ReservaPasoCliente } from '@/modules/reservas/components/ReservaPasoCliente';
import { RestaurantePasoMesaHorario } from '@/modules/restaurante/components/reserva/RestaurantePasoMesaHorario';
import { RestaurantePasoPreordenMenu } from '@/modules/restaurante/components/reserva/RestaurantePasoPreordenMenu';
import { RestauranteResumenReservaSidebar } from '@/modules/restaurante/components/reserva/RestauranteResumenReservaSidebar';
import {
    OCASIONES_RAPIDAS,
    crearRestaurantePorDefecto,
} from '@/modules/restaurante/constants';
import { useReservaFlujoReserva } from '@/modules/restaurante/hooks/useReservaFlujoReserva';
import { useReservaMesaForm } from '@/modules/restaurante/hooks/useReservaMesaForm';
import type { RestaurantePageProps } from '@/modules/restaurante/types';
import { StepperHeader } from '@/modules/shared/components/StepperHeader';
import { Button } from '@/modules/shared/components/ui/button';

const PASOS_RESTAURANTE = [
    { num: 1, titulo: 'Mesa & Horario', subtitulo: 'Plano y fecha' },
    { num: 2, titulo: 'Pre-ordenar Menú', subtitulo: 'Opcional' },
    { num: 3, titulo: 'Confirmación', subtitulo: 'Datos de contacto' },
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

    return (
        <div className="min-h-screen bg-background font-sans text-foreground">
            <Head>
                <title>{`Reservación de Mesa — ${datosRestaurante.nombre}`}</title>
                <meta
                    name="description"
                    content={`Reserva tu mesa en ${datosRestaurante.nombre}. Explora el plano de distribución en tiempo real, elige tu ambiente favorito y asegura tu lugar.`}
                />
            </Head>

            <div className="container mx-auto px-4 py-6 pb-28 sm:px-6 lg:max-w-6xl lg:pb-12">
                <div className="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-border/60 pb-4">
                    <Link
                        href="/restaurante"
                        className="inline-flex items-center gap-2 text-xs font-bold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft className="size-4" />
                        <span>Volver al Restaurante</span>
                    </Link>

                    <span className="rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-black text-primary dark:text-rose-400">
                        Reservación Online
                    </span>
                </div>

                <StepperHeader
                    pasos={PASOS_RESTAURANTE}
                    pasoActual={pasoActual}
                    onCambiarPaso={(p) => irPaso(p as 1 | 2 | 3)}
                />

                {reservaCreada ? (
                    <ReservaConfirmada
                        codigoReserva={reservaCreada.codigo_reserva}
                        reservaId={reservaCreada.reserva_id}
                        titulo={`¡Mesa Reservada en ${datosRestaurante.nombre}!`}
                        subtitulo={`Hemos asegurado tu reservación en ${datosRestaurante.nombre}. Te esperamos puntualmente.`}
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
                        <div className="lg:col-span-8">
                            {pasoActual === 1 && (
                                <RestaurantePasoMesaHorario
                                    register={register}
                                    errors={errors}
                                    disponibilidad={disponibilidad}
                                    cargandoDisponibilidad={
                                        cargandoDisponibilidad
                                    }
                                    horaSeleccionada={horaSeleccionada}
                                    adultosSeleccionados={adultosSeleccionados}
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
                            )}

                            {pasoActual === 2 && (
                                <RestaurantePasoPreordenMenu
                                    menu={menu}
                                    platosPreorden={platosPreorden}
                                    categoriaMenuFiltro={categoriaMenuFiltro}
                                    categoriasMenu={categoriasMenu}
                                    menuFiltrado={menuFiltrado}
                                    agregarPlatoPreorden={agregarPlatoPreorden}
                                    removerPlatoPreorden={removerPlatoPreorden}
                                    alCambiarCategoria={alCambiarCategoria}
                                />
                            )}

                            {pasoActual === 3 && (
                                <form onSubmit={onSubmit} className="space-y-4">
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

                                    <Button
                                        type="submit"
                                        disabled={isSubmitting}
                                        className="h-12 w-full cursor-pointer rounded-2xl text-sm font-black shadow-md"
                                    >
                                        {isSubmitting ? (
                                            <>
                                                <Loader2 className="mr-2 size-4 animate-spin" />
                                                Confirmando Reserva...
                                            </>
                                        ) : (
                                            <>
                                                <CheckCircle2 className="mr-2 size-4" />
                                                Confirmar Reservación de Mesa
                                            </>
                                        )}
                                    </Button>
                                </form>
                            )}
                        </div>

                        <div className="lg:col-span-4">
                            <RestauranteResumenReservaSidebar
                                nombreRestaurante={datosRestaurante.nombre}
                                fechaSeleccionada={fechaSeleccionada}
                                horaSeleccionada={horaSeleccionada}
                                adultosSeleccionados={adultosSeleccionados}
                                disponibilidad={disponibilidad}
                                platosPreorden={platosPreorden}
                                subtotalPreorden={subtotalPreorden}
                                pasoActual={pasoActual}
                                irPaso={irPaso}
                            />
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default RestauranteReservar;
