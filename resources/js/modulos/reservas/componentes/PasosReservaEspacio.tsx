import { Calendar, User, Phone, Mail, FileText } from 'lucide-react';
import React from 'react';
import { Textarea } from '@/modulos/compartido/ui/area-texto';
import {
    CampoGrupo,
    Campo,
    EtiquetaCampo,
} from '@/modulos/compartido/ui/campo';
import { Input } from '@/modulos/compartido/ui/entrada';
import type { EspacioReservable } from '@/modulos/espacios/interfaces/reservaEspacio';
import { ResumenConfirmacionReserva } from '@/modulos/reservas/componentes/ResumenConfirmacionReserva';
import { SeccionesAdicionalesReserva } from '@/modulos/reservas/componentes/SeccionesAdicionalesReserva';
import { SelectorHuespedes } from '@/modulos/reservas/componentes/SelectorHuespedes';
import { SolicitudCuentaEstancia } from '@/modulos/reservas/componentes/SolicitudCuentaEstancia';
import type { DatosBorradorEspacio } from '@/modulos/reservas/interfaces/borradorReserva';
import type { OpcionesReserva } from '@/modulos/reservas/interfaces/opcionesReserva';

interface PropiedadesPasosReservaEspacio {
    pasoActual: number;
    space: EspacioReservable;
    opcionesReserva: OpcionesReserva;
    data: DatosBorradorEspacio;
    setData: {
        (data: Partial<DatosBorradorEspacio>): void;
        <Campo extends keyof DatosBorradorEspacio>(
            campo: Campo,
            valor: DatosBorradorEspacio[Campo],
        ): void;
        (callback: (data: DatosBorradorEspacio) => DatosBorradorEspacio): void;
    };
    subtotalEstimado: number;
}

