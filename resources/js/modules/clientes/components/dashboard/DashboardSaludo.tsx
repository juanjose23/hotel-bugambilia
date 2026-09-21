import { Link } from '@inertiajs/react';
import {
    BedDouble,
    UtensilsCrossed,
    CalendarPlus,
    ShoppingBag,
} from 'lucide-react';
import type { EstadisticasHuesped } from '@/modules/clientes/types';

interface DashboardSaludoProps {
    nombre: string;
    tieneReservas: boolean;
    estadisticas: EstadisticasHuesped;
}

export const DashboardSaludo = ({
    nombre,
    tieneReservas,
    estadisticas,
}: DashboardSaludoProps) => {
    const totalHabitaciones =
        estadisticas.total_habitaciones ?? estadisticas.total_reservas;
    const totalRestaurante = estadisticas.total_restaurante ?? 0;

    return (
        <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <div className="flex items-center gap-2">
                    <span className="text-xs font-black tracking-wider text-primary uppercase">
                        {tieneReservas
                            ? 'Panel del Huésped'
                            : 'Panel del Cliente'}
                    </span>
                    <span>·</span>
                    <span className="text-xs text-muted-foreground">
                        Hotel & Restaurante Bugambilias Estelí
                    </span>
                </div>
                <h1 className="mt-1 text-2xl font-black text-foreground sm:text-3xl">
                    ¡Bienvenido, {nombre}!
                </h1>
                <p className="mt-0.5 text-sm text-muted-foreground">
                    {tieneReservas
                        ? 'Gestiona tus reservas de suites, mesas de restaurante y servicios de forma ordenada.'
                        : 'Seguimiento de pedidos de restaurante a domicilio, comandas y reservación de mesas.'}
                </p>
            </div>

            <div className="flex flex-wrap items-center gap-2 sm:gap-3">
                {tieneReservas ? (
                    <>
                        <Link
                            href="/habitaciones"
                            className="hidden cursor-pointer items-center gap-2 rounded-2xl bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm shadow-primary/20 hover:bg-primary/90 sm:inline-flex"
                        >
                            <CalendarPlus className="size-4" />
                            <span>Nueva Reserva</span>
                        </Link>

                        <div className="rounded-2xl border border-border/70 bg-card px-3.5 py-2 shadow-xs">
                            <div className="flex items-center gap-1.5 text-[11px] font-bold text-muted-foreground">
                                <BedDouble className="size-3.5 text-primary" />
                                <span>Suites</span>
                            </div>
                            <span className="text-base font-black text-foreground">
                                {totalHabitaciones}
                            </span>
                        </div>

                        {totalRestaurante > 0 && (
                            <div className="rounded-2xl border border-border/70 bg-card px-3.5 py-2 shadow-xs">
                                <div className="flex items-center gap-1.5 text-[11px] font-bold text-muted-foreground">
                                    <UtensilsCrossed className="size-3.5 text-amber-500" />
                                    <span>Mesas</span>
                                </div>
                                <span className="text-base font-black text-amber-600 dark:text-amber-400">
                                    {totalRestaurante}
                                </span>
                            </div>
                        )}

                        <div className="rounded-2xl border border-border/70 bg-card px-3.5 py-2 shadow-xs">
                            <span className="block text-[11px] font-bold text-muted-foreground">
                                Activas
                            </span>
                            <span className="text-base font-black text-primary">
                                {estadisticas.activas}
                            </span>
                        </div>
                    </>
                ) : (
                    <>
                        <Link
                            href="/restaurante"
                            className="hidden cursor-pointer items-center gap-2 rounded-2xl bg-primary px-4 py-2 text-xs font-bold text-white shadow-sm shadow-primary/20 hover:bg-primary/90 sm:inline-flex"
                        >
                            <ShoppingBag className="size-4" />
                            <span>Pedir en Restaurante</span>
                        </Link>
                        <div className="rounded-2xl border border-border/70 bg-card px-4 py-2.5 shadow-xs">
                            <span className="block text-[11px] font-bold text-muted-foreground">
                                Total Pedidos
                            </span>
                            <span className="text-lg font-black text-foreground">
                                {estadisticas.total_pedidos || 0}
                            </span>
                        </div>
                        <div className="rounded-2xl border border-border/70 bg-card px-4 py-2.5 shadow-xs">
                            <span className="block text-[11px] font-bold text-muted-foreground">
                                En Cocina
                            </span>
                            <span className="text-lg font-black text-primary">
                                {estadisticas.pedidos_activos || 0}
                            </span>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default DashboardSaludo;
