import { Calendar, UserCheck, ChevronRight, Hash } from 'lucide-react';
import React from 'react';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { Boton } from '@/modulos/compartido/ui/boton';
import { Insignia } from '@/modulos/compartido/ui/insignia';
import {
    Tarjeta,
    TarjetaCabecera,
    TarjetaTitulo,
    TarjetaContenido,
    TarjetaPie,
} from '@/modulos/compartido/ui/tarjeta';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';

interface PropiedadesTarjetaClienteReserva {
    reserva: ReservaClienteDomain;
    onVerDetalle?: (reserva: ReservaClienteDomain) => void;
}

export function TarjetaClienteReserva({
    reserva,
    onVerDetalle,
}: PropiedadesTarjetaClienteReserva) {
    return (
        <Tarjeta className="group transition-all duration-300 hover:-translate-y-1">
            <TarjetaCabecera className="flex flex-row items-center justify-between">
                <div className="flex items-center gap-2">
                    <span className="flex size-8 items-center justify-center rounded-xl bg-primary/10 text-xs font-bold text-primary">
                        <Hash className="size-3.5" />
                    </span>
                    <span className="font-mono text-xs font-bold text-foreground">
                        {reserva.codigo_reserva}
                    </span>
                </div>
                <Insignia
                    variant="outline"
                    className="rounded-full px-3 py-0.5 text-xs font-semibold tracking-wider uppercase"
                >
                    {reserva.estado_label}
                </Insignia>
            </TarjetaCabecera>

            <TarjetaContenido className="flex flex-col gap-3 pt-2">
                <TarjetaTitulo className="text-base font-bold text-foreground">
                    {reserva.detalles || reserva.tipo_reserva_label}
                </TarjetaTitulo>

                <div className="grid grid-cols-2 gap-3 rounded-2xl bg-muted/40 p-3 text-xs">
                    <div className="flex items-center gap-2 text-muted-foreground">
                        <Calendar className="size-4 shrink-0 text-primary" />
                        <span>
                            {reserva.fecha_check_in}
                            {reserva.fecha_check_out
                                ? ` - ${reserva.fecha_check_out}`
                                : ''}
                        </span>
                    </div>

                    <div className="flex items-center gap-2 text-muted-foreground">
                        <UserCheck className="size-4 shrink-0 text-primary" />
                        <span>
                            {reserva.adultos} adulto(s), {reserva.ninos} niño(s)
                        </span>
                    </div>
                </div>

                {reserva.notas && (
                    <p className="rounded-xl border border-border/40 bg-background p-2.5 text-xs text-muted-foreground italic">
                        "{reserva.notas}"
                    </p>
                )}
            </TarjetaContenido>

            <TarjetaPie className="flex items-center justify-between">
                <div className="flex flex-col">
                    <span className="text-[10px] font-semibold text-muted-foreground uppercase">
                        Total
                    </span>
                    <span className="text-base font-black text-foreground">
                        ${formatearNumero(Number(reserva.total))}
                    </span>
                </div>

                {onVerDetalle && (
                    <Boton
                        variant="secondary"
                        size="sm"
                        onClick={() => onVerDetalle(reserva)}
                        className="gap-1 rounded-full text-xs"
                    >
                        Ver Detalle
                        <ChevronRight className="size-3.5" />
                    </Boton>
                )}
            </TarjetaPie>
        </Tarjeta>
    );
}
