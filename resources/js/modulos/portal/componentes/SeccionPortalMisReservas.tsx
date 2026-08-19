import { Link } from '@inertiajs/react';
import { Flame, BedDouble, ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';
import { usePropiedadesPagina } from '@/modulos/compartido/hooks/usePropiedadesPagina';
import { Button } from '@/modulos/compartido/ui/boton';
import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
} from '@/modulos/compartido/ui/pestanas';
import { usePortalMisReservas } from '../hooks/usePortalMisReservas';
import type {
    PropiedadesPortalMisReservas,
    TabPortal,
} from '../interfaces/portalInterfaces';
import { AccionesRapidasPortal } from './secciones/AccionesRapidasPortal';
import { BuscadorMisReservas } from './secciones/BuscadorMisReservas';
import { CabeceraPortalCliente } from './secciones/CabeceraPortalCliente';
import { ModalAccesoPortalCliente } from './secciones/ModalAccesoPortalCliente';
import { ModalCancelarReserva } from './secciones/ModalCancelarReserva';
import { TarjetaReservaPortalItem } from './secciones/TarjetaReservaPortalItem';

export const SeccionPortalMisReservas = ({
    reservas = [],
    hotel,
}: PropiedadesPortalMisReservas) => {
    const {
        searchTerm,
        setSearchTerm,
        portalTab,
        setPortalTab,
        paginaActual,
        setPaginaActual,
        itemsPorPagina,
        reservasFiltradas,
        reservasActivas,
        reservasPasadas,
        reservaACancelar,
        setReservaACancelar,
        motivoCancelacion,
        setMotivoCancelacion,
        cancelando,
        errorCancelacion,
        mensajeCancelacion,
        reembolsoPendiente,
        cerrarCancelacion,
        cancelarReserva,
    } = usePortalMisReservas(reservas);

    const obtenerItemsPagina = <T,>(lista: T[]): T[] => {
        const inicio = (paginaActual - 1) * itemsPorPagina;

        return lista.slice(inicio, inicio + itemsPorPagina);
    };

    const calcularTotalPaginas = (totalItems: number): number =>
        Math.max(1, Math.ceil(totalItems / itemsPorPagina));

    return (
        <section className="min-h-screen w-full max-w-full overflow-x-hidden bg-background pt-3 pb-12 font-sans md:pt-4 md:pb-16">
            <div className="container mx-auto max-w-full space-y-6 px-3 sm:space-y-8 sm:px-6 lg:px-8">
                {/* Cabecera Principal del Portal */}
                <CabeceraPortalCliente
                    hotelName={hotel?.name}
                    totalReservas={reservas.length}
                    reservasActivasCount={reservasActivas.length}
                />

                {/* Banner Dashboard de Gestión Administrativa del Huésped */}
                <div className="grid grid-cols-2 gap-2 sm:gap-3 lg:grid-cols-4">
                    <div className="rounded-2xl border border-border/80 bg-card p-3 shadow-2xs sm:p-4">
                        <span className="block truncate text-[9px] font-extrabold tracking-wider text-muted-foreground uppercase sm:text-[10px]">
                            Reservas Activas
                        </span>
                        <span className="font-mono text-lg font-black text-bugambilia-600 sm:text-xl dark:text-bugambilia-400">
                            {reservasActivas.length}
                        </span>
                    </div>

                    <div className="rounded-2xl border border-border/80 bg-card p-3 shadow-2xs sm:p-4">
                        <span className="block truncate text-[9px] font-extrabold tracking-wider text-muted-foreground uppercase sm:text-[10px]">
                            Huéspedes Registrados
                        </span>
                        <span className="font-mono text-lg font-black text-foreground sm:text-xl">
                            {reservas.reduce(
                                (acc, r) =>
                                    acc + (r.adultos || 1) + (r.ninos || 0),
                                0,
                            )}
                        </span>
                    </div>

                    <div className="rounded-2xl border border-border/80 bg-card p-3 shadow-2xs sm:p-4">
                        <span className="block truncate text-[9px] font-extrabold tracking-wider text-muted-foreground uppercase sm:text-[10px]">
                            Servicios Estancia
                        </span>
                        <span className="font-mono text-lg font-black text-emerald-600 sm:text-xl dark:text-emerald-400">
                            Activo
                        </span>
                    </div>

                    <div className="rounded-2xl border border-border/80 bg-card p-3 shadow-2xs sm:p-4">
                        <span className="block truncate text-[9px] font-extrabold tracking-wider text-muted-foreground uppercase sm:text-[10px]">
                            Saldo Pendiente
                        </span>
                        <span className="font-mono text-lg font-black text-foreground sm:text-xl">
                            $
                            {reservas.reduce(
                                (acc, r) =>
                                    acc +
                                    (r.estado_cuenta?.saldo_pendiente || 0),
                                0,
                            )}
                        </span>
                    </div>
                </div>

                {/* Acciones Rápidas del Cliente */}
                <AccionesRapidasPortal />

                {/* Buscador por Código de Reserva o Nombre */}
                <BuscadorMisReservas
                    searchTerm={searchTerm}
                    onSearchChange={setSearchTerm}
                />

                {/* Pestañas de Estado: Todas, Activas, Historial */}
                <Tabs
                    defaultValue="overview"
                    value={portalTab}
                    onValueChange={(val) => setPortalTab(val as TabPortal)}
                    className="w-full"
                >
                    <TabsList className="no-scrollbar mb-6 flex w-full max-w-md gap-1 overflow-x-auto rounded-2xl border border-border/80 bg-card p-1 shadow-2xs">
                        <TabsTrigger
                            value="overview"
                            className="min-w-0 flex-1 truncate rounded-xl px-2 py-1.5 text-[11px] font-bold data-[state=active]:bg-bugambilia-600 data-[state=active]:text-white sm:text-xs"
                        >
                            Todas ({reservasFiltradas.length})
                        </TabsTrigger>
                        <TabsTrigger
                            value="activas"
                            className="min-w-0 flex-1 truncate rounded-xl px-2 py-1.5 text-[11px] font-bold data-[state=active]:bg-bugambilia-600 data-[state=active]:text-white sm:text-xs"
                        >
                            Activas ({reservasActivas.length})
                        </TabsTrigger>
                        <TabsTrigger
                            value="historial"
                            className="min-w-0 flex-1 truncate rounded-xl px-2 py-1.5 text-[11px] font-bold data-[state=active]:bg-bugambilia-600 data-[state=active]:text-white sm:text-xs"
                        >
                            Historial ({reservasPasadas.length})
                        </TabsTrigger>
                    </TabsList>

                    {/* Contenido Pestaña Todas */}
                    <TabsContent value="overview" className="space-y-4">
                        {reservasFiltradas.length > 0 ? (
                            <>
                                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    {obtenerItemsPagina(reservasFiltradas).map(
                                        (reserva) => (
                                            <TarjetaReservaPortalItem
                                                key={reserva.id}
                                                reserva={reserva}
                                                onSolicitarCancelacion={
                                                    setReservaACancelar
                                                }
                                            />
                                        ),
                                    )}
                                </div>
                                <PaginadorLocal
                                    paginaActual={paginaActual}
                                    totalPaginas={calcularTotalPaginas(
                                        reservasFiltradas.length,
                                    )}
                                    totalItems={reservasFiltradas.length}
                                    itemsPorPagina={itemsPorPagina}
                                    onPageChange={setPaginaActual}
                                />
                            </>
                        ) : (
                            <SinReservasEstado
                                resetSearch={() => setSearchTerm('')}
                            />
                        )}
                    </TabsContent>

                    {/* Contenido Pestaña Activas */}
                    <TabsContent value="activas" className="space-y-4">
                        {reservasActivas.length > 0 ? (
                            <>
                                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    {obtenerItemsPagina(reservasActivas).map(
                                        (reserva) => (
                                            <TarjetaReservaPortalItem
                                                key={reserva.id}
                                                reserva={reserva}
                                                onSolicitarCancelacion={
                                                    setReservaACancelar
                                                }
                                            />
                                        ),
                                    )}
                                </div>
                                <PaginadorLocal
                                    paginaActual={paginaActual}
                                    totalPaginas={calcularTotalPaginas(
                                        reservasActivas.length,
                                    )}
                                    totalItems={reservasActivas.length}
                                    itemsPorPagina={itemsPorPagina}
                                    onPageChange={setPaginaActual}
                                />
                            </>
                        ) : (
                            <SinReservasEstado
                                resetSearch={() => setSearchTerm('')}
                            />
                        )}
                    </TabsContent>

                    {/* Contenido Pestaña Historial */}
                    <TabsContent value="historial" className="space-y-4">
                        {reservasPasadas.length > 0 ? (
                            <>
                                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    {obtenerItemsPagina(reservasPasadas).map(
                                        (reserva) => (
                                            <TarjetaReservaPortalItem
                                                key={reserva.id}
                                                reserva={reserva}
                                                onSolicitarCancelacion={
                                                    setReservaACancelar
                                                }
                                            />
                                        ),
                                    )}
                                </div>
                                <PaginadorLocal
                                    paginaActual={paginaActual}
                                    totalPaginas={calcularTotalPaginas(
                                        reservasPasadas.length,
                                    )}
                                    totalItems={reservasPasadas.length}
                                    itemsPorPagina={itemsPorPagina}
                                    onPageChange={setPaginaActual}
                                />
                            </>
                        ) : (
                            <SinReservasEstado
                                resetSearch={() => setSearchTerm('')}
                            />
                        )}
                    </TabsContent>
                </Tabs>
            </div>

            {/* Modal de Cancelación de Reservas */}
            <ModalCancelarReserva
                reserva={reservaACancelar}
                motivoCancelacion={motivoCancelacion}
                onMotivoChange={setMotivoCancelacion}
                onClose={cerrarCancelacion}
                onConfirm={() =>
                    reservaACancelar && cancelarReserva(reservaACancelar)
                }
                cancelando={cancelando}
                errorCancelacion={errorCancelacion}
                mensajeCancelacion={mensajeCancelacion}
                reembolsoPendiente={reembolsoPendiente}
            />
        </section>
    );
};

