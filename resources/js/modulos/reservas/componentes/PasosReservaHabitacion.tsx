import React from 'react';
import type { DateRange } from 'react-day-picker';
import { Textarea } from '@/modulos/compartido/ui/area-texto';
import { Campo, EtiquetaCampo } from '@/modulos/compartido/ui/campo';
import type { HabitacionReservable } from '@/modulos/habitaciones/interfaces/reservaHabitacion';
import type { ConfiguracionStripePago } from '@/modulos/pagos/interfaces/pago';
import { ResumenConfirmacionReserva } from '@/modulos/reservas/componentes/ResumenConfirmacionReserva';
import { SeccionesAdicionalesReserva } from '@/modulos/reservas/componentes/SeccionesAdicionalesReserva';
import { SelectorFechasEstancia } from '@/modulos/reservas/componentes/SelectorFechasEstancia';
import { SelectorHuespedes } from '@/modulos/reservas/componentes/SelectorHuespedes';
import { SolicitudCuentaEstancia } from '@/modulos/reservas/componentes/SolicitudCuentaEstancia';
import type { DatosBorradorHabitacion } from '@/modulos/reservas/interfaces/borradorReserva';
import type { OpcionesReserva } from '@/modulos/reservas/interfaces/opcionesReserva';

interface RecomendacionDisponibilidad {
    fecha_check_in: string;
    fecha_check_out: string;
    noches: number;
    disponibles_minimos: number;
}

interface DisponibilidadEntrada {
    ocupadas: number;
    total: number;
    disponibles: number;
    agotado: boolean;
}

interface PropiedadesPasosReservaHabitacion {
    pasoActual: number;
    room: HabitacionReservable;
    opcionesReserva: OpcionesReserva;
    data: DatosBorradorHabitacion;
    setData: {
        (data: Partial<DatosBorradorHabitacion>): void;
        <Campo extends keyof DatosBorradorHabitacion>(
            campo: Campo,
            valor: DatosBorradorHabitacion[Campo],
        ): void;
        (
            callback: (
                data: DatosBorradorHabitacion,
            ) => DatosBorradorHabitacion,
        ): void;
    };
    calendario: {
        mesesCalendario: number;
        fechaSeleccionada: DateRange | undefined;
        esFechaDeshabilitada: (fecha: Date) => boolean;
        fechaEstaAgotada: (fecha: Date) => boolean;
        seleccionarRangoFechas: (rango?: DateRange) => void;
        limpiarSeleccionFechas: () => void;
        nochesCalculadas: number;
        disponibilidadEntrada: DisponibilidadEntrada | null;
    };
    totalHabitacionesCategoria: number;
    rangoExactoDisponible: boolean | null;
    recomendacionesDisponibilidad: RecomendacionDisponibilidad[];
    subtotalEstimado: number;
    stripePago?: ConfiguracionStripePago | null;
    preparandoStripe?: boolean;
    errorStripe?: string | null;
}

