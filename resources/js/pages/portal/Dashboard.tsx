import { Head, Link } from '@inertiajs/react';
import { CalendarDays, CalendarPlus } from 'lucide-react';
import { DashboardAccesosRapidos } from '@/modules/clientes/components/dashboard/DashboardAccesosRapidos';
import { DashboardSaludo } from '@/modules/clientes/components/dashboard/DashboardSaludo';
import { EstanciaActivaBanner } from '@/modules/clientes/components/dashboard/EstanciaActivaBanner';
import { HistorialResumen } from '@/modules/clientes/components/dashboard/HistorialResumen';
import { PedidosRestauranteBanner } from '@/modules/clientes/components/dashboard/PedidosRestauranteBanner';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import type {
    ClienteProfile,
    PortalReservaResumen,
    PortalPedidoResumen,
    EstadisticasHuesped,
} from '@/modules/clientes/types';

interface DashboardProps {
    cliente: ClienteProfile;
    estancia_activa?: PortalReservaResumen | null;
    reservas_activas: PortalReservaResumen[];
    historial_reservas: PortalReservaResumen[];
    pedidos_activos?: PortalPedidoResumen[];
    historial_pedidos?: PortalPedidoResumen[];
    estadisticas: EstadisticasHuesped;
}

export const Dashboard = ({
    cliente,
    estancia_activa,
    historial_reservas = [],
    pedidos_activos = [],
    historial_pedidos = [],
    estadisticas,
}: DashboardProps) => {
    const tieneReservas = estadisticas.total_reservas > 0;

    return (
        <PortalLayout cliente={cliente}>
            <Head>
                <title>Portal de Huéspedes — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Panel de administración y gestión de estancias y pedidos para huéspedes de Hotel Bugambilias Estelí."
                />
            </Head>

            <div className="mx-auto max-w-6xl space-y-8 p-5 sm:p-8 lg:p-10">
                <DashboardSaludo
                    nombre={cliente.nombre}
                    tieneReservas={tieneReservas}
                    estadisticas={estadisticas}
                />

                {tieneReservas &&
                    (estancia_activa ? (
                        <EstanciaActivaBanner reserva={estancia_activa} />
                    ) : (
                        <div className="rounded-3xl border border-dashed border-border/80 bg-secondary/20 p-8 text-center sm:p-12">
                            <CalendarDays className="mx-auto size-12 text-muted-foreground/60" />
                            <h3 className="mt-3 text-lg font-bold text-foreground">
                                No tienes ninguna estancia activa en este
                                momento
                            </h3>
                            <p className="mx-auto mt-1 max-w-md text-sm text-muted-foreground">
                                Explora nuestras suites coloniales y reserva tu
                                próxima escapada de descanso.
                            </p>
                            <div className="mt-5">
                                <Link
                                    href="/habitaciones"
                                    className="inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-primary/20 hover:bg-primary/90"
                                >
                                    <CalendarPlus className="size-4" />
                                    <span>Reservar Habitación</span>
                                </Link>
                            </div>
                        </div>
                    ))}

                {(!tieneReservas || pedidos_activos.length > 0) && (
                    <PedidosRestauranteBanner
                        pedidosActivos={pedidos_activos}
                        historialPedidos={historial_pedidos}
                    />
                )}

                <DashboardAccesosRapidos
                    tieneReservas={tieneReservas}
                    estadisticas={estadisticas}
                    estanciaActiva={estancia_activa}
                />

                {tieneReservas && historial_reservas.length > 0 && (
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-black text-foreground">
                                Historial Reciente de Estancias
                            </h3>
                            <Link
                                href="/portal/reservas"
                                className="cursor-pointer text-xs font-bold text-primary hover:underline"
                            >
                                Ver todas
                            </Link>
                        </div>
                        <HistorialResumen reservas={historial_reservas} />
                    </div>
                )}
            </div>
        </PortalLayout>
    );
};

Dashboard.layout = (page: React.ReactNode) => page;

export default Dashboard;
