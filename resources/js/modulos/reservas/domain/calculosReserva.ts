export function calcularNochesReserva(
    checkIn?: string,
    checkOut?: string,
): number {
    if (!checkIn || !checkOut) {
        return 1;
    }

    const inicio = new Date(checkIn);
    const fin = new Date(checkOut);
    const diferenciaMs = fin.getTime() - inicio.getTime();
    const noches = Math.ceil(diferenciaMs / (1000 * 3600 * 24));

    return noches > 0 ? noches : 1;
}

export function calcularMontoGarantiaReserva(
    subtotal: number,
    tipoPago: 'sin_pago' | 'abono_50' | 'pago_completo' = 'abono_50',
): number {
    if (tipoPago === 'pago_completo') {
        return subtotal;
    }

    if (tipoPago === 'abono_50') {
        return subtotal * 0.5;
    }

    return 0;
}

export function obtenerEtiquetaEstadoReserva(estado: number): {
    label: string;
    variant: 'default' | 'outline' | 'destructive' | 'secondary';
} {
    switch (estado) {
        case 1:
            return { label: 'Pendiente', variant: 'outline' };
        case 2:
            return { label: 'Confirmada', variant: 'default' };
        case 3:
            return { label: 'Completada', variant: 'secondary' };
        case 4:
            return { label: 'Cancelada', variant: 'destructive' };
        default:
            return { label: 'Registrada', variant: 'outline' };
    }
}
