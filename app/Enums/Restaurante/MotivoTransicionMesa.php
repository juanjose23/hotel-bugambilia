<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

enum MotivoTransicionMesa: string
{
    case Manual = 'manual';
    case AperturaPedido = 'apertura_pedido';
    case CierrePedido = 'cierre_pedido';
    case LlegadaReserva = 'llegada_reserva';
    case CancelacionReserva = 'cancelacion_reserva';
    case UnionMesas = 'union_mesas';
    case SeparacionMesas = 'separacion_mesas';
    case MovimientoCuenta = 'movimiento_cuenta';
    case LimpiezaIniciada = 'limpieza_iniciada';
    case LimpiezaCompletada = 'limpieza_completada';
    case Administracion = 'administracion';
}
