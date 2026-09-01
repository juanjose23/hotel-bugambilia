import { Head, router } from '@inertiajs/react';
import { BedDouble } from 'lucide-react';
import { useState } from 'react';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import { PortalReservaItem } from '@/modules/clientes/components/reservas/PortalReservaItem';
import type {
    ClienteProfile,
    PortalReservaResumen,
} from '@/modules/clientes/types';

interface MisReservasPageProps {
    reservas_activas: PortalReservaResumen[];
    historial_reservas: PortalReservaResumen[];
    cliente: ClienteProfile;
}

export const MisReservas = ({
    reservas_activas,
    historial_reservas,
    cliente,
}: MisReservasPageProps) => {
    const [tab, setTab] = useState<'activas' | 'historial'>('activas');

    const handleCancelarReserva = (
        reservaId: number,
        codigoReserva: string,
    ) => {
        if (!confirm('¿Estás seguro de que deseas cancelar tu reservación?')) {
            return;
        }

        router.post(
            `/reservas/${reservaId}/cancelar`,
            {
                codigo: codigoReserva,
            },
            {
                preserveScroll: true,
            },
        );
    };

    const listaActual =
        tab === 'activas' ? reservas_activas : historial_reservas;

    return (
        <PortalLayout cliente={cliente}>
            <Head>
                <title>Mis Reservaciones — Portal de Huéspedes</title>
                <meta
                    name="description"
                    content="Administra y consulta tus reservaciones activas y pasadas en Hotel Bugambilias Estelí."
                />
            </Head>

            <div className="mx-auto max-w-5xl space-y-8 p-5 sm:p-8 lg:p-10">
                <div>
                    <span className="text-xs font-black tracking-wider text-primary uppercase">
                        Portal de Huéspedes
                    </span>
                    <h1 className="mt-1 text-2xl font-black text-foreground sm:text-3xl">
                        Mis Reservaciones & Estancias
                    </h1>
                    <p className="mt-0.5 text-sm text-muted-foreground">
                        Revisa los detalles de tus reservas, descarga tus
                        comprobantes en PDF y pide servicios para tu estancia.
                    </p>
                </div>

                {/* Tabs de Selección */}
                <div className="flex items-center gap-2 border-b border-border/60 pb-3">
                    <button
                        type="button"
                        onClick={() => setTab('activas')}
                        className={`rounded-xl px-4 py-2 text-xs font-bold transition-all ${
                            tab === 'activas'
                                ? 'bg-primary text-white shadow-sm'
                                : 'text-muted-foreground hover:bg-secondary hover:text-foreground'
                        }`}
                    >
                        Reservas Activas ({reservas_activas.length})
                    </button>
                    <button
                        type="button"
                        onClick={() => setTab('historial')}
                        className={`rounded-xl px-4 py-2 text-xs font-bold transition-all ${
                            tab === 'historial'
                                ? 'bg-primary text-white shadow-sm'
                                : 'text-muted-foreground hover:bg-secondary hover:text-foreground'
                        }`}
                    >
                        Historial Pasado ({historial_reservas.length})
                    </button>
                </div>

                {/* Listado de Reservas */}
                {listaActual.length > 0 ? (
                    <div className="space-y-4">
                        {listaActual.map((reserva) => (
                            <PortalReservaItem
                                key={reserva.id}
                                reserva={reserva}
                                onCancelar={handleCancelarReserva}
                            />
                        ))}
                    </div>
                ) : (
                    <div className="rounded-3xl border border-dashed border-border/80 bg-secondary/20 p-12 text-center">
                        <BedDouble className="mx-auto size-12 text-muted-foreground/60" />
                        <h4 className="mt-3 text-base font-bold text-foreground">
                            {tab === 'activas'
                                ? 'No tienes reservaciones activas en este momento'
                                : 'No se encontraron reservaciones anteriores'}
                        </h4>
                        <p className="mt-1 text-xs text-muted-foreground">
                            {tab === 'activas'
                                ? 'Cuando realices una nueva reservación, aparecerá aquí.'
                                : 'Tus estancias completadas o finalizadas se guardarán en este historial.'}
                        </p>
                    </div>
                )}
            </div>
        </PortalLayout>
    );
};

MisReservas.layout = (page: React.ReactNode) => page;

export default MisReservas;
