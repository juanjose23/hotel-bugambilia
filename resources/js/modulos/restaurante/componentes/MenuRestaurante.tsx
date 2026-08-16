import { Search, UtensilsCrossed, Tag, Flame } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { useFiltroMenuRestaurante } from '../hooks/useFiltroMenuRestaurante';
import type { PropiedadesMenuRestaurante } from '../interfaces/restauranteInterfaces';
import { TarjetaPlatilloMenu } from './secciones/TarjetaPlatilloMenu';

export const MenuRestaurante = ({ menu = [] }: PropiedadesMenuRestaurante) => {
    const {
        selectedCategory,
        setSelectedCategory,
        searchQuery,
        setSearchQuery,
        selectedTag,
        setSelectedTag,
        categories,
        allTags,
        filteredMenu,
    } = useFiltroMenuRestaurante(menu);

    if (!menu || menu.length === 0) {
        return null;
    }

    return (
        <section id="menu-section" className="bg-background py-20 font-sans">
            <div className="container mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="mx-auto mb-12 max-w-2xl text-center">
                    <Badge
                        variant="outline"
                        className="mb-3 border-amber-500/20 bg-amber-500/10 text-amber-600 dark:text-amber-400"
                    >
                        <UtensilsCrossed
                            className="mr-1.5 size-3.5"
                            data-icon="inline-start"
                        />{' '}
                        Propuesta Gastronómica
                    </Badge>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground md:text-5xl">
                        Nuestro Menú a la Carta
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        Platos preparados al momento con carnes seleccionadas,
                        mariscos frescos y recetas autóctonas nicaragüenses.
                    </p>
                </div>

                {/* Buscador & Categorías */}
                <div className="mb-8 flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div className="no-scrollbar flex w-full items-center gap-2 overflow-x-auto pb-2 md:w-auto">
                        {categories.map((cat) => {
                            const isSelected = selectedCategory === cat;

                            return (
                                <Button
                                    key={cat}
                                    type="button"
                                    variant={isSelected ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() => setSelectedCategory(cat)}
                                    className={`shrink-0 rounded-full text-xs font-extrabold ${
                                        isSelected
                                            ? 'bg-amber-500 text-black hover:bg-amber-600'
                                            : ''
                                    }`}
                                >
                                    {cat}
                                </Button>
                            );
                        })}
                    </div>

                    <div className="relative flex w-full items-center md:w-72">
                        <Search className="absolute left-3.5 size-4 text-muted-foreground" />
                        <Input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Buscar platillo..."
                            className="rounded-2xl pl-10 text-xs font-medium"
                        />
                    </div>
                </div>

                {/* Filtros por Etiqueta (Especialidad, Saludable, etc.) */}
                {allTags.length > 0 && (
                    <div className="mb-10 flex flex-wrap items-center justify-center gap-2">
                        <Button
                            type="button"
                            variant={selectedTag === null ? 'default' : 'ghost'}
                            size="xs"
                            onClick={() => setSelectedTag(null)}
                            className="rounded-full text-[11px] font-bold"
                        >
                            Todas las etiquetas
                        </Button>
                        {allTags.map((tag) => (
                            <Button
                                key={tag}
                                type="button"
                                variant={
                                    selectedTag === tag ? 'default' : 'ghost'
                                }
                                size="xs"
                                onClick={() =>
                                    setSelectedTag(
                                        selectedTag === tag ? null : tag,
                                    )
                                }
                                className={`rounded-full text-[11px] font-bold ${
                                    selectedTag === tag
                                        ? 'bg-amber-500 text-black'
                                        : 'text-muted-foreground'
                                }`}
                            >
                                <Tag
                                    className="mr-1 size-3"
                                    data-icon="inline-start"
                                />
                                {tag}
                            </Button>
                        ))}
                    </div>
                )}

                {/* Grilla de Platillos */}
                {filteredMenu.length > 0 ? (
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {filteredMenu.map((item) => (
                            <TarjetaPlatilloMenu key={item.id} item={item} />
                        ))}
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center rounded-3xl border border-border bg-card p-12 text-center">
                        <Flame className="mb-3 size-12 text-muted-foreground/40" />
                        <h3 className="text-lg font-black text-foreground">
                            No se encontraron platillos
                        </h3>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Intente cambiar el término de búsqueda o seleccione
                            otra categoría.
                        </p>
                    </div>
                )}
            </div>
        </section>
    );
};

export default MenuRestaurante;
