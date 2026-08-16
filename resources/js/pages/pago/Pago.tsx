import type { DatosReserva, ServicioExtra } from '@/modulos/compartido/types';
import { ProcesoPago } from '@/modulos/pagos/componentes/ProcesoPago';
interface PropiedadesPago {
    datosReserva?: DatosReserva | null;
    serviciosExtras?: ServicioExtra[];
}
export const PaginaPago = ({
    datosReserva,
    serviciosExtras,
}: PropiedadesPago) => {
    if (!datosReserva) {
        return (
            <main className="flex min-h-screen items-center justify-center bg-gray-50 px-4">
                <div className="max-w-md rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-xl">
                    <h1 className="text-2xl font-black tracking-tight text-gray-900">
                        Selecciona una reserva
                    </h1>
                    <p className="mt-3 text-sm font-medium text-gray-500">
                        Para pagar en linea, abre el enlace de pago desde una
                        reserva existente.
                    </p>
                </div>
            </main>
        );
    }

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
