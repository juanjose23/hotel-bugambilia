import MyBookings from '@/modules/home/components/MyBookings';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

interface AcompananteItem {
    nombre: string;
    identificacion?: string;
    tipo?: string;
}

interface ReservaClienteData {
    id: number;
    codigo_reserva: string;
    nombre_cliente: string;
    tipo_reserva: string;
    tipo_reserva_label: string;
    estado: string;
    estado_label: string;
    estado_color: string;
    fecha_check_in: string;
    fecha_check_out?: string;
    hora_reserva?: string;
    adultos: number;
    ninos: number;
    acompanantes?: AcompananteItem[];
    total: number;
    detalles: string;
    notas?: string;
}

interface MisReservasProps {
    reservas?: ReservaClienteData[];
    codigoBusqueda?: string;
}

export default function MisReservas({
    reservas = [],
    codigoBusqueda = '',
}: MisReservasProps) {
    return (
        <LayoutPublico>
            <MyBookings
                reservasProps={reservas}
                codigoBusqueda={codigoBusqueda}
            />
        </LayoutPublico>
    );
}
