import { Head } from '@inertiajs/react';
import {
    BedDouble,
    Calendar,
    UtensilsCrossed,
    Sparkles,
    Layers,
} from 'lucide-react';
import { useState, useMemo } from 'react';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import { AccesoCodigoCard } from '@/modules/clientes/components/reservas/AccesoCodigoCard';
import { PortalReservaItem } from '@/modules/clientes/components/reservas/PortalReservaItem';
import type {
    ClienteProfile,
    PortalReservaResumen,
} from '@/modules/clientes/types';
import { useCancelarReserva } from '@/modules/reservas/hooks/useCancelarReserva';
import { Button } from '@/modules/shared/components/ui/button';

interface MisReservasPageProps {
    reservas_activas?: PortalReservaResumen[];
    historial_reservas?: PortalReservaResumen[];
    cliente?: ClienteProfile | null;
    codigoBusqueda?: string;
}

export const MisReservas = ({
    reservas_activas = [],
    historial_reservas = [],
    cliente = null,
    codigoBusqueda = '',
}: MisReservasPageProps) => {
    const [tabEstado, setTabEstado] = useState<'activas' | 'historial'>(
        'activas',
    );
    const [filtroTipo, setFiltroTipo] = useState<
        'todas' | 'habitacion' | 'restaurante' | 'servicio'
    >('todas');

    const { cancelarReserva } = useCancelarReserva();

    const listaBase =
        tabEstado === 'activas' ? reservas_activas : historial_reservas;

    // Conteo por categorías dentro de la pestaña actual
    const conteos = useMemo(() => {
        return {
            todas: listaBase.length,
            habitacion: listaBase.filter(
                (r) =>
                    r.es_habitacion ||
                    r.tipo_reserva === 'habitacion' ||
                    r.tipo_reserva === 'paquete',
            ).length,
            restaurante: listaBase.filter(
                (r) =>
                    r.es_restaurante ||
                    r.tipo_reserva === 'restaurante' ||
                    r.recurso.categoria.toLowerCase().includes('mesa') ||
                    r.recurso.categoria.toLowerCase().includes('restaurante'),
            ).length,
            servicio: listaBase.filter(
                (r) => r.es_servicio || r.tipo_reserva === 'servicio',
            ).length,
        };
    }, [listaBase]);

    // Filtrar según el tipo seleccionado
    const listaFiltrada = useMemo(() => {
        if (filtroTipo === 'todas') {
            return listaBase;
        }

        if (filtroTipo === 'habitacion') {
            return listaBase.filter(
                (r) =>
                    r.es_habitacion ||
                    r.tipo_reserva === 'habitacion' ||
                    r.tipo_reserva === 'paquete',
            );
        }

        if (filtroTipo === 'restaurante') {
            return listaBase.filter(
                (r) =>
                    r.es_restaurante ||
                    r.tipo_reserva === 'restaurante' ||
                    r.recurso.categoria.toLowerCase().includes('mesa') ||
                    r.recurso.categoria.toLowerCase().includes('restaurante'),
            );
        }

        if (filtroTipo === 'servicio') {
            return listaBase.filter(
                (r) => r.es_servicio || r.tipo_reserva === 'servicio',
            );
        }

        return listaBase;
    }, [listaBase, filtroTipo]);

    return (
        <PortalLayout cliente={cliente ?? undefined}>
            <Head>
                <title>Mis Reservaciones — Portal de Huéspedes</title>
                <meta
                    name="description"
                    content="Administra y consulta tus reservaciones de habitaciones y mesas de restaurante en Hotel Bugambilias Estelí."
                />
            </Head>

            <div className="mx-auto max-w-5xl space-y-8 p-5 sm:p-8 lg:p-10">
                {/* Encabezado */}
                <div>
                    <div className="flex items-center gap-2">
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-black text-primary dark:bg-rose-950/60 dark:text-rose-400">
                            <Calendar className="size-3.5" />
                            <span>Portal del Huésped & Cliente</span>
                        </span>
                    </div>
                    <h1 className="mt-2 text-2xl font-black text-foreground sm:text-3xl">
                        Mis Reservaciones & Estancias
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Consulta y gestiona tus reservas de habitaciones, mesas
                        de restaurante y servicios especiales de forma
                        organizada.
                    </p>
                </div>

                {/* Acceso / Búsqueda Rápida por Código de Reserva */}
                <AccesoCodigoCard
                    codigoInicial={codigoBusqueda}
                    titulo="Consultar o Vincular otra Reservación"
                    descripcion="Ingresa cualquier código de confirmación (ej. RES-2026-XXXX) para ver el estado de tu habitación o mesa."
                />

                {/* Filtros Principales: Estado (Activas / Historial) */}
                <div className="flex flex-col gap-4 border-b border-border/60 pb-3 sm:flex-row sm:items-center sm:justify-between">
                    {/* Tabs de Estado */}
                    <div className="flex items-center gap-1.5">
                        <Button
                            type="button"
                            variant={
                                tabEstado === 'activas' ? 'default' : 'ghost'
                            }
                            size="sm"
                            onClick={() => setTabEstado('activas')}
                            className={`cursor-pointer rounded-xl px-4 py-2 text-xs font-bold transition-all ${
                                tabEstado === 'activas'
                                    ? 'bg-primary text-white shadow-sm hover:bg-primary/90'
                                    : 'text-muted-foreground hover:bg-secondary hover:text-foreground'
                            }`}
                        >
                            Reservas Activas ({reservas_activas.length})
                        </Button>
                        <Button
                            type="button"
                            variant={
                                tabEstado === 'historial' ? 'default' : 'ghost'
                            }
                            size="sm"
                            onClick={() => setTabEstado('historial')}
                            className={`cursor-pointer rounded-xl px-4 py-2 text-xs font-bold transition-all ${
                                tabEstado === 'historial'
                                    ? 'bg-primary text-white shadow-sm hover:bg-primary/90'
                                    : 'text-muted-foreground hover:bg-secondary hover:text-foreground'
                            }`}
                        >
                            Historial Pasado ({historial_reservas.length})
                        </Button>
                    </div>

                    {/* Filtros por Tipo (Habitación / Restaurante / Servicio) */}
                    <div className="flex flex-wrap items-center gap-1 rounded-2xl border border-border/60 bg-secondary/50 p-1">
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => setFiltroTipo('todas')}
                            className={`flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                filtroTipo === 'todas'
                                    ? 'bg-card text-foreground shadow-xs hover:bg-card/90'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Layers className="size-3.5" />
                            <span>Todas ({conteos.todas})</span>
                        </Button>

                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => setFiltroTipo('habitacion')}
                            className={`flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                filtroTipo === 'habitacion'
                                    ? 'bg-primary/10 text-primary shadow-xs hover:bg-primary/20 dark:text-rose-400'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <BedDouble className="size-3.5" />
                            <span>Habitaciones ({conteos.habitacion})</span>
                        </Button>

                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => setFiltroTipo('restaurante')}
                            className={`flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                filtroTipo === 'restaurante'
                                    ? 'bg-amber-500/10 text-amber-600 shadow-xs hover:bg-amber-500/20 dark:text-amber-400'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <UtensilsCrossed className="size-3.5" />
                            <span>Restaurante ({conteos.restaurante})</span>
                        </Button>

                        {conteos.servicio > 0 && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={() => setFiltroTipo('servicio')}
                                className={`flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all ${
                                    filtroTipo === 'servicio'
                                        ? 'bg-indigo-500/10 text-indigo-600 shadow-xs hover:bg-indigo-500/20 dark:text-indigo-400'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                <Sparkles className="size-3.5" />
                                <span>Servicios ({conteos.servicio})</span>
                            </Button>
                        )}
                    </div>
                </div>

                {/* Listado de Reservas Filtradas */}
                {listaFiltrada.length > 0 ? (
                    <div className="space-y-4">
                        {listaFiltrada.map((reserva) => (
                            <PortalReservaItem
                                key={reserva.id}
                                reserva={reserva}
                                onCancelar={cancelarReserva}
                            />
                        ))}
                    </div>
                ) : (
                    <div className="rounded-3xl border border-dashed border-border/80 bg-secondary/20 p-12 text-center">
                        {filtroTipo === 'restaurante' ? (
                            <UtensilsCrossed className="mx-auto size-12 text-muted-foreground/60" />
                        ) : (
                            <BedDouble className="mx-auto size-12 text-muted-foreground/60" />
                        )}
                        <h4 className="mt-3 text-base font-bold text-foreground">
                            {tabEstado === 'activas'
                                ? `No tienes reservaciones ${filtroTipo !== 'todas' ? `de ${filtroTipo}` : ''} activas`
                                : `No se encontraron reservaciones ${filtroTipo !== 'todas' ? `de ${filtroTipo}` : ''} en tu historial`}
                        </h4>
                        <p className="mt-1 text-xs text-muted-foreground">
                            {tabEstado === 'activas'
                                ? 'Ingresa tu código de reserva arriba o realiza una nueva reservación para verla reflejada aquí.'
                                : 'Tus estancias y reservaciones completadas o finalizadas se guardarán en este historial.'}
                        </p>
                    </div>
                )}
            </div>
        </PortalLayout>
    );
};

MisReservas.layout = (page: React.ReactNode) => page;

export default MisReservas;
