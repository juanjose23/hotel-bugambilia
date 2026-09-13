import {
    Clock,
    Sparkles,
    Search,
    UtensilsCrossed,
    Plus,
    Minus,
} from 'lucide-react';
import { Input } from '@/modules/shared/components/ui/input';
import type { RetornoCarritoRestaurante } from '../hooks/useCarritoRestaurante';
import { useFiltrosMenu } from '../hooks/useFiltrosMenu';
import type { MenuItemData } from '../types';

interface PropsRestauranteMenu {
    menu: MenuItemData[];
    carrito?: RetornoCarritoRestaurante;
    permiteDelivery?: boolean;
}

export const RestauranteMenu = ({
    menu,
    carrito,
    permiteDelivery = true,
}: PropsRestauranteMenu) => {
    const {
        categoriaActiva,
        setCategoriaActiva,
        busqueda,
        setBusqueda,
        categoriasDisponibles,
        menuFiltrado,
    } = useFiltrosMenu({ menu });

    return (
        <section
            id="menu-restaurante"
            className="scroll-mt-16 border-t border-border bg-card/30 py-16 font-sans"
        >
            <div className="container mx-auto px-4 sm:px-6">
                <div className="text-center">
                    <div className="inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/10 px-3.5 py-1 text-xs font-black tracking-widest text-primary uppercase dark:text-rose-400">
                        <UtensilsCrossed className="size-3.5" />
                        <span>Alta Cocina en Estelí</span>
                    </div>

                    <h2 className="mt-3 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                        Menú a la Carta & Especialidades
                    </h2>
                    <p className="mx-auto mt-2 max-w-xl text-xs text-muted-foreground sm:text-sm">
                        Platillos preparados al momento por nuestros chefs con
                        ingredientes frescos de los valles del norte de
                        Nicaragua.
                        {permiteDelivery && (
                            <span className="mt-1 block font-bold text-emerald-600 dark:text-emerald-400">
                                🛵 ¡Pide a domicilio con entrega rápida en
                                Estelí!
                            </span>
                        )}
                    </p>
                </div>

                {/* Filtros de Categorías y Buscador */}
                <div className="mt-8 flex flex-col items-center justify-between gap-4 md:flex-row">
                    {/* Pills de categorías */}
                    <div className="flex flex-wrap items-center justify-center gap-2">
                        <button
                            type="button"
                            onClick={() => setCategoriaActiva('todos')}
                            className={`cursor-pointer rounded-full px-4 py-1.5 text-xs font-bold transition-all ${
                                categoriaActiva === 'todos'
                                    ? 'bg-primary text-primary-foreground shadow-xs'
                                    : 'border border-border bg-card text-muted-foreground hover:bg-muted hover:text-foreground'
                            }`}
                        >
                            Todas las Categorías ({menu.length})
                        </button>
                        {categoriasDisponibles.map((cat) => (
                            <button
                                key={cat}
                                type="button"
                                onClick={() => setCategoriaActiva(cat)}
                                className={`cursor-pointer rounded-full px-4 py-1.5 text-xs font-bold transition-all ${
                                    categoriaActiva === cat
                                        ? 'bg-primary text-primary-foreground shadow-xs'
                                        : 'border border-border bg-card text-muted-foreground hover:bg-muted hover:text-foreground'
                                }`}
                            >
                                {cat}
                            </button>
                        ))}
                    </div>

                    {/* Buscador de platillos */}
                    <div className="relative w-full max-w-xs">
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            type="text"
                            placeholder="Buscar plato o ingrediente..."
                            value={busqueda}
                            onChange={(e) => setBusqueda(e.target.value)}
                            className="rounded-full pl-9 text-xs"
                        />
                    </div>
                </div>

                {/* Grid de Platos */}
                {menuFiltrado.length === 0 ? (
                    <div className="mt-12 flex flex-col items-center justify-center rounded-3xl border border-dashed border-border bg-card/40 p-12 text-center">
                        <UtensilsCrossed className="size-10 text-muted-foreground/50" />
                        <h3 className="mt-3 text-sm font-bold text-foreground">
                            No se encontraron platillos
                        </h3>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Prueba buscando otro término o seleccionando otra
                            categoría.
                        </p>
                    </div>
                ) : (
                    <div className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {menuFiltrado.map((plato) => {
                            const cantidadEnCarrito = carrito
                                ? carrito.obtenerCantidadItem(plato.id)
                                : 0;

                            return (
                                <div
                                    key={plato.id}
                                    className="group flex flex-col justify-between overflow-hidden rounded-3xl border border-border bg-card p-4 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-lg dark:hover:border-rose-500/50"
                                >
                                    <div className="flex gap-4">
                                        <div className="relative size-24 shrink-0 overflow-hidden rounded-2xl bg-muted sm:size-28">
                                            <img
                                                src={
                                                    plato.imagen ||
                                                    '/images/service-events.webp'
                                                }
                                                alt={plato.nombre}
                                                className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                loading="lazy"
                                            />
                                            <span className="absolute bottom-1.5 left-1.5 rounded-md bg-black/70 px-1.5 py-0.5 text-[9px] font-black text-white backdrop-blur-xs">
                                                {plato.categoria}
                                            </span>
                                        </div>

                                        <div className="flex min-w-0 grow flex-col justify-between">
                                            <div>
                                                <div className="flex items-start justify-between gap-2">
                                                    <h3 className="truncate text-sm font-black text-foreground transition-colors group-hover:text-primary dark:group-hover:text-rose-400">
                                                        {plato.nombre}
                                                    </h3>
                                                </div>
                                                {plato.precio !== null && (
                                                    <span className="mt-0.5 block text-sm font-black text-primary dark:text-rose-400">
                                                        {plato.moneda}{' '}
                                                        {Number(
                                                            plato.precio,
                                                        ).toFixed(2)}
                                                    </span>
                                                )}
                                                <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">
                                                    {plato.descripcion}
                                                </p>
                                            </div>

                                            <div className="mt-2 flex items-center justify-between text-[11px] text-muted-foreground">
                                                <span className="inline-flex items-center gap-1 font-medium">
                                                    <Clock className="size-3 text-primary dark:text-rose-400" />
                                                    {plato.tiempo_preparacion}
                                                </span>

                                                {plato.etiquetas &&
                                                    plato.etiquetas.length >
                                                        0 && (
                                                        <span className="inline-flex items-center gap-1 font-semibold text-amber-500">
                                                            <Sparkles className="size-3" />
                                                            {plato.etiquetas[0]}
                                                        </span>
                                                    )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Botón de Agregar / Stepper de Pedido */}
                                    {permiteDelivery &&
                                        plato.disponible &&
                                        carrito && (
                                            <div className="mt-4 flex items-center justify-between border-t border-border/60 pt-3">
                                                <span className="text-[11px] font-bold text-muted-foreground">
                                                    {cantidadEnCarrito > 0
                                                        ? `${cantidadEnCarrito} en carrito`
                                                        : 'Pedido a domicilio'}
                                                </span>

                                                {cantidadEnCarrito === 0 ? (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            carrito.agregarItem(
                                                                plato,
                                                            )
                                                        }
                                                        className="inline-flex cursor-pointer items-center gap-1.5 rounded-full bg-primary/10 px-3.5 py-1.5 text-xs font-black text-primary shadow-xs transition-all hover:bg-primary hover:text-primary-foreground active:scale-95 dark:text-rose-400 dark:hover:text-white"
                                                    >
                                                        <Plus className="size-3.5" />
                                                        <span>+ Pedir</span>
                                                    </button>
                                                ) : (
                                                    <div className="flex items-center rounded-full border border-primary/40 bg-primary/5 p-0.5 shadow-xs">
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                carrito.disminuirItem(
                                                                    plato.id,
                                                                )
                                                            }
                                                            className="flex size-6 cursor-pointer items-center justify-center rounded-full text-foreground transition-colors hover:bg-primary/20"
                                                        >
                                                            <Minus className="size-3" />
                                                        </button>
                                                        <span className="w-6 text-center text-xs font-black text-primary dark:text-rose-400">
                                                            {cantidadEnCarrito}
                                                        </span>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                carrito.agregarItem(
                                                                    plato,
                                                                )
                                                            }
                                                            className="flex size-6 cursor-pointer items-center justify-center rounded-full text-foreground transition-colors hover:bg-primary/20"
                                                        >
                                                            <Plus className="size-3" />
                                                        </button>
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>
        </section>
    );
};
