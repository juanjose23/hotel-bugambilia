import { Link, router } from '@inertiajs/react';
import {
    Sparkles,
    UtensilsCrossed,
    ShieldCheck,
    Wifi,
    FilterX,
    Search,
} from 'lucide-react';
import { useState } from 'react';
import TarjetaHabitacion from '@/modules/rooms/components/TarjetaHabitacion';
import type { RoomItem } from '@/modules/rooms/components/TarjetaHabitacion';
import { PaginadorPublico } from '@/modules/shared/components/PaginadorPublico';
import type { DatosPaginacion } from '@/modules/shared/types';
interface SeccionHabitacionesProps {
    rooms?: RoomItem[];
    categorias?: string[];
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: DatosPaginacion;
}
const SeccionHabitaciones = ({
    rooms = [],
    categorias = [],
    selectedCategory = null,
    searchQuery = '',
    pagination,
}: SeccionHabitacionesProps) => {
    const [term, setTerm] = useState(searchQuery);
    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            '/habitaciones',
            {
                categoria: selectedCategory || undefined,
                buscar: term || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    return (
        <section className="pt-2 pb-16 font-sans sm:pt-4 md:pb-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Banner Hero Fotográfico */}
                <div className="relative mb-8 flex min-h-[280px] items-center overflow-hidden rounded-3xl border border-border/60 shadow-2xl sm:min-h-[340px] md:mb-12 md:min-h-[380px]">
                    <img
                        src="/images/hero-main.webp"
                        alt="Habitaciones Hotel Bugambilias"
                        className="absolute inset-0 h-full w-full scale-105 object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/95 via-black/75 to-black/30" />

                    <div className="relative z-10 max-w-2xl p-6 font-sans text-white sm:p-10 md:p-14">
                        <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/20 px-3.5 py-1 text-xs font-extrabold tracking-widest text-amber-300 uppercase shadow-sm backdrop-blur-md sm:mb-4">
                            <Sparkles className="h-3.5 w-3.5 text-amber-400" />
                            Estancia Exclusiva en Estelí
                        </div>
                        <h1 className="mb-3 text-3xl leading-tight font-black tracking-tight text-white sm:text-4xl md:text-5xl">
                            Habitaciones &{' '}
                            <span className="font-serif font-normal text-amber-400 italic">
                                Suites
                            </span>
                        </h1>
                        <p className="mb-6 text-xs leading-relaxed font-medium text-gray-200 sm:text-sm md:text-base">
                            Seleccione la opción ideal para su descanso. Cada
                            habitación combina elegancia colonial, tecnología
                            moderna y máxima comodidad.
                        </p>

                        <div className="flex flex-wrap gap-4 text-xs font-bold text-amber-200">
                            <span className="flex items-center gap-1.5 rounded-full border border-white/10 bg-black/40 px-3 py-1.5">
                                <Wifi className="h-3.5 w-3.5 text-amber-400" />
                                Wi-Fi de Alta Velocidad
                            </span>
                            <span className="flex items-center gap-1.5 rounded-full border border-white/10 bg-black/40 px-3 py-1.5">
                                <UtensilsCrossed className="h-3.5 w-3.5 text-amber-400" />
                                Desayuno Disponible
                            </span>
                            <span className="flex items-center gap-1.5 rounded-full border border-white/10 bg-black/40 px-3 py-1.5">
                                <ShieldCheck className="h-3.5 w-3.5 text-amber-400" />
                                Seguridad 24/7
                            </span>
                        </div>
                    </div>
                </div>

                {/* Buscador & Barra de Filtros */}
                <div className="mb-8 flex flex-col items-center justify-between gap-4 rounded-3xl border border-border bg-card p-4 shadow-sm md:flex-row">
                    <form
                        onSubmit={handleSearchSubmit}
                        className="relative flex w-full items-center md:w-96"
                    >
                        <Search className="absolute left-4 h-4 w-4 text-muted-foreground" />
                        <input
                            type="text"
                            value={term}
                            onChange={(e) => setTerm(e.target.value)}
                            placeholder="Buscar habitación por tipo o nombre..."
                            className="w-full rounded-2xl border border-border bg-background py-2.5 pr-24 pl-11 text-xs font-semibold text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-bugambilia-500/50 focus:outline-none"
                        />
                        <button
                            type="submit"
                            className="absolute right-1.5 cursor-pointer rounded-xl bg-bugambilia-600 px-3.5 py-1.5 text-xs font-bold text-white transition-all hover:bg-bugambilia-700"
                        >
                            Buscar
                        </button>
                    </form>

                    <div className="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                        {selectedCategory && (
                            <span className="rounded-full bg-bugambilia-500/10 px-3 py-1 font-bold text-bugambilia-600 dark:text-bugambilia-400">
                                Categoría: {selectedCategory}
                            </span>
                        )}
                        {searchQuery && (
                            <span className="rounded-full bg-amber-500/10 px-3 py-1 font-bold text-amber-600 dark:text-amber-400">
                                Búsqueda: "{searchQuery}"
                            </span>
                        )}
                        {(selectedCategory || searchQuery) && (
                            <Link
                                href="/habitaciones"
                                preserveScroll
                                only={[
                                    'rooms',
                                    'pagination',
                                    'categorias',
                                    'selectedCategory',
                                    'searchQuery',
                                ]}
                                className="rounded-full border border-border bg-card p-2 text-muted-foreground transition-colors hover:text-bugambilia-600 dark:hover:text-bugambilia-400"
                                title="Limpiar todos los filtros"
                            >
                                <FilterX className="h-4 w-4" />
                            </Link>
                        )}
                    </div>
                </div>

                {/* Categorías / Filtros Pill Badge Bar */}
                {categorias.length > 0 && (
                    <div className="mb-10 scrollbar-none overflow-x-auto pt-1 pb-3">
                        <div className="flex min-w-max items-center gap-2">
                            <Link
                                href="/habitaciones?categoria=TODOS"
                                className={`rounded-full border px-5 py-2.5 text-xs font-extrabold transition-all duration-200 ${
                                    !selectedCategory ||
                                    selectedCategory === 'TODOS'
                                        ? 'shadow-airbnb border-bugambilia-600 bg-bugambilia-600 text-white'
                                        : 'border-border bg-card text-muted-foreground hover:border-foreground/40 hover:text-foreground'
                                }`}
                            >
                                Todas las Categorías
                            </Link>
                            {categorias.map((cat) => {
                                const isSelected = selectedCategory === cat;

                                return (
                                    <Link
                                        key={cat}
                                        href={`/habitaciones?categoria=${encodeURIComponent(cat)}`}
                                        className={`rounded-full border px-5 py-2.5 text-xs font-extrabold transition-all duration-200 ${
                                            isSelected
                                                ? 'shadow-airbnb scale-105 border-bugambilia-600 bg-bugambilia-600 text-white'
                                                : 'border-border bg-card text-muted-foreground hover:border-foreground/40 hover:text-foreground'
                                        }`}
                                    >
                                        {cat}
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                )}

                {/* Grid / Carrusel Móvil de Habitaciones */}
                {rooms.length > 0 ? (
                    <div className="-mx-4 flex snap-x snap-mandatory scrollbar-none gap-6 overflow-x-auto px-4 pb-6 md:mx-0 md:grid md:grid-cols-2 md:overflow-visible md:px-0 lg:grid-cols-3">
                        {rooms.map((room) => (
                            <div
                                key={room.id}
                                className="w-[85vw] max-w-[360px] shrink-0 snap-center md:w-auto md:max-w-none md:shrink"
                            >
                                <TarjetaHabitacion habitacion={room} />
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="shadow-airbnb rounded-3xl border border-border/80 bg-card p-8 py-20 text-center">
                        <div className="mb-4 inline-flex rounded-full bg-muted/50 p-4">
                            <FilterX className="h-8 w-8 text-muted-foreground" />
                        </div>
                        <h3 className="mb-1 text-lg font-bold text-foreground">
                            No hay habitaciones disponibles con los criterios
                            seleccionados
                        </h3>
                        <p className="mb-6 text-xs text-muted-foreground">
                            Pruebe seleccionando otra categoría o borrando los
                            términos de búsqueda.
                        </p>
                        <Link
                            href="/habitaciones"
                            className="shadow-airbnb inline-flex items-center gap-2 rounded-full bg-bugambilia-600 px-5 py-2.5 text-xs font-bold text-white transition-all hover:bg-bugambilia-700"
                        >
                            Ver Todas las Habitaciones
                        </Link>
                    </div>
                )}

                {/* Paginación Reutilizable en Español */}
                <PaginadorPublico paginacion={pagination} />
            </div>
        </section>
    );
};
export default SeccionHabitaciones;
