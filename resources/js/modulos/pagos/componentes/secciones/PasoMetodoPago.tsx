import { Link } from '@inertiajs/react';
import {
    AlertCircle,
    CheckCircle,
    Clock,
    CreditCard,
    LoaderCircle,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Checkbox } from '@/modulos/compartido/ui/casilla';
import { Label } from '@/modulos/compartido/ui/etiqueta';
import type {
    ConfiguracionStripePago,
    MetodoPago,
    StripeElementsInstance,
    StripeInstance,
} from '@/modulos/pagos/interfaces/pago';

interface PropiedadesPasoMetodoPago {
    metodo: MetodoPago;
    total: number;
    stripePago: ConfiguracionStripePago | null;
    preparandoStripe: boolean;
    errorStripe: string | null;
    alCambiarMetodo: (metodo: MetodoPago) => void;
    alPrepararStripe: () => Promise<void>;
    alConfirmar: () => void;
    alPagoEnLineaConfirmado: () => void;
    politicaCancelacion?: {
        titulo: string;
        descripcion: string;
    } | null;
}

export const PasoMetodoPago = ({
    metodo,
    total,
    stripePago,
    preparandoStripe,
    errorStripe,
    alCambiarMetodo,
    alPrepararStripe,
    alConfirmar,
    alPagoEnLineaConfirmado,
    politicaCancelacion,
}: PropiedadesPasoMetodoPago) => {
    const [terminosAceptados, setTerminosAceptados] = useState(false);
    const [procesandoStripe, setProcesandoStripe] = useState(false);
    const [mensajeStripe, setMensajeStripe] = useState<string | null>(null);
    const [stripeMontado, setStripeMontado] = useState(false);
    const stripeRef = useRef<StripeInstance | null>(null);
    const elementsRef = useRef<StripeElementsInstance | null>(null);

    useEffect(() => {
        if (metodo !== 'tarjeta' || !stripePago || elementsRef.current) {
            return;
        }

        const montarStripe = () => {
            if (!window.Stripe) {
                setMensajeStripe('Stripe.js no esta disponible.');

                return;
            }

            setMensajeStripe(null);
            stripeRef.current = window.Stripe(stripePago.publishableKey);
            elementsRef.current = stripeRef.current.elements({
                clientSecret: stripePago.clientSecret,
            });
            elementsRef.current
                .create('payment', { layout: 'tabs' })
                .mount('#stripe-payment-element');
            setStripeMontado(true);
        };

        if (window.Stripe) {
            montarStripe();

            return;
        }

        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.async = true;
        script.onload = montarStripe;
        script.onerror = () =>
            setMensajeStripe(
                'No se pudo cargar Stripe.js. Revisa la conexion a internet o bloqueadores del navegador.',
            );
        document.head.appendChild(script);
    }, [metodo, stripePago]);

    const confirmarPagoStripe = async () => {
        if (!stripePago) {
            await alPrepararStripe();

            return;
        }

        if (!stripeRef.current || !elementsRef.current) {
            setMensajeStripe('El formulario de Stripe todavia no esta listo.');

            return;
        }

        setProcesandoStripe(true);
        setMensajeStripe(null);

        const submit = await elementsRef.current.submit();

        if (submit.error) {
            setMensajeStripe(
                submit.error.message ?? 'Revise los datos del pago.',
            );
            setProcesandoStripe(false);

            return;
        }

        const resultado = await stripeRef.current.confirmPayment({
            elements: elementsRef.current,
            confirmParams: {
                return_url: `${window.location.origin}/mis-reservas`,
            },
            redirect: 'if_required',
        });

        setProcesandoStripe(false);

        if (resultado.error) {
            setMensajeStripe(
                resultado.error.message ?? 'Stripe no pudo confirmar el pago.',
            );

            return;
        }

        alPagoEnLineaConfirmado();
    };

    return (
        <div className="animate-in fade-in slide-in-from-bottom-6 duration-700">
            <header className="mb-12">
                <h1 className="mb-4 text-4xl leading-none font-black tracking-tighter text-gray-900 md:text-6xl dark:text-white">
                    Detalles del{' '}
                    <span className="text-bugambilia-gradient bg-clip-text text-transparent italic">
                        pago
                    </span>
                </h1>
                <p className="text-lg font-medium text-gray-500">
                    Selecciona cómo deseas confirmar la reserva.
                </p>
            </header>

            <div className="space-y-10">
                <div className="grid grid-cols-1 gap-4">
                    <SelectorMetodoPago
                        metodo="tarjeta"
                        activo={metodo === 'tarjeta'}
                        titulo="Tarjeta con Stripe"
                        icono={CreditCard}
                        alSeleccionar={alCambiarMetodo}
                    />
                </div>

                {metodo === 'tarjeta' && (
                    <FormularioStripe
                        listo={stripeMontado}
                        tieneConfiguracion={stripePago !== null}
                        preparando={preparandoStripe}
                        error={errorStripe || mensajeStripe}
                    />
                )}

                <div className="rounded-3xl border border-bugambilia-100 bg-bugambilia-50/30 p-8 dark:border-bugambilia-800/50 dark:bg-bugambilia-900/10">
                    <div className="flex items-start gap-4">
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-bugambilia-100 bg-white shadow-sm dark:bg-gray-800">
                            <Clock className="h-5 w-5 text-bugambilia-600" />
                        </span>
                        <div>
                            <h4 className="mb-1 text-sm font-black tracking-tighter text-gray-900 uppercase dark:text-white">
                                {politicaCancelacion?.titulo ||
                                    'Política de Cancelación'}
                            </h4>
                            <p className="text-xs leading-relaxed font-medium text-gray-500">
                                {politicaCancelacion?.descripcion ||
                                    'Puedes cancelar sin cargos hasta 24 horas antes de tu llegada.'}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="pt-6">
                    <div className="mb-10 flex items-start gap-4">
                        <Checkbox
                            id="terminos_aceptados"
                            checked={terminosAceptados}
                            onCheckedChange={(valor) =>
                                setTerminosAceptados(valor === true)
                            }
                            className="mt-1 border-gray-200"
                        />
                        <Label
                            htmlFor="terminos_aceptados"
                            className="cursor-pointer text-[10px] leading-relaxed font-black tracking-widest text-gray-400 uppercase select-none"
                        >
                            He leído y acepto los{' '}
                            <Link
                                href="#"
                                className="text-gray-900 underline dark:text-white"
                            >
                                términos de servicio
                            </Link>{' '}
                            y las políticas de privacidad.
                        </Label>
                    </div>

                    <Button
                        type="button"
                        disabled={
                            !terminosAceptados ||
                            preparandoStripe ||
                            procesandoStripe
                        }
                        onClick={
                            metodo === 'tarjeta'
                                ? confirmarPagoStripe
                                : alConfirmar
                        }
                        className="bg-bugambilia-gradient transition-airbnb h-20 w-full rounded-[2rem] px-16 text-xs font-black tracking-[0.3em] text-white uppercase shadow-2xl hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                    >
                        {preparandoStripe
                            ? 'Preparando Stripe...'
                            : procesandoStripe
                              ? 'Procesando pago...'
                              : stripePago
                                ? `Confirmar reserva • $${total.toFixed(2)}`
                                : 'Cargar formulario de tarjeta'}
                    </Button>
                </div>
            </div>
        </div>
    );
};

