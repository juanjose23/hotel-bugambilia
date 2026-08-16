import type { DatosReserva, ServicioExtra } from '@/modulos/compartido/types';
import type { DatosContactoPago } from '@/modulos/pagos/interfaces/pago';
interface OpcionesMensajeWhatsApp {
    datosContacto: DatosContactoPago;
    datosReserva: DatosReserva;
    serviciosExtras: ServicioExtra[];
    serviciosSeleccionados: string[];
    total: number;
}
export const construirMensajeWhatsApp = ({
    datosContacto,
    datosReserva,
    serviciosExtras,
    serviciosSeleccionados,
    total,
}: OpcionesMensajeWhatsApp): string => {
    const servicios = serviciosSeleccionados
        .map((id) => serviciosExtras.find((servicio) => servicio.id === id))
        .filter((servicio): servicio is ServicioExtra => Boolean(servicio));
    const lineas = [
        'Hola, me gustaría reservar:',
        '',
        `*Habitación:* ${datosReserva.habitacion}`,
        `*Ubicación:* ${datosReserva.ubicacion}`,
        `*Entrada:* ${datosReserva.fechaEntrada}`,
        `*Salida:* ${datosReserva.fechaSalida}`,
        `*Noches:* ${datosReserva.noches}`,
        `*Huéspedes:* ${datosReserva.huespedes}`,
    ];
    const nombreCompleto =
        `${datosContacto.nombre} ${datosContacto.apellido}`.trim();

    if (nombreCompleto) {
        lineas.push('', `*Nombre:* ${nombreCompleto}`);
    }

    if (datosContacto.correo) {
        lineas.push(`*Correo:* ${datosContacto.correo}`);
    }

    if (datosContacto.telefono) {
        lineas.push(`*Teléfono:* ${datosContacto.telefono}`);
    }

    if (servicios.length > 0) {
        lineas.push('', '*Servicios adicionales:*');
        servicios.forEach((servicio) => {
            lineas.push(`- ${servicio.nombre} ($${servicio.precio})`);
        });
    }

    if (datosContacto.peticiones) {
        lineas.push('', `*Peticiones especiales:* ${datosContacto.peticiones}`);
    }

    lineas.push('', `*Total:* $${total.toFixed(2)} USD`);

    return encodeURIComponent(lineas.join('\n'));
};
