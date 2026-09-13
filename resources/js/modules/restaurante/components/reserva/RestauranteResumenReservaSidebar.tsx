import { ArrowRight } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { PlatoPreorden } from '../../hooks/useReservaFlujoReserva';
import type { DisponibilidadMesasResponse } from '../../services/restauranteService';

interface RestauranteResumenReservaSidebarProps {
    nombreRestaurante: string;
    fechaSeleccionada: string;
    horaSeleccionada: string;
    adultosSeleccionados: number;
    disponibilidad: DisponibilidadMesasResponse | null;
    platosPreorden: PlatoPreorden[];
    subtotalPreorden: number;
    pasoActual: 1 | 2 | 3;
    irPaso: (paso: 1 | 2 | 3) => void;
}

export const RestauranteResumenReservaSidebar = ({
    nombreRestaurante,
    fechaSeleccionada,
    horaSeleccionada,
    adultosSeleccionados,
    disponibilidad,
    platosPreorden,
    subtotalPreorden,
    pasoActual,
    irPaso,
}: RestauranteResumenReservaSidebarProps) => {
    const tieneMesasUnidas = Boolean(
        disponibilidad?.requiere_union &&
        disponibilidad?.mesas_sugeridas_union &&
        disponibilidad.mesas_sugeridas_union.length > 1,
    );

    const mesaIndividualAsignada = !disponibilidad?.requiere_union
        ? disponibilidad?.mesa_asignada
        : null;

    const mesaAsignada = mesaIndividualAsignada?.nombre
        ? mesaIndividualAsignada.nombre
        : tieneMesasUnidas
          ? disponibilidad?.mesas_sugeridas_union
                ?.map((m) => m.nombre)
                .join(' + ')
          : 'Mesa seleccionada en plano';

    return (
        <div className="sticky top-6 space-y-6 rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
            <h4 className="border-b border-border/60 pb-3 text-sm font-black text-foreground">
                Resumen de tu Reserva
            </h4>

            <div className="space-y-3 text-xs">
                <div className="flex items-center justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">Restaurante:</span>
                    <span className="font-bold">{nombreRestaurante}</span>
                </div>
                <div className="flex items-center justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">Fecha:</span>
                    <span className="font-bold">
                        {fechaSeleccionada || 'Hoy'}
                    </span>
                </div>
                <div className="flex items-center justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">Horario:</span>
                    <span className="font-bold">
                        {horaSeleccionada || '13:00'}
                    </span>
                </div>
                <div className="flex items-center justify-between border-b border-border/40 pb-2">
                    <span className="text-muted-foreground">Comensales:</span>
                    <span className="font-bold">
                        {adultosSeleccionados} personas
                    </span>
                </div>

                <div className="space-y-1 rounded-2xl border border-primary/20 bg-primary/5 p-3">
                    <span className="block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                        Mesa Asignada
                    </span>
                    <span className="block text-xs font-black text-primary dark:text-rose-400">
                        {mesaAsignada}
                    </span>
                    <span className="text-[11px] text-muted-foreground">
                        Zona:{' '}
                        {disponibilidad?.zona_asignada || 'Salón Interior'}
                    </span>
                </div>

                {platosPreorden.length > 0 && (
                    <div className="space-y-1.5 rounded-2xl border border-border/60 bg-muted/20 p-3">
                        <span className="block text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                            Pre-orden ({platosPreorden.length} platos)
                        </span>
                        {platosPreorden.map((item) => (
                            <div
                                key={item.plato.id}
                                className="flex justify-between text-[11px]"
                            >
                                <span className="max-w-[140px] truncate">
                                    {item.cantidad}x {item.plato.nombre}
                                </span>
                                <span className="font-bold">
                                    C${' '}
                                    {(
                                        (item.plato.precio ?? 0) * item.cantidad
                                    ).toFixed(2)}
                                </span>
                            </div>
                        ))}
                        <div className="flex justify-between border-t border-border/40 pt-1 text-xs font-black">
                            <span>Subtotal:</span>
                            <span>C$ {subtotalPreorden.toFixed(2)}</span>
                        </div>
                    </div>
                )}
            </div>

            <div className="flex flex-col gap-2 pt-2">
                {pasoActual === 1 && (
                    <Button
                        type="button"
                        onClick={() => irPaso(2)}
                        className="h-11 w-full cursor-pointer rounded-2xl text-xs font-black shadow-md"
                    >
                        Siguiente: Pre-ordenar Menú
                        <ArrowRight className="ml-2 size-4" />
                    </Button>
                )}
                {pasoActual === 2 && (
                    <div className="flex gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => irPaso(1)}
                            className="h-11 flex-1 cursor-pointer rounded-2xl text-xs font-bold"
                        >
                            Atrás
                        </Button>
                        <Button
                            type="button"
                            onClick={() => irPaso(3)}
                            className="h-11 flex-1 cursor-pointer rounded-2xl text-xs font-black shadow-md"
                        >
                            Siguiente: Datos
                            <ArrowRight className="ml-2 size-4" />
                        </Button>
                    </div>
                )}
                {pasoActual === 3 && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => irPaso(2)}
                        className="h-11 w-full cursor-pointer rounded-2xl text-xs font-bold"
                    >
                        Atrás
                    </Button>
                )}
            </div>
        </div>
    );
};

export default RestauranteResumenReservaSidebar;
