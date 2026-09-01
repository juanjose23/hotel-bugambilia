import type { CrearReservaFormValues } from '../schemas/crearReservaSchema';
import type { ReservaCreadaResponse, StripePaymentData } from '../types';

export interface CrearReservaApiResponse {
    reserva: ReservaCreadaResponse;
    requiere_pago_stripe?: boolean;
    stripe_pago?: StripePaymentData;
    message?: string;
}

export interface ConfirmarPagoApiResponse {
    success: boolean;
    reserva: ReservaCreadaResponse;
    message?: string;
}

const obtenerCsrfToken = (): string => {
    return (
        (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)
            ?.content || ''
    );
};

export const reservasService = {
    async crearReserva(
        payload: CrearReservaFormValues,
    ): Promise<CrearReservaApiResponse> {
        const response = await fetch('/reservas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': obtenerCsrfToken(),
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Error al procesar la reserva.');
        }

        return result;
    },

    async obtenerIntentoPago(payload: {
        reserva_id: number;
        codigo_reserva: string;
    }): Promise<StripePaymentData> {
        const response = await fetch('/pagos/stripe/reservas/intento', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': obtenerCsrfToken(),
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Error al conectar con Stripe.');
        }

        return data;
    },

    async confirmarPagoStripe(payload: {
        reserva_id: number;
        codigo_reserva: string;
        payment_intent_id: string;
    }): Promise<ConfirmarPagoApiResponse> {
        const response = await fetch('/pagos/stripe/reservas/confirmar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': obtenerCsrfToken(),
            },
            body: JSON.stringify(payload),
        });

        const res = await response.json();

        if (!response.ok) {
            throw new Error(
                res.message || 'Error al confirmar el pago en el servidor.',
            );
        }

        return res;
    },
};

export default reservasService;
