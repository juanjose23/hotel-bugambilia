import type {
    BeneficioClienteItem,
    ServicioAdicionalItem,
    PoliticaReserva,
} from '../types';

interface UseReservaCalculosProps {
    checkIn: string;
    checkOut: string;
    room: {
        precio?: number | string;
        precio_desde?: number | string;
        politicas?: PoliticaReserva[];
    };
    serviciosSeleccionados?: { servicio_id: number; cantidad: number }[];
    serviciosDisponibles?: ServicioAdicionalItem[];
    beneficiosCliente?: BeneficioClienteItem[];
    beneficioId?: number | string | null;
    canalPago?: string;
    tipoPago?: string;
}

const calcularNoches = (checkIn: string, checkOut: string): number => {
    if (!checkIn || !checkOut) {
        return 0;
    }

    const diff = Math.ceil(
        (new Date(checkOut).getTime() - new Date(checkIn).getTime()) /
            (1000 * 60 * 60 * 24),
    );

    return diff > 0 ? diff : 0;
};

const resolverPorcentajeAnticipo = (
    politicas: PoliticaReserva[] = [],
): number => {
    for (const pol of politicas) {
        if (
            Array.isArray(pol.penalizaciones) &&
            pol.penalizaciones.length > 0
        ) {
            const p = pol.penalizaciones[0]?.porcentaje;

            if (typeof p === 'number' && p > 0) {
                return p;
            }
        }

        if (typeof pol.porcentaje === 'number' && pol.porcentaje > 0) {
            return pol.porcentaje;
        }
    }

    return 50;
};

export const useReservaCalculos = ({
    checkIn,
    checkOut,
    room,
    serviciosSeleccionados = [],
    serviciosDisponibles = [],
    beneficiosCliente = [],
    beneficioId = null,
    canalPago = 'stripe',
    tipoPago = 'pago_completo',
}: UseReservaCalculosProps) => {
    // 1. Noches y Subtotal Suite
    const noches = calcularNoches(checkIn, checkOut);
    const precioNoche = Number(room.precio ?? room.precio_desde ?? 0);
    const subtotalHabitacion = noches * precioNoche;

    // 2. Subtotal Servicios
    const subtotalServicios = serviciosSeleccionados.reduce((acc, item) => {
        const servicio = serviciosDisponibles.find(
            (s) => s.id === item.servicio_id,
        );

        return acc + Number(servicio?.precio || 0) * item.cantidad;
    }, 0);

    const subtotalBruto = subtotalHabitacion + subtotalServicios;

    // 3. Beneficios y Descuentos
    const beneficioAplicado = beneficioId
        ? beneficiosCliente.find((b) => b.id === Number(beneficioId)) || null
        : null;

    let montoDescuento = 0;

    if (beneficioAplicado?.tipo === 'descuento_reserva') {
        montoDescuento = beneficioAplicado.es_porcentaje
            ? (subtotalBruto * Number(beneficioAplicado.valor)) / 100
            : Math.min(subtotalBruto, Number(beneficioAplicado.valor));
    }

    const totalNeto = Math.max(0, subtotalBruto - montoDescuento);

    // 4. Políticas de Anticipo
    const porcentajeAnticipoPolitica = resolverPorcentajeAnticipo(
        room.politicas,
    );

    let montoACobrarAhora = totalNeto;

    if (canalPago === 'sin_pago' || tipoPago === 'sin_pago') {
        montoACobrarAhora = 0;
    } else if (tipoPago === 'abono_50') {
        montoACobrarAhora = Number(
            ((totalNeto * porcentajeAnticipoPolitica) / 100).toFixed(2),
        );
    }

    return {
        noches,
        precioNoche,
        subtotalHabitacion,
        subtotalServicios,
        subtotalBruto,
        beneficioAplicado,
        montoDescuento,
        totalNeto,
        porcentajeAnticipoPolitica,
        montoACobrarAhora,
    };
};

export default useReservaCalculos;
