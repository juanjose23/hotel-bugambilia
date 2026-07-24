import { ProcesoPago } from '@/modules/payment/components/ProcesoPago';
import type { DatosReserva, ServicioExtra } from '@/modules/shared/types';
interface PropiedadesPago {
    datosReserva: DatosReserva;
    serviciosExtras?: ServicioExtra[];
}
export const PaginaPago = ({
    datosReserva,
    serviciosExtras,
}: PropiedadesPago) => {
    return (
        <>
            <ProcesoPago
                datosReserva={datosReserva}
                serviciosExtras={serviciosExtras}
            />
        </>
    );
};
export default PaginaPago;