const PaginadorLocal = ({
    paginaActual,
    totalPaginas,
    totalItems,
    itemsPorPagina,
    onPageChange,
}: {
    paginaActual: number;
    totalPaginas: number;
    totalItems: number;
    itemsPorPagina: number;
    onPageChange: (p: number) => void;
}) => {
    if (totalPaginas <= 1) {
        return null;
    }

    const desde = (paginaActual - 1) * itemsPorPagina + 1;
    const hasta = Math.min(paginaActual * itemsPorPagina, totalItems);

    return (
        <div className="mt-6 flex flex-col items-center justify-between gap-3 border-t border-border/60 pt-6 font-sans sm:flex-row">
            <span className="text-xs font-semibold text-muted-foreground">
                Mostrando{' '}
                <span className="font-bold text-foreground">{desde}</span> a{' '}
                <span className="font-bold text-foreground">{hasta}</span> de{' '}
                <span className="font-bold text-foreground">{totalItems}</span>{' '}
                reservación(es)
            </span>

            <div className="flex items-center gap-1.5 sm:gap-2">
                <Button
                    variant="outline"
                    disabled={paginaActual <= 1}
                    onClick={() => onPageChange(paginaActual - 1)}
                    className="rounded-full px-2.5 py-1 text-[11px] font-extrabold sm:px-3 sm:text-xs"
                >
                    <ChevronLeft className="mr-1 size-3.5" /> Anterior
                </Button>

                <span className="px-1 font-mono text-[11px] font-black text-foreground sm:px-2 sm:text-xs">
                    {paginaActual} / {totalPaginas}
                </span>

                <Button
                    variant="outline"
                    disabled={paginaActual >= totalPaginas}
                    onClick={() => onPageChange(paginaActual + 1)}
                    className="rounded-full px-2.5 py-1 text-[11px] font-extrabold sm:px-3 sm:text-xs"
                >
                    Siguiente <ChevronRight className="ml-1 size-3.5" />
                </Button>
            </div>
        </div>
    );
};

