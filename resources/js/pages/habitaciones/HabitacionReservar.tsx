import { Head, router } from '@inertiajs/react';
import React, { useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';
import type { PropiedadesReservarHabitacion } from '@/modulos/habitaciones/interfaces/reservaHabitacion';
import type {
    ConfiguracionStripePago,
    StripeElementsInstance,
    StripeInstance,
} from '@/modulos/pagos/interfaces/pago';
import { PasosReservaHabitacion } from '@/modulos/reservas/componentes/PasosReservaHabitacion';
import { PlantillaProcesoReserva } from '@/modulos/reservas/componentes/PlantillaProcesoReserva';
import { useCalculoFechasDisponibilidad } from '@/modulos/reservas/hooks/useCalculoFechasDisponibilidad';
import { useFormularioReservaHabitacion } from '@/modulos/reservas/hooks/useFormularioReservaHabitacion';
import type { DatosBorradorHabitacion } from '@/modulos/reservas/interfaces/borradorReserva';

const PASOS_RESERVA = [
    { id: 1, titulo: 'Fechas & Titular' },
    { id: 2, titulo: 'Huéspedes' },
    { id: 3, titulo: 'Adicionales' },
    { id: 4, titulo: 'Confirmación' },
];

const construirFechaLocal = (fecha: string): Date | null => {
    const partes = fecha.split('-').map(Number);

    if (partes.length !== 3 || partes.some(Number.isNaN)) {
        return null;
    }

    return new Date(partes[0], partes[1] - 1, partes[2], 12, 0, 0);
};

const formatearFechaLocal = (fecha: Date): string => {
    const year = fecha.getFullYear();
    const month = String(fecha.getMonth() + 1).padStart(2, '0');
    const day = String(fecha.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const calcularNoches = (checkIn: string, checkOut: string): number => {
    const inicio = construirFechaLocal(checkIn);
    const salida = construirFechaLocal(checkOut);

    if (!inicio || !salida) {
        return 0;
    }

    return Math.max(
        0,
        Math.ceil((salida.getTime() - inicio.getTime()) / 86400000),
    );
};

const HabitacionReservar = ({
    room,
    opcionesReserva,
    diasAgotadosHabitacion = [],
    ocupacionHabitacionPorDia = {},
    totalHabitacionesCategoria = 0,
}: PropiedadesReservarHabitacion) => {
    const {
        data,
        setData,
        processing,
        errors,
        pasoActual,
        avanzar,
        retroceder,
        irAlPaso,
        limpiarBorrador,
    } = useFormularioReservaHabitacion(room);

    const [promoAplicada] = useState<string | null>(() => {
        if (typeof window === 'undefined') {
            return null;
        }

        const params = new URLSearchParams(window.location.search);

        return params.get('promo') || params.get('codigo_promocional');
    });
    const [creandoReserva, setCreandoReserva] = useState(false);
    const [preparandoStripe, setPreparandoStripe] = useState(false);
    const [procesandoStripe, setProcesandoStripe] = useState(false);
    const [errorStripe, setErrorStripe] = useState<string | null>(null);
    const [stripePago, setStripePago] =
        useState<ConfiguracionStripePago | null>(null);
    const [reservaCreada, setReservaCreada] = useState<{
        id: number;
        codigo_reserva: string;
    } | null>(null);
    const stripeRef = useRef<StripeInstance | null>(null);
    const elementsRef = useRef<StripeElementsInstance | null>(null);

    const calendario = useCalculoFechasDisponibilidad({
        diasAgotadosHabitacion,
        ocupacionHabitacionPorDia,
        totalHabitacionesCategoria,
        fechaCheckIn: data.fecha_check_in,
        fechaCheckOut: data.fecha_check_out,
        onSelectFechas: (checkIn, checkOut) => {
            setData((prev: DatosBorradorHabitacion) => ({
                ...prev,
                fecha_check_in: checkIn,
                fecha_check_out: checkOut,
            }));
        },
    });

    const rangoExactoDisponible = useMemo(() => {
        if (!data.fecha_check_in || !data.fecha_check_out) {
            return null;
        }

        return !calendario.rangoTieneNochesAgotadas(
            data.fecha_check_in,
            data.fecha_check_out,
        );
    }, [calendario, data.fecha_check_in, data.fecha_check_out]);

    const recomendacionesDisponibilidad = useMemo(() => {
        if (
            !data.fecha_check_in ||
            !data.fecha_check_out ||
            rangoExactoDisponible !== false
        ) {
            return [];
        }

        const nochesSolicitadas = calcularNoches(
            data.fecha_check_in,
            data.fecha_check_out,
        );
        const inicioSolicitado = construirFechaLocal(data.fecha_check_in);

        if (!inicioSolicitado || nochesSolicitadas <= 0) {
            return [];
        }

        const recomendaciones = [];

        for (let desplazamiento = -7; desplazamiento <= 14; desplazamiento++) {
            const inicio = new Date(inicioSolicitado);
            inicio.setDate(inicio.getDate() + desplazamiento);

            const salida = new Date(inicio);
            salida.setDate(salida.getDate() + nochesSolicitadas);

            const checkIn = formatearFechaLocal(inicio);
            const checkOut = formatearFechaLocal(salida);

            if (
                checkIn === data.fecha_check_in ||
                calendario.rangoTieneNochesAgotadas(checkIn, checkOut)
            ) {
                continue;
            }

            const disponibilidadEntrada =
                ocupacionHabitacionPorDia[checkIn]?.disponibles ??
                totalHabitacionesCategoria;

            recomendaciones.push({
                fecha_check_in: checkIn,
                fecha_check_out: checkOut,
                noches: nochesSolicitadas,
                disponibles_minimos: disponibilidadEntrada,
            });

            if (recomendaciones.length >= 3) {
                break;
            }
        }

        return recomendaciones;
    }, [
        calendario,
        data.fecha_check_in,
        data.fecha_check_out,
        ocupacionHabitacionPorDia,
        rangoExactoDisponible,
        totalHabitacionesCategoria,
    ]);

    const imagenPrincipal = useMemo(
        () =>
            room.imagenes && room.imagenes.length > 0
                ? room.imagenes[0]
                : '/images/main-room.webp',
        [room.imagenes],
    );

    const subtotalEstimado = (room.precio || 0) * calendario.nochesCalculadas;
    const procesando =
        processing || creandoReserva || preparandoStripe || procesandoStripe;

    const validarPasoActual = (): boolean => {
        if (pasoActual !== 1) {
            return true;
        }

        if (!data.fecha_check_in || !data.fecha_check_out) {
            toast.error(
                'Seleccione la fecha de entrada y salida en el calendario.',
            );

            return false;
        }

        if (
            calendario.rangoTieneNochesAgotadas(
                data.fecha_check_in,
                data.fecha_check_out,
            )
        ) {
            toast.error('Las fechas seleccionadas no están disponibles.');

            return false;
        }

        if (
            !(data.nombre_cliente ?? '').trim() ||
            !(data.telefono_cliente ?? '').trim()
        ) {
            toast.error('Ingrese su nombre y teléfono para continuar.');

            return false;
        }

        return true;
    };

    const csrfToken = () =>
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    const crearReservaEnPaso = async () => {
        setCreandoReserva(true);
        setErrorStripe(null);

        const payload = {
            ...data,
            tipo_pago_reserva: data.tipo_pago_reserva || 'abono_50',
            canal_pago_reserva: data.canal_pago_reserva || 'stripe',
            origen_pago_reserva: 'publico',
            metodo_pago_reserva:
                data.canal_pago_reserva === 'transferencia' ? 4 : null,
        };

        try {
            const response = await fetch('/reservas', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(payload),
            });
            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'No se pudo crear la reserva.',
                );
            }

            setReservaCreada(result.reserva);
            limpiarBorrador();
            toast.success(result.message || 'Reserva creada correctamente.');

            if (
                payload.canal_pago_reserva === 'stripe' &&
                result.requiere_pago_stripe
            ) {
                if (
                    result.stripe_pago?.client_secret &&
                    result.stripe_pago?.publishable_key
                ) {
                    setStripePago({
                        reservaId: result.reserva.id,
                        clientSecret: result.stripe_pago.client_secret,
                        publishableKey: result.stripe_pago.publishable_key,
                        monto: Number(result.stripe_pago.monto),
                        moneda: String(result.stripe_pago.moneda),
                    });

                    return;
                }

                await prepararStripeReserva(
                    result.reserva.id,
                    result.reserva.codigo_reserva,
                );

                return;
            }

            router.visit(
                `/mis-reservas?codigo=${encodeURIComponent(result.reserva.codigo_reserva)}`,
            );
        } catch (error) {
            setErrorStripe(
                error instanceof Error
                    ? error.message
                    : 'No se pudo crear la reserva.',
            );
            toast.error(
                error instanceof Error
                    ? error.message
                    : 'No se pudo crear la reserva.',
            );
        } finally {
            setCreandoReserva(false);
        }
    };

    const prepararStripeReserva = async (
        reservaId: number,
        codigoReserva: string,
    ) => {
        setPreparandoStripe(true);
        setErrorStripe(null);

        try {
            const response = await fetch('/pagos/stripe/reservas/intento', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    reserva_id: reservaId,
                    codigo_reserva: codigoReserva,
                }),
            });
            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'No se pudo preparar Stripe.',
                );
            }

            setStripePago({
                reservaId,
                clientSecret: result.client_secret,
                publishableKey: result.publishable_key,
                monto: Number(result.monto),
                moneda: String(result.moneda),
            });
            toast.success(
                'Formulario de tarjeta listo para confirmar el pago.',
            );
        } catch (error) {
            setErrorStripe(
                error instanceof Error
                    ? error.message
                    : 'No se pudo preparar Stripe.',
            );
            toast.error(
                error instanceof Error
                    ? error.message
                    : 'No se pudo preparar Stripe.',
            );
        } finally {
            setPreparandoStripe(false);
        }
    };

    useEffect(() => {
        if (!stripePago || elementsRef.current) {
            return;
        }

        const montarStripe = () => {
            if (!window.Stripe) {
                setErrorStripe('Stripe.js no está disponible.');

                return;
            }

            stripeRef.current = window.Stripe(stripePago.publishableKey);
            elementsRef.current = stripeRef.current.elements({
                clientSecret: stripePago.clientSecret,
            });
            elementsRef.current
                .create('payment', { layout: 'tabs' })
                .mount('#stripe-reserva-payment-element');
        };

        if (window.Stripe) {
            montarStripe();

            return;
        }

        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.async = true;
        script.onload = montarStripe;
        script.onerror = () => setErrorStripe('No se pudo cargar Stripe.js.');
        document.head.appendChild(script);
    }, [stripePago]);

    const confirmarStripeEnPaso = async () => {
        if (!stripeRef.current || !elementsRef.current || !reservaCreada) {
            setErrorStripe('El formulario de tarjeta todavía no está listo.');

            return;
        }

        setProcesandoStripe(true);
        setErrorStripe(null);

        const submit = await elementsRef.current.submit();

        if (submit.error) {
            setErrorStripe(
                submit.error.message ?? 'Revise los datos del pago.',
            );
            setProcesandoStripe(false);

            return;
        }

        const resultado = await stripeRef.current.confirmPayment({
            elements: elementsRef.current,
            confirmParams: {
                return_url: `${window.location.origin}/mis-reservas?codigo=${encodeURIComponent(reservaCreada.codigo_reserva)}`,
            },
            redirect: 'if_required',
        });

        if (resultado.error) {
            setProcesandoStripe(false);
            setErrorStripe(
                resultado.error.message ?? 'Stripe no pudo confirmar el pago.',
            );
            toast.error(
                resultado.error.message ?? 'Stripe no pudo confirmar el pago.',
            );

            return;
        }

        const paymentIntentId = resultado.paymentIntent?.id;

        if (!paymentIntentId) {
            setProcesandoStripe(false);
            setErrorStripe(
                'Stripe confirmo el pago, pero no devolvio el identificador para abonarlo.',
            );
            toast.error('No se pudo registrar el abono en la cuenta.');

            return;
        }

        let result: { message?: string } = {};

        try {
            const response = await fetch('/pagos/stripe/reservas/confirmar', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    reserva_id: reservaCreada.id,
                    codigo_reserva: reservaCreada.codigo_reserva,
                    payment_intent_id: paymentIntentId,
                }),
            });
            result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message ??
                        'El pago fue confirmado, pero no se pudo abonar en la cuenta.',
                );
            }
        } catch (error) {
            const mensaje =
                error instanceof Error
                    ? error.message
                    : 'No se pudo registrar el abono en la cuenta.';
            setProcesandoStripe(false);
            setErrorStripe(mensaje);
            toast.error(mensaje);

            return;
        }

        setProcesandoStripe(false);
        toast.success(
            result.message ?? 'Pago confirmado y abonado a la cuenta.',
        );
        router.visit(
            `/mis-reservas?codigo=${encodeURIComponent(reservaCreada.codigo_reserva)}`,
        );
    };

    const handleNextStep = async (e: React.SubmitEvent) => {
        e.preventDefault();

        if (!validarPasoActual()) {
            return;
        }

        if (pasoActual < 4) {
            avanzar();

            return;
        }

        if (stripePago) {
            await confirmarStripeEnPaso();

            return;
        }

        await crearReservaEnPaso();
    };

    return (
        <>
            <Head title={`Reservar ${room.nombre} - Hotel Bugambilias`} />

            <PlantillaProcesoReserva
                nombreRecurso={room.nombre}
                categoriaRecurso={room.categoria}
                tipoEtiqueta="Tipo de habitación"
                ubicacionRecurso={room.ubicacion}
                camasRecurso={room.camas}
                imagenPrincipal={imagenPrincipal}
                slugRecurso={room.slug}
                rutaRetorno={`/habitaciones/${room.slug}`}
                promoAplicada={promoAplicada}
                pasoActual={pasoActual}
                totalPasos={4}
                pasos={PASOS_RESERVA}
                errores={errors}
                procesando={procesando}
                onRetroceder={retroceder}
                onIrAlPaso={irAlPaso}
                onSubmit={handleNextStep}
            >
                <PasosReservaHabitacion
                    pasoActual={pasoActual}
                    room={room}
                    opcionesReserva={opcionesReserva}
                    data={data}
                    setData={setData}
                    calendario={calendario}
                    totalHabitacionesCategoria={totalHabitacionesCategoria}
                    rangoExactoDisponible={rangoExactoDisponible}
                    recomendacionesDisponibilidad={
                        recomendacionesDisponibilidad
                    }
                    subtotalEstimado={subtotalEstimado}
                    stripePago={stripePago}
                    preparandoStripe={preparandoStripe || creandoReserva}
                    errorStripe={errorStripe}
                />
            </PlantillaProcesoReserva>
        </>
    );
};

export default HabitacionReservar;
