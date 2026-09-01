import { Link } from '@inertiajs/react';
import { ChevronRight, BedDouble } from 'lucide-react';
import type { PortalReservaResumen } from '../../types';

interface HistorialResumenProps {
    reservas: PortalReservaResumen[];
}

export const HistorialResumen = ({ reservas }: HistorialResumenProps) => {
    if (reservas.length === 0) {
        return (
            <div className="rounded-3xl border border-dashed border-border/80 p-8 text-center">
                <BedDouble className="mx-auto size-10 text-muted-foreground/50" />
                <p className="mt-2 text-sm font-semibold text-muted-foreground">
                    No tienes estancias pasadas registradas.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-3">
            {reservas.slice(0, 5).map((r) => (
                <Link
                    key={r.id}
                    href={`/portal/reservas/${r.id}`}
                    className="flex items-center justify-between gap-4 rounded-2xl border border-border/60 bg-card p-4 transition-colors hover:bg-secondary/40"
                >
                    <div className="flex min-w-0 items-center gap-3.5">
                        <div className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-secondary text-muted-foreground">
                            <BedDouble className="size-5" />
                        </div>
                        <div className="min-w-0">
                            <h5 className="truncate text-sm font-bold text-foreground">
                                {r.recurso.nombre}
                            </h5>
                            <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                <span className="font-mono">
                                    {r.codigo_reserva}
                                </span>
                                <span>·</span>
                                <span>{r.fecha_check_in || 'N/D'}</span>
                            </div>
                        </div>
                    </div>

                    <div className="flex shrink-0 items-center gap-3">
                        <span className="rounded-full bg-secondary px-2.5 py-1 text-xs font-semibold text-muted-foreground">
                            {r.estado_label}
                        </span>
                        <ChevronRight className="size-4 text-muted-foreground" />
                    </div>
                </Link>
            ))}
        </div>
    );
};
