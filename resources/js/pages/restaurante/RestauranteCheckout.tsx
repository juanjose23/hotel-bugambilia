import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Lock, Utensils, AlertCircle } from 'lucide-react';
import { useState } from 'react';
import { RestauranteCheckoutResumenSidebar } from '@/modules/restaurante/components/RestauranteCheckoutResumenSidebar';
import { RestaurantePasoArticulos } from '@/modules/restaurante/components/RestaurantePasoArticulos';
import { RestaurantePasoEntrega } from '@/modules/restaurante/components/RestaurantePasoEntrega';
import { RestaurantePasoPago } from '@/modules/restaurante/components/RestaurantePasoPago';
import { RestauranteReservaMesaModal } from '@/modules/restaurante/components/RestauranteReservaMesaModal';
import { crearRestaurantePorDefecto } from '@/modules/restaurante/constants';
import { useCarritoRestaurante } from '@/modules/restaurante/hooks/useCarritoRestaurante';
import { usePedidoDeliveryForm } from '@/modules/restaurante/hooks/usePedidoDeliveryForm';
import type { RestauranteCheckoutPageProps } from '@/modules/restaurante/types';
import { GuiaPasoAPaso } from '@/modules/shared/components/GuiaPasoAPaso';
import { StepperHeader } from '@/modules/shared/components/StepperHeader';

const PASOS_CHECKOUT = [
    { num: 1, titulo: 'Artículos', subtitulo: 'Revisión de platillos' },
    { num: 2, titulo: 'Entrega', subtitulo: 'Dirección y contacto' },
    { num: 3, titulo: 'Pago', subtitulo: 'Efectivo o Tarjeta' },
];

