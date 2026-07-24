import { TarjetaCatalogoUnificada } from '@/modules/shared/components/TarjetaCatalogoUnificada';
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
const badgeDisponibilidad = (disponibles?: number, total?: number) => {
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
};
const TarjetaHabitacion = ({ habitacion }: TarjetaHabitacionProps) => {
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

    return (
        <TarjetaCatalogoUnificada
            id={Number(habitacion.id || 0)}
            slug={habitacion.slug}
            nombre={nombreHabitacion}
            codigo={habitacion.codigo}
            categoria={habitacion.categoria || badge.texto}
            descripcion={
                habitacion.descripcion ||
                'Ambiente confortable con acabados de primera calidad, pensado para su descanso en Estelí.'
            }
            precio={precioMostrar}
            moneda={habitacion.moneda || '$'}
            tipoTarifaLabel="Tarifa por noche"
            imagen={habitacion.imagen || '/images/hero-main.webp'}
            capacidadHuespedes={
                typeof habitacion.capacidad === 'number'
                    ? habitacion.capacidad
                    : undefined
            }
            hrefDetalle={urlHabitacion}
        />
    );
};
export default TarjetaHabitacion;