export function PasosReservaEspacio({
    pasoActual,
    space,
    opcionesReserva,
    data,
    setData,
    subtotalEstimado,
}: PropiedadesPasosReservaEspacio) {
    const serviciosIds = (data.servicios_adicionales ?? []).map(
        (s) => s.servicio_id,
    );
    const espaciosIds = (data.espacios_adicionales ?? []).map(
        (e) => e.espacio_id,
    );

    if (pasoActual === 1) {
        return (
            <div className="flex flex-col gap-6">
                <div className="rounded-3xl border border-border bg-card p-6 shadow-sm">
                    <div className="mb-6 flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <Calendar className="size-5" />
                        </div>
                        <div className="flex flex-col gap-0.5">
                            <h3 className="text-lg font-bold text-foreground">
                                Fecha & Horario de la Reserva
                            </h3>
                            <p className="text-xs text-muted-foreground">
                                Seleccione el día y la franja horaria para su
                                visita a {space.nombre}
                            </p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <Campo>
                            <EtiquetaCampo htmlFor="fecha_check_in">
                                Fecha de Reserva *
                            </EtiquetaCampo>
                            <Input
                                id="fecha_check_in"
                                type="date"
                                value={data.fecha_check_in || ''}
                                min={new Date().toISOString().split('T')[0]}
                                onChange={(
                                    e: React.ChangeEvent<HTMLInputElement>,
                                ) => setData('fecha_check_in', e.target.value)}
                            />
                        </Campo>

                        <Campo>
                            <EtiquetaCampo htmlFor="hora_reserva">
                                Hora Inicio *
                            </EtiquetaCampo>
                            <Input
                                id="hora_reserva"
                                type="time"
                                value={data.hora_reserva || '12:00'}
                                onChange={(
                                    e: React.ChangeEvent<HTMLInputElement>,
                                ) => setData('hora_reserva', e.target.value)}
                            />
                        </Campo>

                        <Campo>
                            <EtiquetaCampo htmlFor="hora_fin">
                                Hora Fin *
                            </EtiquetaCampo>
                            <Input
                                id="hora_fin"
                                type="time"
                                value={data.hora_fin || '14:00'}
                                onChange={(
                                    e: React.ChangeEvent<HTMLInputElement>,
                                ) => setData('hora_fin', e.target.value)}
                            />
                        </Campo>
                    </div>
                </div>

                <div className="rounded-3xl border border-border bg-card p-6 shadow-sm">
                    <div className="mb-6 flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <User className="size-5" />
                        </div>
                        <div className="flex flex-col gap-0.5">
                            <h3 className="text-lg font-bold text-foreground">
                                Datos del Titular
                            </h3>
                            <p className="text-xs text-muted-foreground">
                                Información de contacto principal para la
                                confirmación de reserva
                            </p>
                        </div>
                    </div>

                    <CampoGrupo className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <Campo>
                            <EtiquetaCampo htmlFor="nombre_cliente">
                                Nombre Completo *
                            </EtiquetaCampo>
                            <div className="relative">
                                <User className="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="nombre_cliente"
                                    type="text"
                                    placeholder="Nombre completo"
                                    value={data.nombre_cliente}
                                    onChange={(
                                        e: React.ChangeEvent<HTMLInputElement>,
                                    ) =>
                                        setData(
                                            'nombre_cliente',
                                            e.target.value,
                                        )
                                    }
                                    className="pl-10"
                                />
                            </div>
                        </Campo>

                        <Campo>
                            <EtiquetaCampo htmlFor="telefono_cliente">
                                Teléfono *
                            </EtiquetaCampo>
                            <div className="relative">
                                <Phone className="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="telefono_cliente"
                                    type="tel"
                                    placeholder="+505 8888 8888"
                                    value={data.telefono_cliente}
                                    onChange={(
                                        e: React.ChangeEvent<HTMLInputElement>,
                                    ) =>
                                        setData(
                                            'telefono_cliente',
                                            e.target.value,
                                        )
                                    }
                                    className="pl-10"
                                />
                            </div>
                        </Campo>

                        <Campo>
                            <EtiquetaCampo htmlFor="email_cliente">
                                Correo Electrónico
                            </EtiquetaCampo>
                            <div className="relative">
                                <Mail className="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="email_cliente"
                                    type="email"
                                    placeholder="cliente@ejemplo.com"
                                    value={data.email_cliente}
                                    onChange={(
                                        e: React.ChangeEvent<HTMLInputElement>,
                                    ) =>
                                        setData('email_cliente', e.target.value)
                                    }
                                    className="pl-10"
                                />
                            </div>
                        </Campo>
                    </CampoGrupo>
                </div>
            </div>
        );
    }

    if (pasoActual === 2) {
        return (
            <div className="flex flex-col gap-6">
                <SelectorHuespedes
                    adultos={data.adultos}
                    ninos={data.ninos}
                    capacidadMaxima={space.capacidad || 10}
                    onAdultosChange={(val: number) => setData('adultos', val)}
                    onNinosChange={(val: number) => setData('ninos', val)}
                />

                <div className="rounded-3xl border border-border bg-card p-6 shadow-sm">
                    <div className="mb-4 flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <FileText className="size-5" />
                        </div>
                        <div className="flex flex-col gap-0.5">
                            <h3 className="text-lg font-bold text-foreground">
                                Notas o Solicitudes Especiales
                            </h3>
                            <p className="text-xs text-muted-foreground">
                                Indique preferencias de ubicación, alergias o
                                requerimientos particulares
                            </p>
                        </div>
                    </div>
                    <Campo>
                        <Textarea
                            rows={4}
                            placeholder="Ejemplo: Preferencia mesa cerca de la ventana, silla de bebé..."
                            value={data.notas}
                            onChange={(
                                e: React.ChangeEvent<HTMLTextAreaElement>,
                            ) => setData('notas', e.target.value)}
                        />
                    </Campo>
                </div>
            </div>
        );
    }

    if (pasoActual === 3) {
        return (
            <div className="flex flex-col gap-6">
                <SeccionesAdicionalesReserva
                    opciones={opcionesReserva}
                    serviciosSeleccionados={serviciosIds}
                    espaciosSeleccionados={espaciosIds}
                    promocionId={data.promocion_id}
                    onServiciosChange={(ids) =>
                        setData(
                            'servicios_adicionales',
                            ids.map((id) => ({ servicio_id: id, cantidad: 1 })),
                        )
                    }
                    onEspaciosChange={(ids) =>
                        setData(
                            'espacios_adicionales',
                            ids.map((id) => ({ espacio_id: id, cantidad: 1 })),
                        )
                    }
                    onPromocionChange={(id) => setData('promocion_id', id)}
                />

                <SolicitudCuentaEstancia
                    solicitada={data.solicita_cuenta}
                    limite={data.limite_cuenta_solicitado}
                    alCambiarSolicitud={(val) =>
                        setData('solicita_cuenta', val)
                    }
                    alCambiarLimite={(val) =>
                        setData('limite_cuenta_solicitado', val)
                    }
                />
            </div>
        );
    }

    return (
        <ResumenConfirmacionReserva
            nombreRecurso={space.nombre}
            categoriaRecurso={space.tipo_label || space.tipo}
            nombreCliente={data.nombre_cliente}
            telefonoCliente={data.telefono_cliente}
            fechaCheckIn={data.fecha_check_in}
            fechaCheckOut={data.fecha_check_in}
            nochesCalculadas={1}
            adultos={data.adultos}
            ninos={data.ninos}
            moneda={space.moneda || '$'}
            subtotalEstimado={subtotalEstimado}
            tipoPagoReserva={data.tipo_pago_reserva}
            canalPagoReserva={data.canal_pago_reserva}
            onCanalPagoChange={(canal) => setData('canal_pago_reserva', canal)}
        />
    );
}