export function PasosReservaHabitacion({
    pasoActual,
    room,
    opcionesReserva,
    data,
    setData,
    calendario,
    totalHabitacionesCategoria,
    rangoExactoDisponible,
    recomendacionesDisponibilidad,
    subtotalEstimado,
    stripePago = null,
    preparandoStripe = false,
    errorStripe = null,
}: PropiedadesPasosReservaHabitacion) {
    const serviciosAdicionales = data.servicios_adicionales ?? [];
    const espaciosAdicionales = data.espacios_adicionales ?? [];

    if (pasoActual === 1) {
        return (
            <SelectorFechasEstancia
                fechaCheckIn={data.fecha_check_in}
                fechaCheckOut={data.fecha_check_out}
                nombreCliente={data.nombre_cliente}
                telefonoCliente={data.telefono_cliente}
                emailCliente={data.email_cliente}
                nochesCalculadas={calendario.nochesCalculadas}
                totalHabitacionesCategoria={totalHabitacionesCategoria}
                disponibilidadEntrada={calendario.disponibilidadEntrada}
                rangoExactoDisponible={rangoExactoDisponible}
                recomendacionesDisponibilidad={recomendacionesDisponibilidad}
                onAplicarRecomendacion={(checkIn, checkOut) =>
                    setData((prev) => ({
                        ...prev,
                        fecha_check_in: checkIn,
                        fecha_check_out: checkOut,
                    }))
                }
                mesesCalendario={calendario.mesesCalendario}
                fechaSeleccionada={calendario.fechaSeleccionada}
                esFechaDeshabilitada={calendario.esFechaDeshabilitada}
                fechaEstaAgotada={calendario.fechaEstaAgotada}
                onSelectRangoFechas={calendario.seleccionarRangoFechas}
                onLimpiarFechas={calendario.limpiarSeleccionFechas}
                onNombreChange={(val) => setData('nombre_cliente', val)}
                onTelefonoChange={(val) => setData('telefono_cliente', val)}
                onEmailChange={(val) => setData('email_cliente', val)}
            />
        );
    }

    if (pasoActual === 2) {
        return (
            <SelectorHuespedes
                adultos={data.adultos}
                ninos={data.ninos}
                capacidadMaxima={room.capacidad}
                onAdultosChange={(val) => setData('adultos', val)}
                onNinosChange={(val) => setData('ninos', val)}
            />
        );
    }

    if (pasoActual === 3) {
        return (
            <div className="animate-in fade-in-50 flex flex-col gap-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
                <div>
                    <h2 className="text-lg font-black text-foreground md:text-xl">
                        Complementos &{' '}
                        <span className="font-serif font-normal text-primary italic">
                            Experiencias
                        </span>
                    </h2>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        Personalice su estancia con servicios y promociones
                        exclusivas
                    </p>
                </div>

                <SeccionesAdicionalesReserva
                    opciones={opcionesReserva}
                    serviciosSeleccionados={serviciosAdicionales.map(
                        (i) => i.servicio_id,
                    )}
                    espaciosSeleccionados={espaciosAdicionales.map(
                        (i) => i.espacio_id,
                    )}
                    promocionId={data.promocion_id}
                    onServiciosChange={(ids) =>
                        setData(
                            'servicios_adicionales',
                            ids.map((servicio_id) => ({
                                servicio_id,
                                cantidad: 1,
                            })),
                        )
                    }
                    onEspaciosChange={(ids) =>
                        setData(
                            'espacios_adicionales',
                            ids.map((espacio_id) => ({
                                espacio_id,
                                cantidad: 1,
                            })),
                        )
                    }
                    onPromocionChange={(id) => setData('promocion_id', id)}
                />

                <SolicitudCuentaEstancia
                    solicitada={data.solicita_cuenta}
                    limite={data.limite_cuenta_solicitado}
                    alCambiarSolicitud={(solicitada) => {
                        setData('solicita_cuenta', solicitada);

                        if (!solicitada) {
                            setData('limite_cuenta_solicitado', null);
                        }
                    }}
                    alCambiarLimite={(limite) =>
                        setData('limite_cuenta_solicitado', limite)
                    }
                />

                <Campo>
                    <EtiquetaCampo htmlFor="notas-habitacion">
                        Indicaciones o Solicitudes Especiales
                    </EtiquetaCampo>
                    <Textarea
                        id="notas-habitacion"
                        value={data.notas}
                        onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) =>
                            setData('notas', e.target.value)
                        }
                        rows={3}
                        placeholder="Ej. Almohada extra, horario aproximado de check-in..."
                    />
                </Campo>
            </div>
        );
    }

    return (
        <ResumenConfirmacionReserva
            nombreRecurso={room.nombre}
            categoriaRecurso={room.categoria}
            nombreCliente={data.nombre_cliente}
            telefonoCliente={data.telefono_cliente}
            fechaCheckIn={data.fecha_check_in}
            fechaCheckOut={data.fecha_check_out}
            nochesCalculadas={calendario.nochesCalculadas}
            adultos={data.adultos}
            ninos={data.ninos}
            moneda={room.moneda}
            subtotalEstimado={subtotalEstimado}
            tipoPagoReserva={data.tipo_pago_reserva}
            canalPagoReserva={data.canal_pago_reserva}
            stripePago={stripePago}
            preparandoStripe={preparandoStripe}
            errorStripe={errorStripe}
            onCanalPagoChange={(canal) =>
                setData((prev: any) => ({
                    ...prev,
                    canal_pago_reserva: canal,
                    metodo_pago_reserva: canal === 'transferencia' ? 4 : null,
                }))
            }
        />
    );
}
