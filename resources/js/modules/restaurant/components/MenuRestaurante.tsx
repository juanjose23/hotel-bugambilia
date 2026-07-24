import {
    Search,
    UtensilsCrossed,
    Clock,
    Sparkles,
    Tag,
    Flame,
} from 'lucide-react';
import { useState, useMemo } from 'react';
import type { MenuItemData } from '@/modules/restaurant/types';
interface MenuRestauranteProps {
    menu: MenuItemData[];
}
const MenuRestaurante = ({ menu }: MenuRestauranteProps) => {
    const [selectedCategory, setSelectedCategory] = useState<string>('TODOS');
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [selectedTag, setSelectedTag] = useState<string | null>(null);
    // Extraer categorías únicas
    const categories = useMemo(() => {
        if (!menu || menu.length === 0) {
            return [];
        }

        const cats = Array.from(new Set(menu.map((m) => m.categoria)));

        return ['TODOS', ...cats];
    }, [menu]);
    // Extraer etiquetas únicas (ej. Recomendado, Especialidad, etc.)
    const allTags = useMemo(() => {
        if (!menu || menu.length === 0) {
            return [];
        }

        const tagsSet = new Set<string>();
        menu.forEach((item) => {
            item.etiquetas?.forEach((t) => tagsSet.add(t));
        });

        return Array.from(tagsSet);
    }, [menu]);
    // Filtrar menú
    const filteredMenu = useMemo(() => {
        return menu.filter((item) => {
            // Filtro Categoría
            const matchCat =
                selectedCategory === 'TODOS' ||
                item.categoria.toLowerCase() === selectedCategory.toLowerCase();
            // Filtro Búsqueda
            const matchSearch =
                !searchQuery ||
                item.nombre.toLowerCase().includes(searchQuery.toLowerCase()) ||
                item.descripcion
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase());
            // Filtro Etiqueta
            const matchTag =
                !selectedTag ||
                (item.etiquetas && item.etiquetas.includes(selectedTag));

            return matchCat && matchSearch && matchTag;
        });
    }, [menu, selectedCategory, searchQuery, selectedTag]);

    if (!menu || menu.length === 0) {
        return null;
    }

    return (
        <section id="menu-section" className="bg-background py-20 font-sans">
            <div className="container mx-auto max-w-6xl px-4">
                {/* Header */}
                <div className="mx-auto mb-12 max-w-2xl text-center">
                    <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-black tracking-widest text-amber-600 uppercase dark:text-amber-400">
                        <UtensilsCrossed className="h-3.5 w-3.5" />
                        Propuesta Gastronómica
                    </div>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground md:text-5xl">
                        Nuestro Menú a la Carta
                    </h2>
                    <p className="text-base text-muted-foreground md:text-lg">
                        Platos preparados al momento con carnes seleccionadas,
                        mariscos frescos de la costa y recetas autóctonas
                        nicaragüenses.
                    </p>
                </div>

                {/* Controls: Category Tabs & Search Bar */}
                <div className="mb-8 flex flex-col items-center justify-between gap-4 md:flex-row">
                    {/* Categories Horizontal Scroll */}
                    <div className="flex w-full scrollbar-none items-center gap-2 overflow-x-auto pb-2 md:w-auto">
                        {categories.map((cat) => {
                            const isSelected = selectedCategory === cat;

                            return (
                                <button
                                    key={cat}
                                    onClick={() => setSelectedCategory(cat)}
                                    className={`cursor-pointer rounded-2xl px-4 py-2.5 text-xs font-black tracking-wider whitespace-nowrap uppercase transition-all ${
                                        isSelected
                                            ? 'scale-105 bg-amber-500 text-zinc-950 shadow-md shadow-amber-500/20'
                                            : 'border border-border bg-muted/50 text-muted-foreground hover:bg-muted hover:text-foreground'
                                    }`}
                                >
                                    {cat === 'TODOS' ? 'Todo el Menú' : cat}
                                </button>
                            );
                        })}
                    </div>

                    {/* Search Box */}
                    <div className="relative w-full shrink-0 md:w-72">
                        <Search className="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Buscar platillo o ingrediente..."
                            className="w-full rounded-2xl border border-border bg-card py-2.5 pr-4 pl-10 text-xs font-semibold text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-amber-500/50 focus:outline-none"
                        />
                    </div>
                </div>

                {/* Tag Filters (if any) */}
                {allTags.length > 0 && (
                    <div className="mb-10 flex flex-wrap items-center gap-2 border-b border-border/40 pb-4">
                        <span className="mr-2 flex items-center gap-1 text-xs font-extrabold tracking-wider text-muted-foreground uppercase">
                            <Tag className="h-3 w-3 text-amber-500" />
                            Filtros:
                        </span>
                        <button
                            onClick={() => setSelectedTag(null)}
                            className={`cursor-pointer rounded-full px-3 py-1 text-xs font-bold transition-all ${
                                selectedTag === null
                                    ? 'border border-amber-500/40 bg-amber-500/20 text-amber-600 dark:text-amber-400'
                                    : 'bg-muted/40 text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            Todos
                        </button>
                        {allTags.map((tag) => (
                            <button
                                key={tag}
                                onClick={() =>
                                    setSelectedTag(
                                        selectedTag === tag ? null : tag,
                                    )
                                }
                                className={`cursor-pointer rounded-full px-3 py-1 text-xs font-bold transition-all ${
                                    selectedTag === tag
                                        ? 'border border-amber-500 bg-amber-500 text-zinc-950'
                                        : 'border border-border/50 bg-muted/40 text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                {tag}
                            </button>
                        ))}
                    </div>
                )}

                {/* Menu Items Grid */}
                {filteredMenu.length === 0 ? (
                    <div className="rounded-3xl border border-border bg-card p-8 py-16 text-center">
                        <UtensilsCrossed className="mx-auto mb-3 h-10 w-10 text-muted-foreground" />
                        <p className="mb-1 text-base font-bold text-foreground">
                            No se encontraron opciones
                        </p>
                        <p className="text-xs text-muted-foreground">
                            Pruebe cambiar la categoría o término de búsqueda.
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {filteredMenu.map((item) => (
                            <div
                                key={item.id}
                                className="group flex flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-card transition-all duration-300 hover:border-amber-500/50 hover:shadow-xl"
                            >
                                {/* Dish Image */}
                                <div className="relative h-48 w-full overflow-hidden bg-muted">
                                    <img
                                        src={
                                            item.imagen ||
                                            '/images/service-kitchen.webp'
                                        }
                                        alt={item.nombre}
                                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-60 transition-opacity group-hover:opacity-40" />

                                    {/* Category Pill */}
                                    <span className="absolute top-3 left-3 rounded-full border border-white/20 bg-black/60 px-2.5 py-1 text-[10px] font-black tracking-widest text-white uppercase backdrop-blur-md">
                                        {item.categoria}
                                    </span>

                                    {/* Price Tag */}
                                    {item.precio !== null && (
                                        <span className="absolute right-3 bottom-3 rounded-xl bg-amber-500 px-3 py-1 text-sm font-black text-zinc-950 shadow-lg">
                                            {item.moneda} {item.precio}
                                        </span>
                                    )}
                                </div>

                                {/* Dish Details */}
                                <div className="flex flex-1 flex-col justify-between space-y-4 p-5">
                                    <div>
                                        {/* Tags */}
                                        {item.etiquetas &&
                                            item.etiquetas.length > 0 && (
                                                <div className="mb-2 flex flex-wrap gap-1.5">
                                                    {item.etiquetas.map(
                                                        (t, idx) => (
                                                            <span
                                                                key={idx}
                                                                className="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-2 py-0.5 text-[10px] font-extrabold text-amber-600 dark:text-amber-400"
                                                            >
                                                                <Sparkles className="h-2.5 w-2.5" />
                                                                {t}
                                                            </span>
                                                        ),
                                                    )}
                                                </div>
                                            )}

                                        <h3 className="mb-2 text-lg font-black tracking-tight text-foreground transition-colors group-hover:text-amber-600 dark:group-hover:text-amber-400">
                                            {item.nombre}
                                        </h3>

                                        <p className="line-clamp-3 text-xs leading-relaxed text-muted-foreground">
                                            {item.descripcion}
                                        </p>
                                    </div>

                                    {/* Prep time & footer */}
                                    <div className="flex items-center justify-between border-t border-border/40 pt-3 text-xs text-muted-foreground">
                                        <span className="flex items-center gap-1 font-semibold">
                                            <Clock className="h-3.5 w-3.5 text-amber-500" />
                                            {item.tiempo_preparacion ||
                                                '15-20 min'}
                                        </span>
                                        <span className="flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                                            <Flame className="h-3.5 w-3.5" />
                                            Disponible
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
};
export default MenuRestaurante;
