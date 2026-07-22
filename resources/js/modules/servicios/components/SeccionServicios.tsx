import { Link, router } from '@inertiajs/react';
import {
    Sparkles,
    ArrowRight,
    FilterX,
    ConciergeBell,
    Search,
    Flame,
} from 'lucide-react';
import { useState } from 'react';
import PaginadorPublico from '@/modules/compartido/componentes/PaginadorPublico';
import type { ServicioItem, PaginacionData } from '@/modules/compartido/tipos';
import { resolverImagenStorage } from '@/modules/compartido/utilidades/imagenes';

interface SeccionServiciosProps {
    services?: ServicioItem[];
    categorias?: string[];
    categoriaMasPopular?: string | null;
    selectedCategory?: string | null;
    searchQuery?: string;
    pagination?: PaginacionData;
}

export default function SeccionServicios({
    services = [],
    categorias = [],
    categoriaMasPopular = null,
    selectedCategory = null,
    searchQuery = '',
    pagination,
}: SeccionServiciosProps) {
    const [term, setTerm] = useState(searchQuery);

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            '/servicios',
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
                {/* Banner Hero */}
                <div className="relative mb-8 flex min-h-[280px] items-center overflow-hidden rounded-3xl border border-border/60 shadow-2xl sm:min-h-[340px] md:mb-12 md:min-h-[380px]">
                    <img
                        src="/images/terrace.jpg"
                        alt="Servicios Hotel Bugambilias"
                        className="absolute inset-0 h-full w-full scale-105 object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/95 via-black/75 to-black/30" />

                    <div className="relative z-10 max-w-3xl p-6 sm:p-10 lg:p-14">
                        <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-bugambilia-400/40 bg-bugambilia-500/20 px-3.5 py-1 text-xs font-extrabold tracking-widest text-bugambilia-300 uppercase backdrop-blur-md">
                            <Sparkles className="h-3.5 w-3.5" />
                            Hospitalidad Premium
                        </div>

                        <h1 className="mb-3 text-2xl leading-tight font-black tracking-tight text-white drop-shadow-md sm:text-4xl lg:text-5xl">
                            Servicios Exclusivos &{' '}
                            <span className="font-serif font-normal text-amber-300 italic">
                                Experiencias
                            </span>
                        </h1>

                        <p className="max-w-xl text-xs leading-relaxed font-medium text-white/90 drop-shadow-sm sm:text-sm">
                            Diseñados para complementar su estancia con confort,
                            gastronomía local y comodidades de primer nivel en
                            Estelí.
                        </p>
                    </div>
                </div>

                {/* Buscador & Controles */}
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
                            placeholder="Buscar servicio por nombre o palabra clave..."
                            className="w-full rounded-2xl border border-border bg-background py-2.5 pr-24 pl-11 text-xs font-semibold text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-bugambilia-500/50 focus:outline-none"
                        />
                        <button
                            type="submit"
                            className="absolute right-1.5 cursor-pointer rounded-xl bg-bugambilia-600 px-3.5 py-1.5 text-xs font-bold text-white transition-all hover:bg-bugambilia-700"
                        >
                            Buscar
                        </button>
                    </form>

                    {/* Estado de Filtros */}
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
                                href="/servicios"
                                preserveScroll
                                only={[
                                    'services',
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

                {/* Filtros por Categoría (TODAS las categorías) */}
                {categorias.length > 0 && (
                    <div className="mb-8 flex scrollbar-none items-center gap-2 overflow-x-auto pb-4">
                        <Link
                            href="/servicios?categoria=TODOS"
                            preserveScroll
                            only={[
                                'services',
                                'pagination',
                                'categorias',
                                'selectedCategory',
                                'searchQuery',
                            ]}
                            className={`shrink-0 cursor-pointer rounded-full px-4 py-2 text-xs font-extrabold tracking-wider uppercase transition-all duration-200 ${
                                selectedCategory === 'TODOS' ||
                                selectedCategory === null
                                    ? 'shadow-airbnb bg-foreground text-background'
                                    : 'border border-border/80 bg-card text-muted-foreground hover:border-gray-400 hover:text-foreground dark:hover:border-gray-600'
                            }`}
                        >
                            Todos los Servicios
                        </Link>

                        {categorias.map((cat) => {
                            const isSelected = selectedCategory === cat;
                            const isPopular = cat === categoriaMasPopular;

                            return (
                                <Link
                                    key={cat}
                                    href={`/servicios?categoria=${encodeURIComponent(cat)}`}
                                    preserveScroll
                                    only={[
                                        'services',
                                        'pagination',
                                        'categorias',
                                        'selectedCategory',
                                        'searchQuery',
                                    ]}
                                    className={`inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-full px-4 py-2 text-xs font-extrabold tracking-wider uppercase transition-all duration-200 ${
                                        isSelected
                                            ? 'shadow-airbnb scale-105 bg-bugambilia-600 text-white'
                                            : 'border border-border/80 bg-card text-muted-foreground hover:border-gray-400 hover:text-foreground dark:hover:border-gray-600'
                                    }`}
                                >
                                    {cat}
                                    {isPopular && (
                                        <span className="inline-flex items-center gap-1 rounded-full bg-amber-400 px-1.5 py-0.5 text-[9px] font-black text-zinc-950 lowercase">
                                            <Flame className="h-2.5 w-2.5" />
                                            más popular
                                        </span>
                                    )}
                                </Link>
                            );
                        })}
                    </div>
                )}

                {/* Grid de Servicios */}
                {services.length > 0 ? (
                    <div className="mb-16 grid grid-cols-1 gap-6 sm:grid-cols-2 md:gap-8 lg:grid-cols-3">
                        {services.map((item) => {
                            const urlDetalle = `/servicios/${item.codigo || item.id}`;
                            const imagenResolved = resolverImagenStorage(
                                item.imagen,
                                'servicio',
                                item.categoria,
                            );

                            return (
                                <article
                                    key={item.id}
                                    className="group shadow-airbnb hover:shadow-airbnb-hover relative flex flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-background transition-all duration-300 hover:-translate-y-1"
                                >
                                    <Link
                                        href={urlDetalle}
                                        className="relative block aspect-[4/3] overflow-hidden bg-muted/40"
                                    >
                                        <img
                                            src={imagenResolved}
                                            alt={item.nombre}
                                            className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            loading="lazy"
                                        />

                                        {item.categoria && (
                                            <div className="absolute top-3 left-3 z-10">
                                                <span className="rounded-full border border-white/20 bg-black/70 px-3 py-1 text-[10px] font-extrabold tracking-wider text-white uppercase backdrop-blur-md">
                                                    {item.categoria}
                                                </span>
                                            </div>
                                        )}
                                    </Link>

                                    <div className="flex flex-1 flex-col justify-between p-6">
                                        <div>
                                            <Link href={urlDetalle}>
                                                <h3 className="mb-2 line-clamp-1 text-lg font-black tracking-tight text-foreground transition-colors group-hover:text-bugambilia-600">
                                                    {item.nombre}
                                                </h3>
                                            </Link>

                                            {item.descripcion && (
                                                <p className="mb-4 line-clamp-2 text-xs leading-relaxed text-muted-foreground">
                                                    {item.descripcion}
                                                </p>
                                            )}
                                        </div>

                                        <div className="mt-auto flex items-center justify-between border-t border-border/40 pt-4">
                                            <div>
                                                {item.precio ? (
                                                    <div>
                                                        <span className="block text-[10px] font-bold text-muted-foreground uppercase">
                                                            Precio
                                                        </span>
                                                        <span className="text-base font-black text-foreground">
                                                            {item.moneda || '$'}
                                                            {item.precio}{' '}
                                                            <span className="text-xs font-semibold">
                                                                USD
                                                            </span>
                                                        </span>
                                                    </div>
                                                ) : (
                                                    <span className="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                        De Cortesía
                                                    </span>
                                                )}
                                            </div>

                                            <Link
                                                href={urlDetalle}
                                                className="group/btn inline-flex items-center gap-1.5 text-xs font-extrabold text-bugambilia-600 transition-colors hover:text-bugambilia-700 dark:text-bugambilia-400"
                                            >
                                                <span>Ver Detalle</span>
                                                <ArrowRight className="h-3.5 w-3.5 transition-transform group-hover/btn:translate-x-1" />
                                            </Link>
                                        </div>
                                    </div>
                                </article>
                            );
                        })}
                    </div>
                ) : (
                    <div className="mb-16 rounded-3xl border border-border/60 bg-card py-20 text-center">
                        <ConciergeBell className="mx-auto mb-4 h-12 w-12 text-muted-foreground/40" />
                        <h3 className="mb-1 text-lg font-bold text-foreground">
                            No hay servicios disponibles con los criterios
                            seleccionados
                        </h3>
                        <p className="mb-6 text-xs text-muted-foreground">
                            Pruebe borrar los términos de búsqueda o cambiar la
                            categoría.
                        </p>
                        <Link
                            href="/servicios"
                            preserveScroll
                            only={[
                                'services',
                                'pagination',
                                'categorias',
                                'selectedCategory',
                                'searchQuery',
                            ]}
                            className="inline-flex items-center gap-2 rounded-full bg-bugambilia-600 px-6 py-2.5 text-xs font-bold tracking-wider text-white uppercase transition-colors hover:bg-bugambilia-700"
                        >
                            Ver Todos los Servicios
                        </Link>
                    </div>
                )}

                {/* Paginador Compartido */}
                {pagination && (
                    <PaginadorPublico
                        paginacion={pagination}
                        propiedadesSolo={[
                            'services',
                            'pagination',
                            'categorias',
                            'selectedCategory',
                            'searchQuery',
                        ]}
                    />
                )}
            </div>
        </section>
    );
}
