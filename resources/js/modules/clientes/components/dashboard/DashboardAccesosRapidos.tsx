import {
    CalendarDays,
    CalendarPlus,
    ChefHat,
    Shield,
    ShoppingBag,
    Users,
    UtensilsCrossed,
} from 'lucide-react';
import type {
    EstadisticasHuesped,
    PortalReservaResumen,
} from '@/modules/clientes/types';
import { AccesoRapidoCard } from './AccesoRapidoCard';

interface DashboardAccesosRapidosProps {
    tieneReservas: boolean;
    estadisticas: EstadisticasHuesped;
    estanciaActiva?: PortalReservaResumen | null;
}

export const DashboardAccesosRapidos = ({
    tieneReservas,
    estadisticas,
    estanciaActiva,
}: DashboardAccesosRapidosProps) => {
    return (
        <div>
            <h3 className="mb-4 text-lg font-black text-foreground">
                {tieneReservas
                    ? 'Gestión y Servicios del Huésped'
                    : 'Gestión y Servicios del Cliente'}
            </h3>
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {tieneReservas ? (
                    <>
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

                        {estanciaActiva ? (
                            <>
                                <AccesoRapidoCard
                                    titulo="Servicios a la Habitación"
                                    descripcion="Pide alimentos, bebidas, lavandería o spa a tu suite."
                                    href={`/portal/reservas/${estanciaActiva.id}/servicios`}
                                    icono={UtensilsCrossed}
                                />
                                <AccesoRapidoCard
                                    titulo="Check-In y Huéspedes"
                                    descripcion="Registra a tus acompañantes para un ingreso rápido."
                                    href={`/portal/reservas/${estanciaActiva.id}/acompanantes`}
                                    icono={Users}
                                />
                            </>
                        ) : (
                            <>
                                <AccesoRapidoCard
                                    titulo="Restaurante & Delivery"
                                    descripcion="Pide comida a la carta o solicita entrega a domicilio."
                                    href="/restaurante"
                                    icono={UtensilsCrossed}
                                />
                                <AccesoRapidoCard
                                    titulo="Mis Pedidos"
                                    descripcion="Revisa el estado de tus comandas y pedidos."
                                    href="/portal/pedidos"
                                    icono={ChefHat}
                                    badge={`${estadisticas.pedidos_activos || 0} en cocina`}
                                />
                            </>
                        )}
                    </>
                ) : (
                    <>
                        <AccesoRapidoCard
                            titulo="Pedir en Restaurante"
                            descripcion="Platillos gourmet, cortes y comida típica a domicilio en Estelí."
                            href="/restaurante"
                            icono={ShoppingBag}
                            badge="Entrega Express"
                        />

                        <AccesoRapidoCard
                            titulo="Mis Pedidos y Comandas"
                            descripcion="Consulta el estado de preparación y tus pedidos anteriores."
                            href="/portal/pedidos"
                            icono={ChefHat}
                            badge={`${estadisticas.pedidos_activos || 0} activos`}
                        />

                        <AccesoRapidoCard
                            titulo="Reservar Suite / Hotel"
                            descripcion="Descubre nuestras suites coloniales con desayuno buffet."
                            href="/habitaciones"
                            icono={CalendarPlus}
                            badge="Hospedaje"
                        />

                        <AccesoRapidoCard
                            titulo="Mi Cuenta y Perfil"
                            descripcion="Actualiza tus datos de contacto, teléfonos y preferencias."
                            href="/portal/perfil"
                            icono={Shield}
                        />
                    </>
                )}
            </div>
        </div>
    );
};

export default DashboardAccesosRapidos;
