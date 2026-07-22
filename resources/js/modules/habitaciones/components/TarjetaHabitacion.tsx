import { Link } from '@inertiajs/react';
import { Users, BedDouble, ArrowRight } from 'lucide-react';

export interface HabitacionGrupo {
    id: number | string;
    codigo?: string;
    numero?: number;
    slug?: string;
    nombre?: string;
    name?: string;
    descripcion?: string | null;
    categoria?: string;
    precio_desde?: number | null;
    precio?: number | null;
    moneda?: string;
    imagen?: string;
    disponibles?: number;
    total?: number;
    capacidad?: string | number;
    ids?: number[];
}

export type RoomItem = HabitacionGrupo;

interface TarjetaHabitacionProps {
    habitacion?: HabitacionGrupo;
}

function badgeDisponibilidad(disponibles?: number, total?: number) {
    if (disponibles === undefined || total === undefined) {
        return {
            texto: 'Disponible',
            color: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
        };
    }

    if (disponibles === 0) {
        return {
            texto: 'Agotado',
            color: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800',
        };
    }

    if (disponibles === 1) {
        return {
            texto: 'Última disponible',
            color: 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
        };
    }

    if (disponibles <= 3) {
        return {
            texto: `${disponibles} disponibles`,
            color: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
        };
    }

    return {
        texto: `${disponibles} de ${total} disponibles`,
        color: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
    };
}

export default function TarjetaHabitacion({
    habitacion,
}: TarjetaHabitacionProps) {
    if (!habitacion) {
        return null;
    }

    const nombreHabitacion =
        habitacion.nombre || habitacion.name || 'Habitación Bugambilias';
    const precioMostrar = habitacion.precio_desde ?? habitacion.precio;
    const badge = badgeDisponibilidad(habitacion.disponibles, habitacion.total);
    const urlHabitacion = habitacion.slug
        ? `/habitaciones/${habitacion.slug}`
        : `/habitaciones/${habitacion.ids?.[0] || habitacion.id}`;
    const capacidadTexto = habitacion.capacidad
        ? `Hasta ${habitacion.capacidad} personas`
        : 'Confort Garantizado';

    return (
        <article className="group shadow-airbnb hover:shadow-airbnb-hover flex h-full flex-col overflow-hidden rounded-3xl border border-border/80 bg-card font-sans transition-all duration-300 hover:-translate-y-1">
            <div className="relative aspect-[4/3] overflow-hidden bg-muted/40">
                <Link href={urlHabitacion} className="block h-full w-full">
                    <img
                        src={habitacion.imagen || '/images/hero-main.jpg'}
                        alt={nombreHabitacion}
                        className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                    />
                </Link>

                <div className="absolute top-3 left-3 z-10">
                    <span
                        className={`inline-block rounded-full border px-3 py-1 text-[10px] font-extrabold tracking-wider uppercase ${badge.color}`}
                    >
                        {badge.texto}
                    </span>
                </div>

                <div className="pointer-events-none absolute right-3 bottom-3 left-3 z-10 flex items-center justify-between">
                    <span className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-black/60 px-3 py-1 text-[10px] font-bold text-white backdrop-blur-md">
                        <Users className="h-3 w-3 text-amber-400" />
                        {capacidadTexto}
                    </span>
                </div>
            </div>

            <div className="flex flex-grow flex-col justify-between p-6">
                <div>
                    <Link href={urlHabitacion}>
                        <h3 className="mb-2 line-clamp-1 text-base font-extrabold text-foreground transition-colors group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                            {nombreHabitacion}
                        </h3>
                    </Link>

                    <p className="mb-3 line-clamp-2 text-xs leading-relaxed font-medium text-muted-foreground">
                        {habitacion.descripcion ||
                            'Ambiente confortable con acabados de primera calidad, pensado para su descanso en Estelí.'}
                    </p>

                    <div className="mb-4 flex items-center gap-3 text-xs font-semibold text-muted-foreground/80">
                        <span className="inline-flex items-center gap-1">
                            <BedDouble className="h-3.5 w-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                            {habitacion.total
                                ? `${habitacion.total} ${habitacion.total === 1 ? 'habitación' : 'habitaciones'}`
                                : 'Habitación Privada'}
                        </span>
                    </div>
                </div>

                <div className="flex items-baseline justify-between border-t border-border/40 pt-4">
                    <div>
                        <span className="block text-xs font-semibold text-muted-foreground">
                            Desde
                        </span>
                        <span className="text-xl font-black text-foreground">
                            {habitacion.moneda || '$'}
                            {precioMostrar ?? '—'}
                            <span className="text-xs font-semibold text-muted-foreground">
                                {' '}
                                / noche
                            </span>
                        </span>
                    </div>

                    <Link
                        href={urlHabitacion}
                        className="shadow-airbnb hover:shadow-airbnb-hover inline-flex items-center gap-1.5 rounded-xl bg-bugambilia-600 px-4 py-2 text-xs font-bold text-white transition-all hover:bg-bugambilia-700"
                    >
                        Ver disponibilidad
                        <ArrowRight className="h-3.5 w-3.5" />
                    </Link>
                </div>
            </div>
        </article>
    );
}