interface PropiedadesSelectorMetodoPago {
    metodo: MetodoPago;
    activo: boolean;
    titulo: string;
    icono: typeof CreditCard;
    alSeleccionar: (metodo: MetodoPago) => void;
}

const SelectorMetodoPago = ({
    metodo,
    activo,
    titulo,
    icono: Icono,
    alSeleccionar,
}: PropiedadesSelectorMetodoPago) => {
    return (
        <button
            type="button"
            aria-pressed={activo}
            onClick={() => alSeleccionar(metodo)}
            className={`group transition-airbnb relative cursor-pointer overflow-hidden rounded-[2rem] border-2 p-5 text-left sm:p-8 ${
                activo
                    ? 'border-black bg-white shadow-lg dark:border-white dark:bg-gray-800'
                    : 'border-gray-100 bg-white/50 hover:border-gray-200 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-gray-700'
            }`}
        >
            {activo && (
                <CheckCircle className="animate-in zoom-in absolute top-4 right-4 h-4 w-4 fill-current text-black dark:text-white" />
            )}
            <span
                className={`transition-airbnb mb-4 flex h-10 w-10 items-center justify-center rounded-xl ${
                    activo
                        ? 'bg-black text-white dark:bg-white dark:text-black'
                        : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500'
                }`}
            >
                <Icono className="h-5 w-5" />
            </span>
            <span
                className={`text-[10px] font-black tracking-widest uppercase ${
                    activo
                        ? 'text-black dark:text-white'
                        : 'text-gray-400 dark:text-gray-500'
                }`}
            >
                {titulo}
            </span>
        </button>
    );
};

interface PropiedadesFormularioStripe {
    listo: boolean;
    tieneConfiguracion: boolean;
    preparando: boolean;
    error: string | null;
}

const FormularioStripe = ({
    listo,
    tieneConfiguracion,
    preparando,
    error,
}: PropiedadesFormularioStripe) => {
    return (
        <div className="shadow-airbnb rounded-[2.5rem] border border-gray-100 bg-white p-5 sm:p-8 md:p-10 dark:border-gray-800 dark:bg-gray-900">
            {preparando && (
                <p className="flex items-center gap-2 text-sm font-bold text-gray-500">
                    <LoaderCircle className="h-4 w-4 animate-spin" />
                    Preparando pago seguro...
                </p>
            )}
            {!preparando && !tieneConfiguracion && !error && (
                <p className="flex items-center gap-2 text-sm font-bold text-gray-500">
                    <CreditCard className="h-4 w-4" />
                    Acepta los terminos y carga el formulario seguro de tarjeta.
                </p>
            )}
            <div
                id="stripe-payment-element"
                className={listo ? 'min-h-24' : 'min-h-32'}
            />
            {error && (
                <div className="mt-5 flex items-start gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm font-bold whitespace-pre-line text-red-600">
                    <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
                    <span>{error}</span>
                </div>
            )}
        </div>
    );
};
