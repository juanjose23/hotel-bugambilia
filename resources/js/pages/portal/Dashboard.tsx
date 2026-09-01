import { Head, Link } from '@inertiajs/react';
import {
    CalendarDays,
    CalendarPlus,
    UtensilsCrossed,
    Users,
    Shield,
} from 'lucide-react';
import { AccesoRapidoCard } from '@/modules/clientes/components/dashboard/AccesoRapidoCard';
import { EstanciaActivaBanner } from '@/modules/clientes/components/dashboard/EstanciaActivaBanner';
import { HistorialResumen } from '@/modules/clientes/components/dashboard/HistorialResumen';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import type {
    ClienteProfile,
    PortalReservaResumen,
    EstadisticasHuesped,
} from '@/modules/clientes/types';

interface DashboardProps {
    cliente: ClienteProfile;
    estancia_activa?: PortalReservaResumen | null;
    reservas_activas: PortalReservaResumen[];
    historial_reservas: PortalReservaResumen[];
    estadisticas: EstadisticasHuesped;
}

export const Dashboard = ({
    cliente,
    estancia_activa,
    historial_reservas,
    estadisticas,
}: DashboardProps) => {
    return (
        <PortalLayout cliente={cliente}>
            <Head>
                <title>Portal de Huéspedes — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Panel de administración y gestión de estancias para huéspedes de Hotel Bugambilias Estelí."
                />
            </Head>

            <div className="mx-auto max-w-6xl space-y-8 p-5 sm:p-8 lg:p-10">
                {/* Saludo y bienvenida */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="text-xs font-black tracking-wider text-primary uppercase">
                                Panel de Huésped
                            </span>
                            <span>·</span>
                            <span className="text-xs text-muted-foreground">
                                Hotel Bugambilias Estelí
                            </span>
                        </div>
                        <h1 className="mt-1 text-2xl font-black text-foreground sm:text-3xl">
                            ¡Bienvenido, {cliente.nombre}!
                        </h1>
                        <p className="mt-0.5 text-sm text-muted-foreground">
                            Administra tus reservaciones, solicita servicios a
                            la habitación y gestiona tu estancia.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Link
                            href="/habitaciones"
                            className="hidden items-center gap-2 rounded-2xl bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm shadow-primary/20 hover:bg-primary/90 sm:inline-flex"
                        >
                            <CalendarPlus className="size-4" />
                            <span>Reservar Habitación</span>
                        </Link>
                        <div className="rounded-2xl border border-border/70 bg-card px-4 py-2.5 shadow-xs">
                            <span className="block text-[11px] font-bold text-muted-foreground">
                                Estancias Registradas
                            </span>
                            <span className="text-lg font-black text-foreground">
                                {estadisticas.total_reservas}
                            </span>
                        </div>
                        <div className="rounded-2xl border border-border/70 bg-card px-4 py-2.5 shadow-xs">
                            <span className="block text-[11px] font-bold text-muted-foreground">
                                Reservas Activas
                            </span>
                            <span className="text-lg font-black text-primary">
                                {estadisticas.activas}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Banner de Estancia Activa o Próxima */}
                {estancia_activa ? (
                    <EstanciaActivaBanner reserva={estancia_activa} />
                ) : (
                    <div className="rounded-3xl border border-dashed border-border/80 bg-secondary/20 p-8 text-center sm:p-12">
                        <CalendarDays className="mx-auto size-12 text-muted-foreground/60" />
                        <h3 className="mt-3 text-lg font-bold text-foreground">
                            No tienes ninguna estancia activa en este momento
                        </h3>
                        <p className="mx-auto mt-1 max-w-md text-sm text-muted-foreground">
                            Explora nuestras suites coloniales y reserva tu
                            próxima escapada de descanso.
                        </p>
                        <div className="mt-5">
                            <Link
                                href="/habitaciones"
                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-primary/20 hover:bg-primary/90"
                            >
                                <CalendarPlus className="size-4" />
                                <span>Reservar Habitación</span>
                            </Link>
                        </div>
                    </div>
                )}

                {/* Accesos Rápidos */}
                <div>
                    <h3 className="mb-4 text-lg font-black text-foreground">
                        Gestión y Servicios del Huésped
                    </h3>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <AccesoRapidoCard
                            titulo="Reservar Suite"
                            descripcion="Explora disponibilidad y reserva una nueva habitación en el hotel."
                            href="/habitaciones"
                            icono={CalendarPlus}
                            badge="Disponible"
                        />

                        <AccesoRapidoCard
                            titulo="Mis Reservaciones"
                            descripcion="Consulta todas tus reservas activas, confirmadas e historial."
                            href="/portal/reservas"
                            icono={CalendarDays}
                            badge={`${estadisticas.activas} activas`}
                        />

                        {estancia_activa ? (
                            <>
                                <AccesoRapidoCard
                                    titulo="Servicios a la Habitación"
                                    descripcion="Pide alimentos, bebidas, lavandería o spa a tu suite."
                                    href={`/portal/reservas/${estancia_activa.id}/servicios`}
                                    icono={UtensilsCrossed}
                                />
                                <AccesoRapidoCard
                                    titulo="Check-In y Huéspedes"
                                    descripcion="Registra a tus acompañantes para un ingreso rápido."
                                    href={`/portal/reservas/${estancia_activa.id}/acompanantes`}
                                    icono={Users}
                                />
                            </>
                        ) : (
                            <>
                                <AccesoRapidoCard
                                    titulo="Gastronomía & Restaurante"
                                    descripcion="Conoce los platillos y menú gourmet del hotel."
                                    href="/restaurante"
                                    icono={UtensilsCrossed}
                                />
                                <AccesoRapidoCard
                                    titulo="Mi Cuenta y Perfil"
                                    descripcion="Actualiza tus datos de contacto y preferencias."
                                    href="/portal/perfil"
                                    icono={Shield}
                                />
                            </>
                        )}
                    </div>
                </div>

                {/* Historial Reciente */}
                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h3 className="text-lg font-black text-foreground">
                            Historial Reciente de Estancias
                        </h3>
                        {historial_reservas.length > 0 && (
                            <Link
                                href="/portal/reservas"
                                className="text-xs font-bold text-primary hover:underline"
                            >
                                Ver todas
                            </Link>
                        )}
                    </div>
                    <HistorialResumen reservas={historial_reservas} />
                </div>
            </div>
        </PortalLayout>
    );
};

// Asignación explícita para evitar doble layout
Dashboard.layout = (page: React.ReactNode) => page;

export default Dashboard;
