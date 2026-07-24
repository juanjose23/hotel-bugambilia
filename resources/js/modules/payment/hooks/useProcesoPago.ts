import { useEffect, useState } from 'react';
import type {
    DatosContactoPago,
    MetodoPago,
} from '@/modules/payment/types/pago';
import { construirMensajeWhatsApp } from '@/modules/payment/utils/construirMensajeWhatsApp';
import type { DatosReserva, ServicioExtra } from '@/modules/shared/types';

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

export const useProcesoPago = ({
    datosReserva,
    serviciosExtras,
    telefonoHotel,
}: OpcionesProcesoPago) => {
    const [pasoActual, establecerPasoActual] = useState(1);
    const [metodoPago, establecerMetodoPago] = useState<MetodoPago>('tarjeta');
    const [serviciosSeleccionados, establecerServiciosSeleccionados] = useState<
        string[]
    >([]);
    const [datosContacto, establecerDatosContacto] =
        useState<DatosContactoPago>(DATOS_CONTACTO_INICIALES);

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

    return {
        pasoActual,
        metodoPago,
        serviciosSeleccionados,
        datosContacto,
        totalExtras,
        totalFinal,
        establecerMetodoPago,
        alternarServicio,
        actualizarDatoContacto,
        irAlPaso,
        retroceder,
        confirmarReserva,
    };
};
