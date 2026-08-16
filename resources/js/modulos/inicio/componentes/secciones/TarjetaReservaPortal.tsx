import {
    Calendar,
    Users,
    Eye,
    Trash2,
    FileText,
    UserPlus,
    Clock,
} from 'lucide-react';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesTarjetaReservaPortal {
    reserva: ReservaClienteDomain;
    onVerDetalle: (reserva: ReservaClienteDomain) => void;
    onGestionarAcompanantes: (reserva: ReservaClienteDomain) => void;
    onCancelar: (reserva: ReservaClienteDomain) => void;
}

export const TarjetaReservaPortal = ({
    reserva,
    onVerDetalle,
    onGestionarAcompanantes,
    onCancelar,
}: PropiedadesTarjetaReservaPortal) => {
    return (
        <Card className="rounded-3xl border-border/80 bg-card transition-all duration-300 hover:shadow-lg">
            <CardContent className="flex flex-col gap-4 p-6">
                <div className="flex items-center justify-between border-b border-border/50 pb-3">
                    <div>
                        <span className="text-[10px] font-extrabold tracking-widest text-muted-foreground uppercase">
                            Código Reserva
                        </span>
                        <h4 className="text-base font-black text-foreground">
                            {reserva.codigo_reserva}
                        </h4>
                    </div>
                    <Badge
                        variant="outline"
                        className={`text-xs font-extrabold ${reserva.estado_color}`}
                    >
                        {reserva.estado_label}
                    </Badge>
                </div>

                <div className="grid grid-cols-2 gap-3 text-xs">
                    <div className="flex items-center gap-2">
                        <Calendar className="size-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                        <div>
                            <span className="block text-[10px] text-muted-foreground">
                                Check-In
                            </span>
                            <span className="font-bold text-foreground">
                                {reserva.fecha_check_in}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Clock className="size-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                        <div>
                            <span className="block text-[10px] text-muted-foreground">
                                Check-Out
                            </span>
                            <span className="font-bold text-foreground">
                                {reserva.fecha_check_out || 'N/A'}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="flex items-center justify-between border-t border-border/50 pt-2">
                    <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Users className="size-3.5" />
                        <span>
                            {reserva.adultos} Ad.{' '}
                            {reserva.ninos > 0 ? `• ${reserva.ninos} Niñ.` : ''}
                        </span>
                    </div>
                    <div className="text-right">
                        <span className="block text-[10px] text-muted-foreground">
                            Total
                        </span>
                        <span className="text-base font-black text-foreground">
                            ${reserva.total}
                        </span>
                    </div>
                </div>

                <div className="flex flex-wrap items-center gap-2 pt-2">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => onVerDetalle(reserva)}
                        className="gap-1 rounded-xl text-xs font-bold"
                    >
                        <Eye className="size-3.5" /> Detalle
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => onGestionarAcompanantes(reserva)}
                        className="gap-1 rounded-xl text-xs font-bold"
                    >
                        <UserPlus className="size-3.5" /> Acompañantes
                    </Button>
                    {reserva.can_generar_voucher && (
                        <Button
                            variant="outline"
                            size="sm"
                            asChild
                            className="gap-1 rounded-xl text-xs font-bold"
                        >
                            <a
                                href={`/reservas/${reserva.codigo_reserva}/voucher`}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <FileText className="size-3.5" /> Voucher
                            </a>
                        </Button>
                    )}
                    {reserva.estado === 1 && (
                        <Button
                            variant="destructive"
                            size="sm"
                            onClick={() => onCancelar(reserva)}
                            className="ml-auto gap-1 rounded-xl text-xs font-bold"
                        >
                            <Trash2 className="size-3.5" /> Cancelar
                        </Button>
                    )}
                </div>
            </CardContent>
        </Card>
    );
};
