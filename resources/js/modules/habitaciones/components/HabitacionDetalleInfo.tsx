import {
    Users,
    Bed,
    Clock,
    CheckCircle2,
    Info,
    Maximize2,
    Compass,
    MapPin,
    ConciergeBell,
    Layers,
} from 'lucide-react';
import type { HabitacionDetalleData } from '../types';

interface HabitacionDetalleInfoProps {
    room: HabitacionDetalleData;
}

export const HabitacionDetalleInfo = ({ room }: HabitacionDetalleInfoProps) => {
    const servicios = room.serviciosIncluidos || [];
    const equipamiento = room.equipamiento || [];
    const politicas = room.politicas || [];
    const vistas = room.vistas || [];

    return (
        <div className="space-y-8 font-sans">
            {/* Especificaciones Principales de la Habitación desde la BD */}
            <div className="flex flex-wrap gap-4 rounded-3xl border border-border bg-card p-5 shadow-xs">
                {/* Capacidad */}
                <div className="flex min-w-[140px] items-center gap-3">
                    <div className="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:bg-rose-950/60 dark:text-rose-400">
                        <Users className="size-5" />
                    </div>
                    <div>
                        <div className="text-[10px] font-black text-muted-foreground uppercase">
                            Huéspedes
                        </div>
                        <div className="text-xs font-bold text-foreground">
                            {room.capacidad ||
                                (room.adultos || 2) + (room.ninos || 0)}{' '}
                            pers.
                            {room.adultos ? ` (${room.adultos} ad.` : ''}
                            {room.ninos
                                ? `, ${room.ninos} niñ.)`
                                : room.adultos
                                  ? ')'
                                  : ''}
                        </div>
                    </div>
                </div>

                {/* Camas (solo si existe en BD) */}
                {room.camas && (
                    <div className="flex min-w-[140px] items-center gap-3">
                        <div className="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:bg-rose-950/60 dark:text-rose-400">
                            <Bed className="size-5" />
                        </div>
                        <div>
                            <div className="text-[10px] font-black text-muted-foreground uppercase">
                                Camas
                            </div>
                            <div className="text-xs font-bold text-foreground">
                                {room.camas}
                            </div>
                        </div>
                    </div>
                )}

                {/* Medidas (solo si existe en BD) */}
                {room.medidas && (
                    <div className="flex min-w-[140px] items-center gap-3">
                        <div className="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:bg-rose-950/60 dark:text-rose-400">
                            <Maximize2 className="size-5" />
                        </div>
                        <div>
                            <div className="text-[10px] font-black text-muted-foreground uppercase">
                                Espacio
                            </div>
                            <div className="text-xs font-bold text-foreground">
                                {room.medidas}
                            </div>
                        </div>
                    </div>
                )}

                {/* Ubicación / Piso (solo si existe en BD) */}
                {room.ubicacion && (
                    <div className="flex min-w-[140px] items-center gap-3">
                        <div className="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:bg-rose-950/60 dark:text-rose-400">
                            <MapPin className="size-5" />
                        </div>
                        <div>
                            <div className="text-[10px] font-black text-muted-foreground uppercase">
                                Ubicación
                            </div>
                            <div className="text-xs font-bold text-foreground">
                                {room.ubicacion}
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* Vistas Disponibles */}
            {vistas.length > 0 && (
                <div className="flex flex-wrap items-center gap-2">
                    <span className="text-xs font-black text-muted-foreground uppercase">
                        Vistas:
                    </span>
                    {vistas.map((vista, idx) => {
                        const vistaTexto =
                            typeof vista === 'string'
                                ? vista
                                : typeof vista === 'object' && vista !== null
                                  ? (
                                        vista as {
                                            nombre?: string;
                                            label?: string;
                                        }
                                    ).nombre ||
                                    (
                                        vista as {
                                            nombre?: string;
                                            label?: string;
                                        }
                                    ).label ||
                                    ''
                                  : String(vista);

                        return (
                            <div
                                key={idx}
                                className="inline-flex items-center gap-1.5 rounded-full border border-border bg-muted/60 px-3 py-1 text-xs font-bold text-foreground"
                            >
                                <Compass className="size-3.5 text-primary dark:text-rose-400" />
                                <span>{vistaTexto}</span>
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Descripción Real */}
            <div>
                <h2 className="text-lg font-black tracking-tight text-foreground sm:text-xl">
                    Sobre esta habitación
                </h2>
                <p className="mt-3 text-sm leading-relaxed whitespace-pre-line text-muted-foreground">
                    {room.descripcion ||
                        'Habitación confortable con acabados de primera calidad, pensada para su descanso en Hotel Bugambilias Estelí.'}
                </p>
            </div>

            {/* Servicios Asignados desde la BD */}
            {servicios.length > 0 && (
                <div className="border-t border-border pt-8">
                    <div className="flex items-center gap-2">
                        <ConciergeBell className="size-5 text-primary dark:text-rose-400" />
                        <h2 className="text-lg font-black tracking-tight text-foreground sm:text-xl">
                            Servicios y amenidades incluidas
                        </h2>
                    </div>
                    <div className="mt-4 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
                        {servicios.map((srv, idx) => {
                            const nombre =
                                typeof srv === 'string'
                                    ? srv
                                    : typeof srv === 'object' && srv !== null
                                      ? srv.nombre
                                      : '';
                            const descripcion =
                                typeof srv === 'object' && srv !== null
                                    ? srv.descripcion
                                    : null;

                            return (
                                <div
                                    key={idx}
                                    className="flex items-start gap-3 rounded-2xl border border-border/70 bg-card/60 p-4"
                                >
                                    <div className="flex size-8 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary dark:bg-rose-950/60 dark:text-rose-400">
                                        <CheckCircle2 className="size-4" />
                                    </div>
                                    <div>
                                        <div className="text-xs font-black text-foreground">
                                            {nombre}
                                        </div>
                                        {descripcion && (
                                            <p className="mt-0.5 text-[11px] leading-relaxed text-muted-foreground">
                                                {descripcion}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Equipamiento e Inventario Fijo desde la BD */}
            {equipamiento.length > 0 && (
                <div className="border-t border-border pt-8">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <Layers className="size-5 text-primary dark:text-rose-400" />
                            <h2 className="text-lg font-black tracking-tight text-foreground sm:text-xl">
                                Equipamiento y comodidades
                            </h2>
                        </div>
                        <span className="rounded-full border border-border bg-muted/50 px-2.5 py-0.5 text-[11px] font-bold text-muted-foreground">
                            {equipamiento.length}{' '}
                            {equipamiento.length === 1
                                ? 'artículo'
                                : 'artículos'}
                        </span>
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                        Mobiliario, climatización y comodidades asignadas en
                        esta habitación.
                    </p>

                    <div className="mt-4 grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                        {equipamiento.map((item, idx) => {
                            const nombre =
                                typeof item === 'string'
                                    ? item
                                    : typeof item === 'object' &&
                                        item !== null &&
                                        typeof item.nombre === 'string'
                                      ? item.nombre
                                      : '';
                            const categoria =
                                typeof item === 'object' &&
                                item !== null &&
                                typeof item.categoria === 'string'
                                    ? item.categoria
                                    : null;
                            const cantidad =
                                typeof item === 'object' &&
                                item !== null &&
                                typeof item.cantidad === 'number' &&
                                item.cantidad > 1
                                    ? item.cantidad
                                    : null;

                            return (
                                <div
                                    key={idx}
                                    className="group flex items-center justify-between gap-3 rounded-2xl border border-border/80 bg-card/60 p-3.5 shadow-xs transition-all hover:border-primary/40 hover:bg-card"
                                >
                                    <div className="flex items-center gap-3">
                                        <div className="flex size-8 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground dark:bg-rose-950/60 dark:text-rose-400">
                                            <CheckCircle2 className="size-4" />
                                        </div>
                                        <div>
                                            <div className="text-xs font-bold text-foreground">
                                                {nombre}
                                            </div>
                                            {categoria && (
                                                <div className="text-[10px] font-medium text-muted-foreground">
                                                    {categoria}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    {cantidad && (
                                        <span className="rounded-full border border-border/70 bg-background px-2 py-0.5 text-[10px] font-black text-foreground">
                                            ×{cantidad}
                                        </span>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Políticas Reales de la BD */}
            {politicas.length > 0 && (
                <div className="space-y-4 rounded-3xl border border-border bg-muted/30 p-6">
                    <div className="flex items-center gap-2 text-sm font-black text-foreground">
                        <Info className="size-4 text-primary dark:text-rose-400" />
                        <span>Políticas y Normas de Alojamiento</span>
                    </div>
                    <div className="space-y-3 text-xs leading-relaxed text-muted-foreground">
                        {politicas.map((pol, idx) => {
                            const nombre =
                                typeof pol === 'string'
                                    ? pol
                                    : typeof pol === 'object' && pol !== null
                                      ? pol.nombre
                                      : '';
                            const descripcion =
                                typeof pol === 'object' && pol !== null
                                    ? pol.descripcion
                                    : null;
                            const idKey =
                                typeof pol === 'object' &&
                                pol !== null &&
                                pol.id
                                    ? pol.id
                                    : idx;

                            return (
                                <div key={idKey} className="space-y-0.5">
                                    <div className="font-bold text-foreground">
                                        • {nombre}
                                    </div>
                                    {descripcion && (
                                        <div className="pl-3 text-muted-foreground">
                                            {descripcion}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Horarios Estándar */}
            <div className="flex items-center gap-2 text-xs font-bold text-muted-foreground">
                <Clock className="size-4 text-primary dark:text-rose-400" />
                <span>Check-in: 2:00 PM • Check-out: 12:00 PM</span>
            </div>
        </div>
    );
};

export default HabitacionDetalleInfo;
