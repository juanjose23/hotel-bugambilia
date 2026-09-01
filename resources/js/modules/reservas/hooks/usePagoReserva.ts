import { useState, useEffect, useCallback } from 'react';
import { reservasService } from '../services/reservasService';
import type { StripePaymentData } from '../types';

interface DatosReservaPago {
    id: number;
    codigoReserva: string;
}

export const usePagoReserva = (datosReserva?: DatosReservaPago | null) => {
    const [stripeData, setStripeData] = useState<StripePaymentData | null>(
        null,
    );
    const [cargandoIntento, setCargandoIntento] = useState(false);
    const [errorIntento, setErrorIntento] = useState<string | null>(null);
    const [pagoCompletado, setPagoCompletado] = useState(false);

    const reservaId = datosReserva?.id;
    const codigoReserva = datosReserva?.codigoReserva;

    useEffect(() => {
        if (!reservaId || !codigoReserva) {
            return;
        }

        let isMounted = true;

        const obtenerIntento = async () => {
            setCargandoIntento(true);
            setErrorIntento(null);

            try {
                const data = await reservasService.obtenerIntentoPago({
                    reserva_id: reservaId,
                    codigo_reserva: codigoReserva,
                });

                if (isMounted) {
                    setStripeData(data);
                }
            } catch (err: unknown) {
                if (isMounted) {
                    setErrorIntento(
                        err instanceof Error
                            ? err.message
                            : 'Error al inicializar el pago.',
                    );
                }
            } finally {
                if (isMounted) {
                    setCargandoIntento(false);
                }
            }
        };

        obtenerIntento();

        return () => {
            isMounted = false;
        };
    }, [reservaId, codigoReserva]);

    const handleStripeSuccess = useCallback(
        async (paymentIntentId: string) => {
            if (!datosReserva) {
                return;
            }

            try {
                await reservasService.confirmarPagoStripe({
                    reserva_id: datosReserva.id,
                    codigo_reserva: datosReserva.codigoReserva,
                    payment_intent_id: paymentIntentId,
                });

                setPagoCompletado(true);
            } catch (err: unknown) {
                setErrorIntento(
                    err instanceof Error
                        ? err.message
                        : 'Error al registrar la confirmación.',
                );
            }
        },
        [datosReserva],
    );

    return {
        stripeData,
        cargandoIntento,
        errorIntento,
        pagoCompletado,
        handleStripeSuccess,
    };
};

export default usePagoReserva;
