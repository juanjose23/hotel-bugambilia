import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { obtenerEtiquetaEstadoReserva } from './calculosReserva';

export function mapearReservaClienteDomain(dto: any): ReservaClienteDomain {
    const estadoInfo = obtenerEtiquetaEstadoReserva(dto.estado ?? 1);

    return {
        id: dto.id,
        codigo_reserva: dto.codigo_reserva || `RES-${dto.id}`,
        nombre_cliente: dto.nombre_cliente || 'Cliente General',
        email_cliente: dto.email_cliente || null,
        telefono_cliente: dto.telefono_cliente || null,
        tipo_reserva: String(dto.tipo_reserva ?? 1),
        tipo_reserva_label: dto.tipo_reserva_label || 'Estancia General',
        estado: dto.estado ?? 1,
        estado_label: dto.estado_label || estadoInfo.label,
        estado_color: dto.estado_color || 'bg-primary',
        fecha_check_in: dto.fecha_check_in || '',
        fecha_check_out: dto.fecha_check_out || null,
        hora_reserva: dto.hora_reserva || null,
        adultos: dto.adultos ?? 1,
        ninos: dto.ninos ?? 0,
        total: dto.total ?? 0,
        detalles: dto.detalles || 'Reserva de Estancia',
        notas: dto.notas || undefined,
        huespedes_count: (dto.acompanantes?.length ?? 0) + 1,
        acompanantes: dto.acompanantes || [],
    };
}
