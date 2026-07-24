import MisReservasInicio from '@/modules/home/components/MisReservasInicio';
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
    estado: number;
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
    items?: ReservaDetalleData[];
}
interface ReservaDetalleData {
    id: number;
    reservable_id: number;
    tipo: number;
    tipo_label: string;
    nombre: string;
    estado: number;
    estado_label: string;
    fecha_inicio: string;
    fecha_fin?: string;
    cantidad: number;
    adultos: number;
    ninos: number;
    subtotal: number;
    huespedes: AcompananteItem[];
}
interface MisReservasProps {
    reservas?: ReservaClienteData[];
    codigoBusqueda?: string;
}
const MisReservas = ({ reservas = [] }: MisReservasProps) => {
    const reservasMapped = reservas.map((r) => ({
        id: r.id,
        codigo_reserva: r.codigo_reserva,
        nombre_cliente: r.nombre_cliente,
        email_cliente: null,
        telefono_cliente: null,
        tipo_reserva: 1,
        tipo_reserva_label: r.tipo_reserva_label,
        estado: r.estado,
        estado_label: r.estado_label,
        estado_color: r.estado_color,
        adultos: r.adultos,
        ninos: r.ninos,
        total: String(r.total),
        fecha_check_in: r.fecha_check_in,
        fecha_check_out: r.fecha_check_out ?? null,
        hora_reserva: r.hora_reserva ?? null,
        detalles: r.detalles,
        huespedes_count: (r.acompanantes?.length ?? 0) + 1,
        acompanantes: r.acompanantes,
    }));

    return <MisReservasInicio reservas={reservasMapped} />;
};
export default MisReservas;
