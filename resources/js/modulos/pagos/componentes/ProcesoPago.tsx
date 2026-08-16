import { usePage, Link } from '@inertiajs/react';
import {
    Lock,
    CheckCircle,
    MapPin,
    Calendar,
    Users,
    ChevronLeft,
    Star,
    HelpCircle,
    Clock,
} from 'lucide-react';
import type { DatosReserva, ServicioExtra } from '@/modulos/compartido/types';
import { Button } from '@/modulos/compartido/ui/boton';
import { CabeceraProcesoPago } from '@/modulos/pagos/componentes/secciones/CabeceraProcesoPago';
import { PasoDatosContactoPago } from '@/modulos/pagos/componentes/secciones/PasoDatosContactoPago';
import { PasoMetodoPago } from '@/modulos/pagos/componentes/secciones/PasoMetodoPago';
import { PasoServiciosExtras } from '@/modulos/pagos/componentes/secciones/PasoServiciosExtras';
import { ResumenPago } from '@/modulos/pagos/componentes/secciones/ResumenPago';
import { useProcesoPago } from '@/modulos/pagos/hooks/useProcesoPago';
const PASOS = [
    { id: 1, titulo: 'Detalles' },
    { id: 2, titulo: 'Servicios' },
    { id: 3, titulo: 'Pago' },
    { id: 4, titulo: 'Reserva' },
];
interface PropiedadesProcesoPago {
    datosReserva: DatosReserva;
    serviciosExtras?: ServicioExtra[];
}
export const ProcesoPago = ({
    datosReserva,
    serviciosExtras = [],
}: PropiedadesProcesoPago) => {
    const { hotel } = usePage().props;
    const {
        pasoActual,
        metodoPago,
        serviciosSeleccionados,
        datosContacto,
        totalFinal,
        stripePago,
        preparandoStripe,
        errorStripe,
        establecerMetodoPago,
        alternarServicio,
        actualizarDatoContacto,
        irAlPaso,
        retroceder,
        confirmarReserva,
        completarPagoEnLinea,
        prepararPagoStripe,
    } = useProcesoPago({
        datosReserva,
        serviciosExtras,
        telefonoHotel: hotel?.whatsapp || hotel?.telefono || '',
    });

    return (
        <main className="min-h-screen overflow-x-hidden bg-gray-50/50 pb-32 dark:bg-gray-950">
            <CabeceraProcesoPago
                nombreHotel={hotel.name}
                pasoActual={pasoActual}
                pasos={PASOS}
            />

            <div className="container mx-auto px-4 pt-12 md:px-8">
                <div className="mx-auto max-w-6xl">
                    {pasoActual < 4 && (
                        <button
                            onClick={() => pasoActual > 1 && retroceder()}
                            className="group transition-airbnb mb-10 flex items-center gap-2 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase hover:text-black dark:hover:text-white"
                        >
                            <div className="transition-airbnb flex h-8 w-8 items-center justify-center rounded-full border border-gray-100 group-hover:border-black">
                                <ChevronLeft className="h-4 w-4" />
                            </div>
                            {pasoActual === 1
                                ? 'Volver a la habitación'
                                : 'Paso anterior'}
                        </button>
                    )}

                    <div className="grid gap-12 lg:grid-cols-12 xl:gap-20">
                        <div
                            className={`lg:col-span-7 ${pasoActual === 3 ? 'lg:col-span-12' : ''}`}
                        >
                            {pasoActual === 1 && (
                                <div className="animate-in fade-in slide-in-from-bottom-6 duration-700">
                                    <header className="mb-12">
                                        <h1 className="mb-4 text-4xl leading-none font-black tracking-tighter text-gray-900 md:text-6xl dark:text-white">
                                            Tu reserva casi está{' '}
                                            <span className="text-bugambilia-gradient bg-clip-text text-transparent italic">
                                                lista
                                            </span>
                                        </h1>
                                        <p className="text-lg font-medium text-gray-500">
                                            Revisa los detalles antes de
                                            confirmar.
                                        </p>
                                    </header>

                                    <section className="space-y-12">
                                        <div className="shadow-airbnb rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
                                            <div className="mb-8 flex items-center justify-between">
                                                <h2 className="text-xl font-black tracking-tight text-gray-900 dark:text-white">
                                                    Tu viaje
                                                </h2>
                                                <button className="text-[10px] font-black tracking-widest text-bugambilia-600 uppercase underline underline-offset-4 hover:opacity-70">
                                                    Cambiar
                                                </button>
                                            </div>

                                            <div className="grid grid-cols-1 gap-8 sm:grid-cols-2">
                                                <div>
                                                    <p className="mb-2 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                                        Fechas
                                                    </p>
                                                    <div className="flex items-center gap-2">
                                                        <Calendar className="h-4 w-4 text-bugambilia-600" />
                                                        <span className="text-sm font-bold text-gray-900 dark:text-white">
                                                            {
                                                                datosReserva.fechaEntrada
                                                            }{' '}
                                                            –{' '}
                                                            {
                                                                datosReserva.fechaSalida
                                                            }
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p className="mb-2 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                                        Huéspedes
                                                    </p>
                                                    <div className="flex items-center gap-2">
                                                        <Users className="h-4 w-4 text-bugambilia-600" />
                                                        <span className="text-sm font-bold text-gray-900 dark:text-white">
                                                            {
                                                                datosReserva.huespedes
                                                            }{' '}
                                                            Personas
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <PasoDatosContactoPago
                                            datos={datosContacto}
                                            alCambiar={actualizarDatoContacto}
                                        />

                                        <div className="flex flex-col items-center gap-6 pt-6 sm:flex-row">
                                            <Button
                                                onClick={() => irAlPaso(2)}
                                                className="transition-airbnb h-16 w-full rounded-2xl bg-black px-12 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-xl hover:scale-105 active:scale-95 sm:w-auto dark:bg-white dark:text-black"
                                            >
                                                Continuar a servicios
                                            </Button>
                                            <div className="flex items-center gap-2 text-gray-400">
                                                <Lock className="h-3.5 w-3.5" />
                                                <span className="text-[9px] font-black tracking-widest uppercase">
                                                    Seguro y Privado
                                                </span>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            )}

                            {pasoActual === 2 && (
                                <PasoServiciosExtras
                                    servicios={serviciosExtras}
                                    seleccionados={serviciosSeleccionados}
                                    alAlternar={alternarServicio}
                                    alContinuar={() => irAlPaso(3)}
                                />
                            )}

                            {pasoActual === 3 && (
                                <PasoMetodoPago
                                    metodo={metodoPago}
                                    total={totalFinal}
                                    stripePago={stripePago}
                                    preparandoStripe={preparandoStripe}
                                    errorStripe={errorStripe}
                                    alCambiarMetodo={establecerMetodoPago}
                                    alPrepararStripe={prepararPagoStripe}
                                    alConfirmar={confirmarReserva}
                                    alPagoEnLineaConfirmado={
                                        completarPagoEnLinea
                                    }
                                />
                            )}

                            {pasoActual === 4 && (
                                <div className="animate-in fade-in zoom-in-95 mx-auto max-w-3xl py-12 duration-1000">
                                    <div className="mb-16 text-center">
                                        <div className="relative mb-10 inline-block">
                                            <div className="transition-airbnb mx-auto flex h-28 w-28 transform items-center justify-center rounded-[2.5rem] bg-emerald-50 shadow-2xl hover:rotate-6 dark:bg-emerald-900/20">
                                                <CheckCircle className="h-14 w-14 text-emerald-600" />
                                            </div>
                                            <div className="absolute -right-2 -bottom-2 flex h-10 w-10 animate-bounce items-center justify-center rounded-2xl bg-bugambilia-600 text-white shadow-lg">
                                                <Star className="h-5 w-5 fill-current" />
                                            </div>
                                        </div>
                                        <h1 className="mb-6 text-5xl leading-none font-black tracking-tighter text-gray-900 md:text-7xl dark:text-white">
                                            ¡Tu refugio en{' '}
                                            {
                                                datosReserva.ubicacion.split(
                                                    ',',
                                                )[0]
                                            }{' '}
                                            <span className="text-bugambilia-gradient bg-clip-text text-transparent italic">
                                                está listo!
                                            </span>
                                        </h1>
                                        <p className="mx-auto max-w-xl text-xl font-medium text-gray-500">
                                            Hemos enviado los detalles de tu
                                            reserva por WhatsApp. Pronto
                                            recibirás la confirmación.
                                        </p>
                                    </div>

                                    <div className="relative mb-12 overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white p-5 shadow-2xl sm:rounded-[3.5rem] sm:p-8 md:p-12 dark:border-gray-800 dark:bg-gray-900">
                                        <div className="pointer-events-none absolute top-0 right-0 h-32 w-32 rounded-bl-[100%] bg-bugambilia-50/50 dark:bg-bugambilia-900/10" />

                                        <div className="mb-12 flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">
                                            <div>
                                                <p className="mb-2 text-[10px] font-black tracking-[0.3em] text-gray-400 uppercase">
                                                    Reserva Enviada
                                                </p>
                                                <h4 className="text-3xl font-black tracking-widest text-gray-900 dark:text-white">
                                                    vía WhatsApp
                                                </h4>
                                            </div>
                                            <div className="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-6 py-3 text-emerald-600 dark:border-emerald-900 dark:bg-emerald-900/20">
                                                <Lock className="h-4 w-4" />
                                                <span className="text-xs font-black tracking-widest uppercase">
                                                    Solicitud Enviada
                                                </span>
                                            </div>
                                        </div>

                                        <div className="grid gap-10 md:grid-cols-2">
                                            <div className="space-y-8">
                                                <div>
                                                    <p className="mb-3 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase">
                                                        Habitación
                                                    </p>
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50">
                                                            <Calendar className="h-5 w-5 text-bugambilia-600" />
                                                        </div>
                                                        <span className="text-base font-bold text-gray-900 dark:text-white">
                                                            {
                                                                datosReserva.habitacion
                                                            }
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p className="mb-3 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase">
                                                        Entrada
                                                    </p>
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50">
                                                            <Clock className="h-5 w-5 text-bugambilia-600" />
                                                        </div>
                                                        <span className="text-base font-bold text-gray-900 dark:text-white">
                                                            {
                                                                datosReserva.fechaEntrada
                                                            }
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="space-y-8">
                                                <div>
                                                    <p className="mb-3 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase">
                                                        Huéspedes
                                                    </p>
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50">
                                                            <Users className="h-5 w-5 text-bugambilia-600" />
                                                        </div>
                                                        <span className="text-base font-bold text-gray-900 dark:text-white">
                                                            {
                                                                datosReserva.huespedes
                                                            }{' '}
                                                            Adultos
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p className="mb-3 text-[10px] font-black tracking-[0.2em] text-gray-400 uppercase">
                                                        Dirección
                                                    </p>
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50">
                                                            <MapPin className="h-5 w-5 text-bugambilia-600" />
                                                        </div>
                                                        <span className="text-sm leading-tight font-bold text-gray-900 dark:text-white">
                                                            {hotel.direccion}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="mt-12 flex flex-col items-center justify-between gap-6 border-t border-gray-50 pt-12 sm:flex-row dark:border-gray-800">
                                            <div className="flex items-center gap-2 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                                <HelpCircle className="h-4 w-4" />
                                                {`¿Necesitas soporte? ${hotel.telefono}`}
                                            </div>
                                            <div className="flex w-full gap-4 sm:w-auto">
                                                <Button
                                                    onClick={() =>
                                                        window.print()
                                                    }
                                                    className="transition-airbnb h-14 flex-1 rounded-2xl bg-black px-8 text-[10px] font-black tracking-widest text-white uppercase shadow-lg sm:flex-none dark:bg-white dark:text-black"
                                                >
                                                    Imprimir Folio
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="text-center">
                                        <Button
                                            variant="ghost"
                                            className="transition-airbnb h-14 rounded-2xl px-10 text-[10px] font-black tracking-[0.4em] text-gray-500 uppercase hover:bg-white hover:text-black"
                                            asChild
                                        >
                                            <Link href="/">
                                                Volver al portal principal
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {pasoActual < 4 && (
                            <ResumenPago
                                datosReserva={datosReserva}
                                serviciosExtras={serviciosExtras}
                                serviciosSeleccionados={serviciosSeleccionados}
                                total={totalFinal}
                                nombreHotel={hotel.name}
                            />
                        )}
                    </div>
                </div>
            </div>
        </main>
    );
};
