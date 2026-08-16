import { Flame } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modulos/compartido/ui/boton';
import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
} from '@/modulos/compartido/ui/pestanas';
import { usePortalMisReservas } from '../hooks/usePortalMisReservas';
import type { PropiedadesPortalMisReservas } from '../interfaces/portalInterfaces';
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

    return (
        <section className="min-h-screen bg-background pt-3 pb-12 font-sans md:pt-4 md:pb-16">
            <div className="container mx-auto space-y-8 px-4 sm:px-6 lg:px-8">
                {/* Cabecera Principal del Portal */}
                <CabeceraPortalCliente
                    hotelName={hotel?.name}
                    totalReservas={reservas.length}
                    reservasActivasCount={reservasActivas.length}
                />

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
                    onValueChange={(val) => setPortalTab(val as any)}
                    className="w-full"
                >
                    <TabsList className="mb-6 flex w-full max-w-md gap-1 rounded-2xl border border-border/80 bg-card p-1 shadow-2xs">
                        <TabsTrigger
                            value="overview"
                            className="flex-1 rounded-xl text-xs font-bold data-[state=active]:bg-bugambilia-600 data-[state=active]:text-white"
                        >
                            Todas ({reservasFiltradas.length})
                        </TabsTrigger>
                        <TabsTrigger
                            value="activas"
                            className="flex-1 rounded-xl text-xs font-bold data-[state=active]:bg-bugambilia-600 data-[state=active]:text-white"
                        >
                            Activas ({reservasActivas.length})
                        </TabsTrigger>
                        <TabsTrigger
                            value="historial"
                            className="flex-1 rounded-xl text-xs font-bold data-[state=active]:bg-bugambilia-600 data-[state=active]:text-white"
                        >
                            Historial ({reservasPasadas.length})
                        </TabsTrigger>
                    </TabsList>

                    {/* Contenido Pestaña Todas */}
                    <TabsContent value="overview" className="space-y-4">
                        {reservasFiltradas.length > 0 ? (
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                {reservasFiltradas.map((reserva) => (
                                    <TarjetaReservaPortalItem
                                        key={reserva.id}
                                        reserva={reserva}
                                        onSolicitarCancelacion={
                                            setReservaACancelar
                                        }
                                    />
                                ))}
                            </div>
                        ) : (
                            <SinReservasEstado
                                resetSearch={() => setSearchTerm('')}
                            />
                        )}
                    </TabsContent>

                    {/* Contenido Pestaña Activas */}
                    <TabsContent value="activas" className="space-y-4">
                        {reservasActivas.length > 0 ? (
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                {reservasActivas.map((reserva) => (
                                    <TarjetaReservaPortalItem
                                        key={reserva.id}
                                        reserva={reserva}
                                        onSolicitarCancelacion={
                                            setReservaACancelar
                                        }
                                    />
                                ))}
                            </div>
                        ) : (
                            <SinReservasEstado
                                resetSearch={() => setSearchTerm('')}
                            />
                        )}
                    </TabsContent>

                    {/* Contenido Pestaña Historial */}
                    <TabsContent value="historial" className="space-y-4">
                        {reservasPasadas.length > 0 ? (
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                {reservasPasadas.map((reserva) => (
                                    <TarjetaReservaPortalItem
                                        key={reserva.id}
                                        reserva={reserva}
                                        onSolicitarCancelacion={
                                            setReservaACancelar
                                        }
                                    />
                                ))}
                            </div>
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

const SinReservasEstado = ({ resetSearch }: { resetSearch: () => void }) => {
    const [modalAbierto, setModalAbierto] = useState(false);

    return (
        <div className="flex flex-col items-center justify-center rounded-3xl border border-border bg-card p-12 text-center">
            <Flame className="mb-3 size-12 text-muted-foreground/40" />
            <h3 className="text-lg font-black text-foreground">
                No se encontraron reservaciones
            </h3>
            <p className="mt-1 text-xs font-medium text-muted-foreground">
                Verifique el código de reservación, escanee su voucher QR o
                inicie sesión.
            </p>

            <div className="mt-4 flex flex-wrap items-center justify-center gap-2">
                <Button
                    onClick={() => setModalAbierto(true)}
                    className="rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                >
                    Ingresar con Código / QR / Usuario
                </Button>
                <Button
                    onClick={resetSearch}
                    variant="outline"
                    className="rounded-full font-bold"
                >
                    Ver Todas las Reservas
                </Button>
            </div>

            <ModalAccesoPortalCliente
                estaAbierto={modalAbierto}
                alCerrar={() => setModalAbierto(false)}
            />
        </div>
    );
};

export default SeccionPortalMisReservas;