const SinReservasEstado = ({ resetSearch }: { resetSearch: () => void }) => {
    const [modalAbierto, setModalAbierto] = useState(false);
    const { auth } = usePropiedadesPagina();
    const usuario = auth?.user;

    return (
        <div className="flex flex-col items-center justify-center rounded-3xl border border-border bg-card p-12 text-center">
            <Flame className="mb-3 size-12 text-muted-foreground/40" />
            <h3 className="text-lg font-black text-foreground">
                {usuario
                    ? 'No posee reservaciones en esta sección'
                    : 'No se encontraron reservaciones'}
            </h3>
            <p className="mt-1 text-xs font-medium text-muted-foreground">
                {usuario
                    ? 'Explore nuestras habitaciones disponibles para realizar su próxima reservación.'
                    : 'Verifique el código de reservación, escanee su voucher QR o inicie sesión.'}
            </p>

            <div className="mt-4 flex flex-wrap items-center justify-center gap-2">
                {usuario ? (
                    <Link
                        href="/habitaciones"
                        className="inline-flex items-center gap-1.5 rounded-full bg-bugambilia-600 px-5 py-2 text-xs font-extrabold text-white transition-colors hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                    >
                        <BedDouble className="size-4" />
                        Explorar Habitaciones & Reservar
                    </Link>
                ) : (
                    <Button
                        onClick={() => setModalAbierto(true)}
                        className="rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                    >
                        Ingresar con Código / QR / Usuario
                    </Button>
                )}
                <Button
                    onClick={resetSearch}
                    variant="outline"
                    className="rounded-full font-bold"
                >
                    Ver Todas las Reservas
                </Button>
            </div>

            {!usuario && (
                <ModalAccesoPortalCliente
                    estaAbierto={modalAbierto}
                    alCerrar={() => setModalAbierto(false)}
                />
            )}
        </div>
    );
};

export default SeccionPortalMisReservas;
