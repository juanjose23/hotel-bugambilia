import { Minus, Plus, UtensilsCrossed } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { PlatoPreorden } from '../../hooks/useReservaFlujoReserva';
import type { MenuItemData } from '../../types';

interface RestaurantePasoPreordenMenuProps {
    menu: MenuItemData[];
    platosPreorden: PlatoPreorden[];
    categoriaMenuFiltro: string;
    categoriasMenu: string[];
    menuFiltrado: MenuItemData[];
    agregarPlatoPreorden: (plato: MenuItemData) => void;
    removerPlatoPreorden: (platoId: number) => void;
    alCambiarCategoria: (categoria: string) => void;
}

export const RestaurantePasoPreordenMenu = ({
    platosPreorden,
    categoriaMenuFiltro,
    categoriasMenu,
    menuFiltrado,
    agregarPlatoPreorden,
    removerPlatoPreorden,
    alCambiarCategoria,
}: RestaurantePasoPreordenMenuProps) => {
    return (
        <div className="space-y-6 rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
            <div className="flex items-center justify-between border-b border-border/60 pb-4">
                <div className="flex items-center gap-3">
                    <div className="flex size-9 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary dark:text-rose-400">
                        <UtensilsCrossed className="size-4" />
                    </div>
                    <div>
                        <h3 className="text-base font-black text-foreground">
                            Pre-ordenar Platos del Menú
                        </h3>
                        <p className="text-xs text-muted-foreground">
                            Opcional: elije qué deseas comer para que esté listo
                            al momento de llegar.
                        </p>
                    </div>
                </div>

                <span className="text-xs font-bold text-muted-foreground">
                    {platosPreorden.length} seleccionados
                </span>
            </div>

            <div className="flex scrollbar-none items-center gap-1.5 overflow-x-auto pb-1">
                {categoriasMenu.map((cat) => (
                    <Button
                        key={cat}
                        type="button"
                        variant={
                            categoriaMenuFiltro === cat ? 'default' : 'ghost'
                        }
                        size="sm"
                        onClick={() => alCambiarCategoria(cat)}
                        className={`cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold whitespace-nowrap transition-all ${
                            categoriaMenuFiltro === cat
                                ? 'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90'
                                : 'bg-muted/60 text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        {cat === 'todos' ? 'Todas las Categorías' : cat}
                    </Button>
                ))}
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {menuFiltrado.map((plato) => {
                    const enPreorden = platosPreorden.find(
                        (p) => p.plato.id === plato.id,
                    );

                    return (
                        <div
                            key={plato.id}
                            className="flex items-center gap-3 rounded-2xl border border-border/80 bg-muted/20 p-3 transition-all hover:border-primary/40"
                        >
                            <img
                                src={
                                    plato.imagen ||
                                    '/images/service-kitchen.png'
                                }
                                alt={plato.nombre}
                                className="size-16 shrink-0 rounded-xl object-cover"
                            />
                            <div className="min-w-0 flex-1">
                                <h4 className="truncate text-xs font-black text-foreground">
                                    {plato.nombre}
                                </h4>
                                <p className="line-clamp-1 text-[10px] text-muted-foreground">
                                    {plato.descripcion}
                                </p>
                                <span className="mt-1 block text-xs font-bold text-primary dark:text-rose-400">
                                    {plato.moneda}{' '}
                                    {Number(plato.precio || 0).toFixed(2)}
                                </span>
                            </div>

                            <div className="flex shrink-0 items-center gap-1.5">
                                {enPreorden ? (
                                    <div className="flex items-center gap-1 rounded-xl border border-border/80 bg-card p-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            onClick={() =>
                                                removerPlatoPreorden(plato.id)
                                            }
                                            className="flex size-6 cursor-pointer items-center justify-center rounded-lg bg-muted p-0 text-foreground hover:bg-destructive/20"
                                        >
                                            <Minus className="size-3" />
                                        </Button>
                                        <span className="px-1.5 text-xs font-black">
                                            {enPreorden.cantidad}
                                        </span>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            onClick={() =>
                                                agregarPlatoPreorden(plato)
                                            }
                                            className="flex size-6 cursor-pointer items-center justify-center rounded-lg bg-primary p-0 text-primary-foreground hover:bg-primary/90"
                                        >
                                            <Plus className="size-3" />
                                        </Button>
                                    </div>
                                ) : (
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        onClick={() =>
                                            agregarPlatoPreorden(plato)
                                        }
                                        className="cursor-pointer rounded-xl text-xs font-bold"
                                    >
                                        Agregar
                                    </Button>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

export default RestaurantePasoPreordenMenu;
