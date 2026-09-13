import type { FormPedidoDelivery } from '../schemas/pedidoDeliverySchema';
import type { FormReservaMesa } from '../schemas/reservaMesaSchema';

export interface MesaSugerida {
    id: number;
    nombre: string;
    capacidad: number;
    ubicacion?: string;
    zona?: string;
}

export interface DisponibilidadMesasResponse {
    fecha: string;
    hora: string;
    duracion_horas: number;
    comensales: number;
    mesa_asignada?: MesaSugerida | null;
    requiere_union?: boolean;
    zona_asignada?: string;
    mesas_disponibles: Array<{
        id: number;
        nombre: string;
        capacidad: number;
        ubicacion: string;
    }>;
    mesas_sugeridas_union: MesaSugerida[];
    capacidad_total_disponible: number;
    horarios_disponibles: Array<{
        hora: string;
        turno: string;
        disponible: boolean;
    }>;
}

export interface RespuestaReservaMesaBackend {
    success: boolean;
    reserva_id: number;
    codigo_reserva: string;
    fecha: string;
    hora: string;
    mesa_nombre: string;
    mesas_unidas: string[];
    requiere_pago_stripe: boolean;
    stripe_pago: Record<string, unknown> | null;
    whatsapp_url: string;
    mensaje: string;
}

export interface StripePedidoPayment {
    client_secret: string;
    publishable_key: string;
    monto: number;
}

export interface RespuestaPedidoBackend {
    success: boolean;
    pedido_id: number;
    codigo: string;
    subtotal: number;
    costo_delivery: number;
    total: number;
    moneda: string;
    metodo_pago: string;
    whatsapp_url: string;
    stripe_pago: StripePedidoPayment | null;
    mensaje: string;
}

const obtenerCsrfToken = (): string => {
    return (
        (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)
            ?.content || ''
    );
};

const extraerMensajeError = (
    resultado: { message?: string; errors?: Record<string, string[]> },
    fallback: string,
): string => {
    if (resultado.message) {
        return resultado.message;
    }

    if (resultado.errors) {
        const primerError = Object.values(resultado.errors)[0];

        if (primerError?.[0]) {
            return primerError[0];
        }
    }

    return fallback;
};

const peticionJson = async <T>(
    url: string,
    opciones: RequestInit = {},
): Promise<T> => {
    const response = await fetch(url, {
        ...opciones,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': obtenerCsrfToken(),
            ...(opciones.headers ?? {}),
        },
    });

    const resultado = (await response.json()) as T & {
        success?: boolean;
        message?: string;
        errors?: Record<string, string[]>;
    };

    if (!response.ok || resultado.success === false) {
        throw new Error(
            extraerMensajeError(
                resultado,
                'Ocurrió un error al procesar la solicitud.',
            ),
        );
    }

    return resultado;
};

export const restauranteService = {
    async consultarMesasDisponibles(parametros: {
        fecha: string;
        hora: string;
        duracion_horas: number;
        comensales: number;
    }): Promise<DisponibilidadMesasResponse> {
        const params = new URLSearchParams({
            fecha: parametros.fecha,
            hora: parametros.hora,
            duracion_horas: String(parametros.duracion_horas),
            comensales: String(parametros.comensales),
        });

        const response = await fetch(
            `/restaurante/mesas-disponibles?${params.toString()}`,
            {
                headers: { Accept: 'application/json' },
            },
        );

        const resultado =
            (await response.json()) as DisponibilidadMesasResponse & {
                message?: string;
            };

        if (!response.ok) {
            throw new Error(
                resultado.message ||
                    'No se pudo consultar la disponibilidad de mesas.',
            );
        }

        return resultado;
    },

    async crearReservaMesa(
        datos: FormReservaMesa,
    ): Promise<RespuestaReservaMesaBackend> {
        return peticionJson('/restaurante/reservar-mesa', {
            method: 'POST',
            body: JSON.stringify({
                nombre_cliente: datos.nombre_cliente,
                telefono_cliente: datos.telefono_cliente,
                email_cliente: datos.email_cliente || null,
                fecha: datos.fecha,
                hora: datos.hora,
                duracion_horas: Number(datos.duracion_horas || 1),
                adultos: Number(datos.adultos || 2),
                espacio_id: datos.espacio_id || null,
                tipo_pago_reserva: 'sin_pago',
                notas: datos.notas || null,
            }),
        });
    },

    async crearPedido(datos: {
        infoEntrega: FormPedidoDelivery;
        items: Array<{
            plato_id: number;
            cantidad: number;
            observaciones: string | null;
        }>;
    }): Promise<RespuestaPedidoBackend> {
        const { infoEntrega, items } = datos;

        return peticionJson('/restaurante/pedido', {
            method: 'POST',
            body: JSON.stringify({
                nombre: infoEntrega.nombre,
                email: infoEntrega.email,
                telefono: infoEntrega.telefono,
                departamento: infoEntrega.departamento,
                municipio: infoEntrega.municipio,
                direccion: infoEntrega.direccion,
                metodo_pago: infoEntrega.metodoPago,
                monto_paga_con: infoEntrega.montoPagaCon || null,
                notas: infoEntrega.notas || null,
                items,
            }),
        });
    },

    async confirmarPagoStripePedido(datos: {
        pedido_id: number;
        payment_intent_id: string;
    }): Promise<{ success: boolean; whatsapp_url?: string; message?: string }> {
        return peticionJson('/restaurante/confirmar-pago-stripe', {
            method: 'POST',
            body: JSON.stringify(datos),
        });
    },
};

export default restauranteService;
