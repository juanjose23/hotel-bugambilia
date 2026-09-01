import { ShieldCheck, Lock } from 'lucide-react';
import type { RoomItem } from '@/modules/shared/types';

interface ReservaResumenSidebarProps {
    room: RoomItem & {
        precio?: number | string;
        precio_desde?: number | string;
        moneda?: string;
        categoria?: string;
    };
    imagenPrincipal: string;
    checkIn: string;
    checkOut: string;
    noches: number;
    adultos: number;
    ninos: number;
    precioNoche: number;
    subtotalHabitacion: number;
    subtotalServicios: number;
    montoDescuento: number;
    totalNeto: number;
    montoACobrarAhora: number;
    moneda: string;
}

export const ReservaResumenSidebar = ({
    room,
    imagenPrincipal,
    checkIn,
    checkOut,
    noches,
    adultos,
    ninos,
    precioNoche,
    subtotalHabitacion,
    subtotalServicios,
    montoDescuento,
    totalNeto,
    montoACobrarAhora,
    moneda,
}: ReservaResumenSidebarProps) => {
    return (
        <div className="sticky top-24 space-y-5 rounded-3xl border border-border bg-card p-6 shadow-xl">
            {/* Cabecera de la Suite */}
            <div className="flex gap-4">
                <img
                    src={imagenPrincipal}
                    alt={room.nombre}
                    className="size-20 rounded-2xl border border-border object-cover"
                />
                <div className="min-w-0 flex-1">
                    <span className="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-black text-primary uppercase">
                        {room.categoria || 'Suite Boutique'}
                    </span>
                    <h3 className="mt-1 truncate text-sm font-black text-foreground">
                        {room.nombre}
                    </h3>
                    <p className="mt-0.5 text-xs font-bold text-muted-foreground">
                        {moneda}
                        {precioNoche}{' '}
                        <span className="text-[11px] font-normal">/ noche</span>
                    </p>
                </div>
            </div>

            {/* Resumen de Fechas */}
            <div className="space-y-2 rounded-2xl border border-border bg-background p-4 text-xs">
                <div className="flex justify-between text-muted-foreground">
                    <span>Llegada (Check-in):</span>
                    <span className="font-black text-foreground">
                        {checkIn || 'Sin seleccionar'}
                    </span>
                </div>
                <div className="flex justify-between text-muted-foreground">
                    <span>Salida (Check-out):</span>
                    <span className="font-black text-foreground">
                        {checkOut || 'Sin seleccionar'}
                    </span>
                </div>
                <div className="flex justify-between text-muted-foreground">
                    <span>Duración de estancia:</span>
                    <span className="font-bold text-foreground">
                        {noches} {noches === 1 ? 'noche' : 'noches'}
                    </span>
                </div>
                <div className="flex justify-between text-muted-foreground">
                    <span>Huéspedes:</span>
                    <span className="font-bold text-foreground">
                        {adultos} {adultos === 1 ? 'adulto' : 'adultos'}
                        {ninos > 0 ? `, ${ninos} niños` : ''}
                    </span>
                </div>
            </div>

            {/* Desglose Económico */}
            <div className="space-y-2.5 rounded-2xl border border-border/80 bg-muted/25 p-4 text-xs">
                <div className="flex justify-between text-muted-foreground">
                    <span>
                        Suite ({noches} {noches === 1 ? 'noche' : 'noches'}):
                    </span>
                    <span className="font-bold text-foreground">
                        {moneda}
                        {subtotalHabitacion.toFixed(2)}
                    </span>
                </div>

                {subtotalServicios > 0 && (
                    <div className="flex justify-between text-muted-foreground">
                        <span>Servicios adicionales:</span>
                        <span className="font-bold text-foreground">
                            +{moneda}
                            {subtotalServicios.toFixed(2)}
                        </span>
                    </div>
                )}

                {montoDescuento > 0 && (
                    <div className="flex justify-between font-bold text-emerald-600 dark:text-emerald-400">
                        <span>Descuento aplicado:</span>
                        <span>
                            -{moneda}
                            {montoDescuento.toFixed(2)}
                        </span>
                    </div>
                )}

                <div className="flex justify-between border-t border-border pt-2 text-sm font-black text-foreground">
                    <span>Total Estancia:</span>
                    <span className="text-base text-primary dark:text-rose-400">
                        {moneda}
                        {totalNeto.toFixed(2)}
                    </span>
                </div>

                <div className="flex justify-between border-t border-dashed border-border pt-2 text-xs font-black">
                    <span className="text-foreground">Monto a pagar hoy:</span>
                    <span className="font-extrabold text-primary dark:text-rose-300">
                        {moneda}
                        {montoACobrarAhora.toFixed(2)}
                    </span>
                </div>
            </div>

            {/* Garantías y Confianza */}
            <div className="space-y-2 text-[11px] font-bold text-muted-foreground">
                <div className="flex items-center gap-2">
                    <ShieldCheck className="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                    <span>
                        Mejor precio garantizado directamente con el hotel
                    </span>
                </div>
                <div className="flex items-center gap-2">
                    <Lock className="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                    <span>
                        Confirmación inmediata sin comisiones de intermediarios
                    </span>
                </div>
            </div>
        </div>
    );
};

export default ReservaResumenSidebar;
