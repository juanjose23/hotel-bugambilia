import { Plus, Minus } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { ServicioAdicionalItem } from '../types';

interface ReservaPasoServiciosProps {
    serviciosDisponibles?: ServicioAdicionalItem[];
    serviciosSeleccionados: { servicio_id: number; cantidad: number }[];
    toggleServicio: (servicioId: number) => void;
    cambiarCantidadServicio: (servicioId: number, cantidad: number) => void;
}

export const ReservaPasoServicios = ({
    serviciosDisponibles = [],
    serviciosSeleccionados,
    toggleServicio,
    cambiarCantidadServicio,
}: ReservaPasoServiciosProps) => {
    return (
        <div className="animate-in fade-in space-y-6 duration-200">
            <div className="space-y-4 rounded-3xl border border-border bg-card p-6 shadow-sm">
                <div>
                    <h2 className="text-lg font-black tracking-tight text-foreground">
                        Mejora tu estancia con servicios adicionales
                    </h2>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        Agrega comodidades opcionales a tu reserva con
                        confirmación anticipada.
                    </p>
                </div>

                {serviciosDisponibles && serviciosDisponibles.length > 0 ? (
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {serviciosDisponibles.map((srv) => {
                            const sel = serviciosSeleccionados.find(
                                (s) => s.servicio_id === srv.id,
                            );
                            const estaSeleccionado = Boolean(sel);

                            return (
                                <div
                                    key={srv.id}
                                    className={`flex flex-col justify-between rounded-2xl border p-4 transition-all ${
                                        estaSeleccionado
                                            ? 'border-primary bg-primary/5 dark:bg-rose-950/20'
                                            : 'border-border bg-background hover:border-primary/40'
                                    }`}
                                >
                                    <div>
                                        <div className="flex items-start justify-between gap-2">
                                            <div className="text-xs font-black text-foreground">
                                                {srv.nombre}
                                            </div>
                                            <span className="rounded-full bg-muted px-2 py-0.5 text-[11px] font-black text-foreground">
                                                {srv.moneda || '$'}
                                                {Number(srv.precio).toFixed(2)}
                                            </span>
                                        </div>
                                        {srv.descripcion && (
                                            <p className="mt-1 line-clamp-2 text-[11px] text-muted-foreground">
                                                {srv.descripcion}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mt-4 flex items-center justify-between border-t border-border/60 pt-3">
                                        <Button
                                            type="button"
                                            variant={
                                                estaSeleccionado
                                                    ? 'default'
                                                    : 'outline'
                                            }
                                            size="sm"
                                            onClick={() =>
                                                toggleServicio(srv.id)
                                            }
                                            className="h-8 rounded-xl text-xs font-bold"
                                        >
                                            {estaSeleccionado
                                                ? 'Agregado'
                                                : 'Agregar'}
                                        </Button>

                                        {estaSeleccionado && (
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>
                                                        cambiarCantidadServicio(
                                                            srv.id,
                                                            (sel?.cantidad ||
                                                                1) - 1,
                                                        )
                                                    }
                                                    className="size-7 rounded-lg"
                                                >
                                                    <Minus className="size-3" />
                                                </Button>
                                                <span className="text-xs font-black">
                                                    {sel?.cantidad || 1}
                                                </span>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>
                                                        cambiarCantidadServicio(
                                                            srv.id,
                                                            (sel?.cantidad ||
                                                                1) + 1,
                                                        )
                                                    }
                                                    className="size-7 rounded-lg"
                                                >
                                                    <Plus className="size-3" />
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <p className="text-xs text-muted-foreground">
                        No hay servicios adicionales configurados actualmente.
                    </p>
                )}
            </div>
        </div>
    );
};

export default ReservaPasoServicios;
