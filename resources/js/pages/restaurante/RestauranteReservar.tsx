import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { RestaurantePasoDatosContacto } from '@/modules/restaurante/components/reserva/RestaurantePasoDatosContacto';
import { RestaurantePasoMesaHorario } from '@/modules/restaurante/components/reserva/RestaurantePasoMesaHorario';
import { RestaurantePasoPreordenMenu } from '@/modules/restaurante/components/reserva/RestaurantePasoPreordenMenu';
import { RestauranteReservaExitosa } from '@/modules/restaurante/components/reserva/RestauranteReservaExitosa';
import { RestauranteResumenReservaSidebar } from '@/modules/restaurante/components/reserva/RestauranteResumenReservaSidebar';
import { RestauranteStepperReserva } from '@/modules/restaurante/components/reserva/RestauranteStepperReserva';
import { crearRestaurantePorDefecto } from '@/modules/restaurante/constants';
import { useReservaFlujoReserva } from '@/modules/restaurante/hooks/useReservaFlujoReserva';
import { useReservaMesaForm } from '@/modules/restaurante/hooks/useReservaMesaForm';
import type { RestaurantePageProps } from '@/modules/restaurante/types';

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

                <RestauranteStepperReserva
                    pasoActual={pasoActual}
                    alHacerClic={irPaso}
                />

                {reservaCreada ? (
                    <RestauranteReservaExitosa
                        reservaCreada={reservaCreada}
                        nombreRestaurante={datosRestaurante.nombre}
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
                                <RestaurantePasoDatosContacto
                                    register={register}
                                    errors={errors}
                                    onSubmit={onSubmit}
                                    isSubmitting={isSubmitting}
                                    errorEnvio={errorEnvio}
                                    alAgregarNotaRapida={agregarNotaRapida}
                                />
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
