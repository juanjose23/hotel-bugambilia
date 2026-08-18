import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { obtenerEtiquetaEstadoReserva } from './calculosReserva';

export function mapearReservaClienteDomain(
    dto: Record<string, unknown>,
): ReservaClienteDomain {
    const id = Number(dto.id || 0);
    const estado = Number(dto.estado ?? 1);
    const total = Number(dto.total ?? 0);
    const detalles = String(dto.detalles || 'Reserva de Estancia');
    const fechaCheckIn = String(dto.fecha_check_in || '');
    const acompanantes = Array.isArray(dto.acompanantes) ? dto.acompanantes : [];
    const estadoInfo = obtenerEtiquetaEstadoReserva(estado);

    return {
        id,
        codigo_reserva: String(dto.codigo_reserva || `RES-${id}`),
        nombre_cliente: String(dto.nombre_cliente || 'Cliente General'),
        email_cliente: dto.email_cliente ? String(dto.email_cliente) : null,
        telefono_cliente: dto.telefono_cliente ? String(dto.telefono_cliente) : null,
        tipo_reserva: String(dto.tipo_reserva ?? 1),
        tipo_reserva_label: String(dto.tipo_reserva_label || 'Estancia General'),
        estado,
        estado_label: String(dto.estado_label || estadoInfo.label),
        estado_color: String(dto.estado_color || 'bg-primary'),
        fecha_check_in: fechaCheckIn,
        fecha_check_out: dto.fecha_check_out ? String(dto.fecha_check_out) : null,
        hora_reserva: dto.hora_reserva ? String(dto.hora_reserva) : null,
        adultos: Number(dto.adultos ?? 1),
        ninos: Number(dto.ninos ?? 0),
        total,
        detalles,
        notas: dto.notas ? String(dto.notas) : undefined,
        huespedes_count: acompanantes.length + 1,
        acompanantes: acompanantes as ReservaClienteDomain['acompanantes'],
        activos_habitacion: (Array.isArray(dto.activos_habitacion)
            ? dto.activos_habitacion
            : []) as ReservaClienteDomain['activos_habitacion'],
        servicios_habitacion: (Array.isArray(dto.servicios_habitacion)
            ? dto.servicios_habitacion
            : []) as ReservaClienteDomain['servicios_habitacion'],
        estado_cuenta: (dto.estado_cuenta as ReservaClienteDomain['estado_cuenta']) || {
            cargos: [
                {
                    id: 1,
                    fecha: fechaCheckIn || 'Hoy',
                    descripcion: `Estancia Base — ${detalles}`,
                    monto: total,
                    categoria: 'Hospedaje',
                },
            ],
            subtotal: Math.round(total / 1.15),
            impuestos: Math.round(total - total / 1.15),
            total,
            total_pagado: total,
            saldo_pendiente: 0,
        },
    };
}
