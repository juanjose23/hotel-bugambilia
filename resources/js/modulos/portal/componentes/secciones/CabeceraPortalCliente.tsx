import { LayoutDashboard, KeyRound, PlusCircle } from 'lucide-react';
import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { usePropiedadesPagina } from '@/modulos/compartido/hooks/usePropiedadesPagina';
import { ModalAccesoPortalCliente } from './ModalAccesoPortalCliente';

interface PropiedadesCabeceraPortalCliente {
    hotelName?: string;
    totalReservas?: number;
    reservasActivasCount?: number;
}

export const CabeceraPortalCliente = ({
    hotelName = 'Hotel Bugambilias',
    totalReservas = 0,
    reservasActivasCount = 0,
}: PropiedadesCabeceraPortalCliente) => {
    const [modalAccesoAbierto, setModalAccesoAbierto] = useState(false);
    const { auth } = usePropiedadesPagina();
    const usuario = auth?.user;

    return (
        <div className="relative overflow-hidden rounded-3xl border border-border/80 bg-gradient-to-r from-muted/80 via-card to-background p-4 font-sans shadow-xs sm:p-6 md:p-8">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="space-y-1">
                    <div className="flex items-center gap-2">
                        <Badge
                            variant="outline"
                            className="border-bugambilia-500/40 bg-bugambilia-500/10 px-3 py-1 text-xs font-extrabold text-bugambilia-600 dark:text-bugambilia-400"
                        >
                            <LayoutDashboard className="mr-1.5 size-3.5" />
                            Portal de Huéspedes
                        </Badge>
                    </div>
                    <h1 className="text-xl font-black tracking-tight text-foreground sm:text-3xl lg:text-4xl">
                        Mis Reservaciones & Historial
                    </h1>
                    <p className="text-xs font-medium text-muted-foreground md:text-sm">
                        Gestione sus estancias en {hotelName}, realice Auto
                        Check-In y descargue sus comprobantes.
                    </p>
                </div>

                <div className="flex shrink-0 flex-wrap items-center gap-2">
                    {usuario ? (
                        <Link
                            href="/habitaciones"
                            className="inline-flex items-center gap-1.5 rounded-full bg-bugambilia-600 px-4 py-2 text-xs font-extrabold text-white shadow-xs transition-colors hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                        >
                            <PlusCircle className="size-4" />
                            Nueva Reservación
                        </Link>
                    ) : (
                        <Button
                            onClick={() => setModalAccesoAbierto(true)}
                            className="rounded-full bg-bugambilia-600 font-extrabold text-white shadow-xs hover:bg-bugambilia-700 dark:bg-bugambilia-500 text-xs px-3.5 py-2"
                        >
                            <KeyRound className="mr-1.5 size-3.5" />
                            Acceso por Código / QR / Login
                        </Button>
                    )}

                    <div className="flex flex-1 items-center justify-around gap-2 sm:flex-initial">
                        <div className="flex flex-col rounded-2xl border border-border/80 bg-background px-3 py-1.5 text-center shadow-2xs sm:px-4 sm:py-2">
                            <span className="text-[9px] font-extrabold text-muted-foreground uppercase sm:text-[10px]">
                                Activas
                            </span>
                            <span className="text-base font-black text-bugambilia-600 sm:text-lg dark:text-bugambilia-400">
                                {reservasActivasCount}
                            </span>
                        </div>
                        <div className="flex flex-col rounded-2xl border border-border/80 bg-background px-3 py-1.5 text-center shadow-2xs sm:px-4 sm:py-2">
                            <span className="text-[9px] font-extrabold text-muted-foreground uppercase sm:text-[10px]">
                                Total
                            </span>
                            <span className="text-base font-black text-foreground sm:text-lg">
                                {totalReservas}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal Interactivo de los 3 Métodos de Acceso */}
            <ModalAccesoPortalCliente
                estaAbierto={modalAccesoAbierto}
                alCerrar={() => setModalAccesoAbierto(false)}
            />
        </div>
    );
};

export default CabeceraPortalCliente;
