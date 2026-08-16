import { useCallback, useEffect, useState } from 'react';
import type { DatosReserva, ServicioExtra } from '@/modulos/compartido/types';
import type {
    DatosContactoPago,
    MetodoPago,
    ConfiguracionStripePago,
} from '@/modulos/pagos/interfaces/pago';
import { construirMensajeWhatsApp } from '@/modulos/pagos/utilidades/construirMensajeWhatsApp';

interface OpcionesProcesoPago {
    datosReserva: DatosReserva;
    serviciosExtras: ServicioExtra[];
    telefonoHotel: string;
}

const DATOS_CONTACTO_INICIALES: DatosContactoPago = {
    nombre: '',
    apellido: '',
    correo: '',
    telefono: '',
    peticiones: '',
};

const describirErrorPago = (payload: Record<string, unknown>): string => {
    const mensaje =
        typeof payload.message === 'string' && payload.message.trim() !== ''
            ? payload.message
            : 'No se pudo preparar el pago con Stripe.';
    const details =
        payload.details && typeof payload.details === 'object'
            ? (payload.details as Record<string, unknown>)
            : {};
    const debug =
        payload.debug && typeof payload.debug === 'object'
            ? (payload.debug as Record<string, unknown>)
            : {};
    const lineas = [
        ['HTTP', details.http_status],
        ['Tipo Stripe', details.stripe_type],
        ['Codigo Stripe', details.stripe_code],
        ['Decline code', details.decline_code],
        ['Parametro', details.param],
        ['Request ID', details.request_id],
        ['Excepcion', debug.exception],
        ['Codigo excepcion', debug.code],
        ['Archivo', debug.file],
        ['Linea', debug.line],
    ]
        .filter(
            ([, valor]) =>
                valor !== null && valor !== undefined && valor !== '',
        )
        .map(([etiqueta, valor]) => `${etiqueta}: ${String(valor)}`);

    return lineas.length > 0 ? `${mensaje}\n${lineas.join('\n')}` : mensaje;
};

export const useProcesoPago = ({
    datosReserva,
    serviciosExtras,
    telefonoHotel,
}: OpcionesProcesoPago) => {
    const [pasoActual, establecerPasoActual] = useState(
        datosReserva.id ? 3 : 1,
    );
    const [metodoPago, establecerMetodoPago] = useState<MetodoPago>('tarjeta');
    const [serviciosSeleccionados, establecerServiciosSeleccionados] = useState<
        string[]
    >([]);
    const [datosContacto, establecerDatosContacto] =
        useState<DatosContactoPago>(DATOS_CONTACTO_INICIALES);
    const [stripePago, establecerStripePago] =
        useState<ConfiguracionStripePago | null>(null);
    const [preparandoStripe, establecerPreparandoStripe] = useState(false);
    const [errorStripe, establecerErrorStripe] = useState<string | null>(null);

    const totalExtras = serviciosSeleccionados.reduce((total, id) => {
        const servicio = serviciosExtras.find((opcion) => opcion.id === id);

        return total + (servicio?.precio ?? 0);
    }, 0);
    const totalFinal = datosReserva.total + totalExtras;

    useEffect(() => {
        window.scrollTo(0, 0);
    }, [pasoActual]);

    const alternarServicio = (id: string) => {
        establecerServiciosSeleccionados((seleccionados) =>
            seleccionados.includes(id)
                ? seleccionados.filter((servicioId) => servicioId !== id)
                : [...seleccionados, id],
        );
    };

    const actualizarDatoContacto = <Campo extends keyof DatosContactoPago>(
        campo: Campo,
        valor: DatosContactoPago[Campo],
    ) => {
        establecerDatosContacto((datosAnteriores) => ({
            ...datosAnteriores,
            [campo]: valor,
        }));
    };

    const irAlPaso = (paso: number) => {
        establecerPasoActual(Math.min(Math.max(paso, 1), 4));
    };

    const retroceder = () => {
        establecerPasoActual((paso) => Math.max(paso - 1, 1));
    };

    const confirmarReserva = () => {
        const numeroWhatsApp = telefonoHotel.replace(/[^0-9]/g, '');
        const mensaje = construirMensajeWhatsApp({
            datosContacto,
            datosReserva,
            serviciosExtras,
            serviciosSeleccionados,
            total: totalFinal,
        });

        window.open(
            `https://wa.me/${numeroWhatsApp}?text=${mensaje}`,
            '_blank',
        );
        irAlPaso(4);
    };

    const completarPagoEnLinea = () => {
        irAlPaso(4);
    };

    const prepararPagoStripe = useCallback(async () => {
        if (!datosReserva.id) {
            establecerErrorStripe(
                'La reserva aun no tiene identificador para pago en linea.',
            );

            return;
        }

        establecerPreparandoStripe(true);
        establecerErrorStripe(null);

        const controlador = new AbortController();
        const timeout = window.setTimeout(() => controlador.abort(), 15000);

        try {
            const response = await fetch('/pagos/stripe/reservas/intento', {
                method: 'POST',
                signal: controlador.signal,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector<HTMLMetaElement>(
                                'meta[name="csrf-token"]',
                            )
                            ?.getAttribute('content') ?? '',
                },
                body: JSON.stringify({
                    reserva_id: datosReserva.id,
                    codigo_reserva: datosReserva.codigoReserva,
                }),
            });

            const contentType = response.headers.get('content-type') ?? '';
            const payload = contentType.includes('application/json')
                ? await response.json()
                : { message: await response.text() };

            if (!response.ok) {
                throw new Error(describirErrorPago(payload));
            }

            if (!payload.client_secret || !payload.publishable_key) {
                throw new Error(
                    'Stripe no devolvio la configuracion necesaria para mostrar el formulario.',
                );
            }

            establecerStripePago({
                reservaId: datosReserva.id,
                clientSecret: payload.client_secret,
                publishableKey: payload.publishable_key,
                monto: Number(payload.monto),
                moneda: String(payload.moneda),
            });
        } catch (error) {
            establecerErrorStripe(
                error instanceof DOMException && error.name === 'AbortError'
                    ? 'Stripe no respondio a tiempo. Revisa conexion, llaves de prueba y acceso a internet del servidor.'
                    : error instanceof Error
                      ? error.message
                      : 'No se pudo preparar el pago con Stripe.',
            );
        } finally {
            window.clearTimeout(timeout);
            establecerPreparandoStripe(false);
        }
    }, [datosReserva.codigoReserva, datosReserva.id]);

    useEffect(() => {
        if (
            pasoActual !== 3 ||
            metodoPago !== 'tarjeta' ||
            stripePago !== null ||
            preparandoStripe ||
            errorStripe !== null
        ) {
            return;
        }

        const timeout = window.setTimeout(() => {
            void prepararPagoStripe();
        }, 0);

        return () => window.clearTimeout(timeout);
    }, [
        errorStripe,
        metodoPago,
        pasoActual,
        preparandoStripe,
        prepararPagoStripe,
        stripePago,
    ]);

    return {
        pasoActual,
        metodoPago,
        serviciosSeleccionados,
        datosContacto,
        totalExtras,
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
    };
};
