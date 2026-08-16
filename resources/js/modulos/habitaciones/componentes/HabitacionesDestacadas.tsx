import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    BadgeCheck,
    BedDouble,
    Hotel,
    Crown,
    Home,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import type { HabitacionGrupo } from '../interfaces/habitacionInterfaces';
import { TarjetaHabitacion } from './TarjetaHabitacion';

interface PropiedadesHabitacionesDestacadas {
    rooms?: HabitacionGrupo[];
    categories?: string[];
}

const ICONOS_CATEGORIA: Record<
    string,
    React.ComponentType<{ className?: string }>
> = {
    Todas: Hotel,
    Boutique: BedDouble,
    Deluxe: Crown,
    Suite: Home,
};

export const HabitacionesDestacadas = ({
    rooms = [],
    categories = [],
}: PropiedadesHabitacionesDestacadas) => {
    const [selectedCat, setSelectedCat] = useState('Todas');

    const availableCategories = [
        'Todas',
        ...Array.from(
            new Set(
                categories.length
                    ? categories
                    : rooms
                          .map((r) => r.categoria)
                          .filter((c): c is string => typeof c === 'string'),
            ),
        ),
    ];

    const filteredRooms =
        selectedCat === 'Todas'
            ? rooms
            : rooms.filter(
                  (r) =>
                      r.categoria &&
                      r.categoria
                          .toLowerCase()
                          .includes(selectedCat.toLowerCase()),
              );

    if (rooms.length === 0) {
        return null;
    }

    return (
        <section className="overflow-hidden border-b border-border/40 bg-background py-20 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Header de la sección */}
                <div className="mb-12 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                    <div className="max-w-2xl">
                        <Badge
                            variant="outline"
                            className="mb-3 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                        >
                            <BadgeCheck
                                className="mr-1 size-3.5"
                                data-icon="inline-start"
                            />{' '}
                            Catálogo de Habitaciones
                        </Badge>
                        <h2 className="text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl md:text-5xl">
                            Habitaciones{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                Exclusivas
                            </span>
                        </h2>
                        <p className="mt-2 text-base font-medium text-muted-foreground sm:text-lg">
                            Descubra estancias diseñadas para el máximo
                            descanso, confort boutique y privacidad.
                        </p>
                    </div>

                    <Button
                        asChild
                        variant="link"
                        className="gap-2 p-0 font-extrabold text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        <Link href="/habitaciones" prefetch>
                            Explorar catálogo completo
                            <ArrowRight
                                className="size-4"
                                data-icon="inline-end"
                            />
                        </Link>
                    </Button>
                </div>

                {/* Pills de Categoría (Filtro) */}
                {availableCategories.length > 1 && (
                    <div className="no-scrollbar mb-8 flex items-center gap-2 overflow-x-auto border-b border-border/40 pb-6">
                        {availableCategories.map((cat) => {
                            const Icon = ICONOS_CATEGORIA[cat] || Hotel;
                            const isSelected = selectedCat === cat;

                            return (
                                <button
                                    key={cat}
                                    type="button"
                                    onClick={() => setSelectedCat(cat)}
                                    className={`inline-flex shrink-0 items-center gap-2 rounded-full px-5 py-2.5 text-xs font-black tracking-wide uppercase transition-all duration-200 ${
                                        isSelected
                                            ? 'bg-bugambilia-600 text-white shadow-md shadow-bugambilia-600/20 dark:bg-bugambilia-500'
                                            : 'border border-border/60 bg-card text-muted-foreground hover:border-border hover:bg-muted/50 hover:text-foreground'
                                    }`}
                                >
                                    <Icon className="size-4" />
                                    <span>{cat}</span>
                                </button>
                            );
                        })}
                    </div>
                )}

                {/* Cuadrícula de Tarjetas de Habitaciones */}
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {filteredRooms.slice(0, 6).map((r) => (
                        <TarjetaHabitacion key={r.id} habitacion={r} />
                    ))}
                </div>
            </div>
        </section>
    );
};

export default HabitacionesDestacadas;