export const RestauranteCheckout = ({
    restaurante,
    departamentos_delivery = [],
    tarjeta_disponible = false,
}: RestauranteCheckoutPageProps) => {
    const [modalReservaAbierto, setModalReservaAbierto] = useState(false);

    const datosRestaurante =
        restaurante || crearRestaurantePorDefecto(departamentos_delivery);

    const carrito = useCarritoRestaurante({
        costoDelivery: datosRestaurante.costo_delivery ?? 50,
        pedidoMinimo: datosRestaurante.pedido_minimo ?? 0,
    });

    const {
        register,
        setValue,
        errors,
        isSubmitting,
        pasoActual,
        irAlPaso,
        avanzarPaso,
        retrocederPaso,
        departamentosActivos,
        municipiosDisponibles,
        departamentoSeleccionado,
        municipioSeleccionado,
        metodoPagoSeleccionado,
        costoEnvioCalculado,
        pedidoCreado,
        stripeData,
        confirmandoStripe,
        errorEnvio,
        onSubmit,
        handleStripeSuccess,
        reiniciarPedido,
        setStripeData,
        cambiarDepartamento,
    } = usePedidoDeliveryForm({
        items: carrito.items,
        costoDelivery: carrito.costoDelivery,
        departamentosDisponibles:
            departamentos_delivery.length > 0
                ? departamentos_delivery
                : undefined,
        alEnviarExitoso: () => {
            carrito.vaciarCarrito();
        },
    });

    return (
        <div className="min-h-screen bg-background font-sans text-foreground">
            <Head>
                <title>{`Checkout de Pedido — ${datosRestaurante.nombre}`}</title>
                <meta
                    name="description"
                    content={`Finaliza tu orden a domicilio o para llevar en ${datosRestaurante.nombre} con entrega express en Estelí.`}
                />
            </Head>

            <div className="container mx-auto px-4 py-6 pb-28 sm:px-6 lg:max-w-6xl lg:pb-12">
                {/* Subheader de retorno y título */}
                <div className="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-border/60 pb-4">
                    <Link
                        href="/restaurante"
                        className="inline-flex items-center gap-2 text-xs font-bold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft className="size-4" />
                        <span>Volver al Menú del Restaurante</span>
                    </Link>

                    <div className="flex items-center gap-3">
                        <div className="hidden items-center gap-1.5 text-xs font-medium text-muted-foreground sm:flex">
                            <Lock className="size-3.5 text-emerald-600 dark:text-emerald-400" />
                            <span>Pedido Seguro & Encriptado</span>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-black text-primary dark:bg-rose-950/50 dark:text-rose-300">
                            <Utensils className="size-3.5" />
                            <span>{datosRestaurante.nombre}</span>
                        </div>
                    </div>
                </div>

                {/* Stepper Superior */}
                <StepperHeader
                    pasos={PASOS_CHECKOUT}
                    pasoActual={pasoActual}
                    onCambiarPaso={(p) => irAlPaso(p as 1 | 2 | 3)}
                />

                {/* Error de envío si ocurre */}
                <div className="mb-4 flex justify-end">
                    <GuiaPasoAPaso
                        pasos={[
                            {
                                titulo: '1. Revisa el carrito',
                                descripcion:
                                    'Comprueba platillos, cantidades y observaciones antes de continuar. El restaurante valida las tarifas vigentes al recibir el pedido.',
                            },
                            {
                                titulo: '2. Entrega y contacto',
                                descripcion:
                                    'Indica departamento, municipio y dirección. El costo de envío se incluye en la cuenta del pedido.',
                            },
                            {
                                titulo: '3. Pago y seguimiento',
                                descripcion:
                                    'Con tarjeta, cocina espera la confirmación del pago. En efectivo, el pedido se prepara y caja mantiene el cobro pendiente hasta recibirlo. Al finalizar tendrás una pantalla de seguimiento.',
                            },
                        ]}
                    />
                </div>
                {errorEnvio && (
                    <div className="animate-in fade-in mb-6 flex items-start gap-2.5 rounded-2xl border border-destructive/30 bg-destructive/10 p-4 text-xs text-destructive duration-200">
                        <AlertCircle className="mt-0.5 size-4 shrink-0" />
                        <span className="font-bold">{errorEnvio}</span>
                    </div>
                )}

                {/* Layout Principal: 2 Columnas */}
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    {/* Columna Izquierda: Formulario por Pasos */}
                    <div className="space-y-6 lg:col-span-7 xl:col-span-8">
                        <form onSubmit={onSubmit}>
                            {pasoActual === 1 && (
                                <RestaurantePasoArticulos
                                    carrito={carrito}
                                    alAvanzar={avanzarPaso}
                                />
                            )}

                            {pasoActual === 2 && (
                                <RestaurantePasoEntrega
                                    register={register}
                                    setValue={setValue}
                                    errors={errors}
                                    departamentos={departamentosActivos}
                                    municipios={municipiosDisponibles}
                                    departamentoSeleccionado={
                                        departamentoSeleccionado
                                    }
                                    municipioSeleccionado={
                                        municipioSeleccionado
                                    }
                                    costoEnvio={costoEnvioCalculado}
                                    moneda={carrito.moneda}
                                    alCambiarDepartamento={cambiarDepartamento}
                                    alAvanzar={avanzarPaso}
                                    alRetroceder={retrocederPaso}
                                />
                            )}

                            {pasoActual === 3 && (
                                <RestaurantePasoPago
                                    tarjetaDisponible={tarjeta_disponible}
                                    register={register}
                                    setValue={setValue}
                                    errors={errors}
                                    metodoPagoSeleccionado={
                                        metodoPagoSeleccionado as
                                            'efectivo' | 'stripe'
                                    }
                                    isSubmitting={isSubmitting}
                                    stripeData={stripeData}
                                    confirmandoStripe={confirmandoStripe}
                                    pedidoCreado={pedidoCreado}
                                    total={
                                        pedidoCreado?.total ??
                                        carrito.subtotal + costoEnvioCalculado
                                    }
                                    moneda={carrito.moneda}
                                    onStripeSuccess={handleStripeSuccess}
                                    onCancelStripe={() =>
                                        pedidoCreado
                                            ? router.visit(
                                                  pedidoCreado.url_seguimiento ??
                                                      `/restaurante/pedidos/${pedidoCreado.pedido_id}`,
                                              )
                                            : setStripeData(null)
                                    }
                                    alRetroceder={retrocederPaso}
                                    alReiniciar={reiniciarPedido}
                                />
                            )}
                        </form>
                    </div>

                    {/* Columna Derecha: Barra Lateral con Resumen */}
                    <div className="lg:col-span-5 xl:col-span-4">
                        <RestauranteCheckoutResumenSidebar
                            items={carrito.items}
                            subtotal={carrito.subtotal}
                            costoDelivery={costoEnvioCalculado}
                            total={
                                pedidoCreado?.total ??
                                carrito.subtotal + costoEnvioCalculado
                            }
                            moneda={carrito.moneda}
                            departamento={departamentoSeleccionado}
                            municipio={municipioSeleccionado}
                            totalDefinitivo={pedidoCreado !== null}
                            pedidoMinimo={datosRestaurante.pedido_minimo ?? 0}
                            alAbrirReservaMesa={() =>
                                setModalReservaAbierto(true)
                            }
                        />
                    </div>
                </div>
            </div>

            {/* Modal de Reservación de Mesas */}
            <RestauranteReservaMesaModal
                abierto={modalReservaAbierto}
                alCambiarAbierto={setModalReservaAbierto}
                restaurante={datosRestaurante}
            />
        </div>
    );
};

export default RestauranteCheckout;
