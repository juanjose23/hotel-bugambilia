import { Link } from '@inertiajs/react';
import {
    ShoppingBag,
    Plus,
    Minus,
    Trash2,
    MessageSquareQuote,
    ArrowRight,
    UtensilsCrossed,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';
import type { RetornoCarritoRestaurante } from '../hooks/useCarritoRestaurante';

interface RestaurantePasoArticulosProps {
    carrito: RetornoCarritoRestaurante;
    alAvanzar: () => void;
}

export const RestaurantePasoArticulos = ({
    carrito,
    alAvanzar,
}: RestaurantePasoArticulosProps) => {
    const {
        items,
        moneda,
        agregarItem,
        disminuirItem,
        actualizarNotasItem,
        eliminarItem,
        vaciarCarrito,
    } = carrito;

    const [platoEditandoNota, setPlatoEditandoNota] = useState<number | null>(
        null,
    );

    if (items.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center rounded-3xl border border-border/80 bg-card p-12 text-center shadow-xs">
                <div className="flex size-20 items-center justify-center rounded-full bg-primary/10 text-primary dark:text-rose-400">
                    <ShoppingBag className="size-10" />
                </div>
                <h3 className="mt-4 text-xl font-black text-foreground">
                    Tu carrito de restaurante está vacío
                </h3>
                <p className="mt-2 max-w-md text-xs text-muted-foreground">
                    Explora nuestra variada selección gastronómica de alta
                    cocina, platillos típicos y cortes selectos para añadirlos a
                    tu orden de entrega.
                </p>
                <div className="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <Link href="/restaurante">
                        <Button className="h-10 gap-2 rounded-2xl px-5 text-xs font-bold">
                            <UtensilsCrossed className="size-4" />
                            <span>Ver Menú del Restaurante</span>
                        </Button>
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header del paso */}
            <div className="rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
                <div className="flex items-center justify-between border-b border-border/60 pb-4">
                    <div>
                        <h2 className="text-lg font-black text-foreground">
                            1. Revisión de Platillos y Comanda
                        </h2>
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            Ajusta cantidades o agrega observaciones especiales
                            para el chef.
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="link"
                        size="sm"
                        onClick={vaciarCarrito}
                        className="h-auto cursor-pointer p-0 text-xs font-bold text-rose-500 hover:underline"
                    >
                        Vaciar carrito
                    </Button>
                </div>

                {/* Lista de platillos */}
                <div className="mt-4 space-y-3 divide-y divide-border/60">
                    {items.map(({ plato, cantidad, notas }) => {
                        const editando = platoEditandoNota === plato.id;
                        const precioTotal = (
                            Number(plato.precio || 0) * cantidad
                        ).toFixed(2);

                        return (
                            <div
                                key={plato.id}
                                className="space-y-2.5 pt-3 first:pt-0"
                            >
                                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                    <div className="flex min-w-0 items-center gap-3">
                                        {plato.imagen ? (
                                            <img
                                                src={plato.imagen}
                                                alt={plato.nombre}
                                                className="size-14 shrink-0 rounded-2xl border border-border/60 object-cover"
                                            />
                                        ) : (
                                            <div className="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-muted text-muted-foreground">
                                                <UtensilsCrossed className="size-6" />
                                            </div>
                                        )}
                                        <div className="min-w-0">
                                            <h4 className="truncate text-sm font-bold text-foreground">
                                                {plato.nombre}
                                            </h4>
                                            <p className="mt-0.5 text-xs text-muted-foreground">
                                                {moneda}{' '}
                                                {Number(
                                                    plato.precio || 0,
                                                ).toFixed(2)}{' '}
                                                c/u
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-between gap-4 sm:justify-end">
                                        {/* Control de cantidad */}
                                        <div className="flex items-center gap-1.5 rounded-full border border-border bg-muted/40 p-1">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                onClick={() =>
                                                    disminuirItem(plato.id)
                                                }
                                                className="flex size-7 cursor-pointer items-center justify-center rounded-full p-0 text-foreground transition-all hover:bg-card"
                                                aria-label="Disminuir cantidad"
                                            >
                                                <Minus className="size-3.5" />
                                            </Button>
                                            <span className="w-6 text-center text-xs font-black">
                                                {cantidad}
                                            </span>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                onClick={() =>
                                                    agregarItem(plato)
                                                }
                                                className="flex size-7 cursor-pointer items-center justify-center rounded-full p-0 text-foreground transition-all hover:bg-card"
                                                aria-label="Aumentar cantidad"
                                            >
                                                <Plus className="size-3.5" />
                                            </Button>
                                        </div>

                                        <div className="min-w-[70px] text-right">
                                            <span className="text-sm font-black text-foreground">
                                                {moneda} {precioTotal}
                                            </span>
                                        </div>

                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            onClick={() =>
                                                eliminarItem(plato.id)
                                            }
                                            className="size-8 cursor-pointer p-1.5 text-muted-foreground/60 transition-colors hover:text-rose-500"
                                            title="Eliminar del pedido"
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    </div>
                                </div>

                                {/* Observaciones / Notas específicas para la cocina */}
                                {notas && !editando && (
                                    <div className="flex items-center justify-between rounded-xl bg-muted/60 px-3 py-1.5 text-xs text-muted-foreground">
                                        <span className="italic">
                                            💬 Nota chef: &quot;{notas}&quot;
                                        </span>
                                        <Button
                                            type="button"
                                            variant="link"
                                            size="sm"
                                            onClick={() =>
                                                setPlatoEditandoNota(plato.id)
                                            }
                                            className="ml-2 h-auto cursor-pointer p-0 text-[11px] font-bold text-primary hover:underline"
                                        >
                                            Modificar
                                        </Button>
                                    </div>
                                )}

                                {editando ? (
                                    <div className="flex items-center gap-2 pt-1">
                                        <Input
                                            type="text"
                                            defaultValue={notas || ''}
                                            placeholder="Ej: Término medio, salsa aparte, sin cebolla..."
                                            className="h-8 flex-1 rounded-xl text-xs"
                                            onKeyDown={(e) => {
                                                if (e.key === 'Enter') {
                                                    e.preventDefault();
                                                    actualizarNotasItem(
                                                        plato.id,
                                                        e.currentTarget.value,
                                                    );
                                                    setPlatoEditandoNota(null);
                                                }
                                            }}
                                            onBlur={(e) => {
                                                actualizarNotasItem(
                                                    plato.id,
                                                    e.target.value,
                                                );
                                                setPlatoEditandoNota(null);
                                            }}
                                            autoFocus
                                        />
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="secondary"
                                            className="h-8 rounded-xl text-xs"
                                            onClick={() =>
                                                setPlatoEditandoNota(null)
                                            }
                                        >
                                            Listo
                                        </Button>
                                    </div>
                                ) : (
                                    !notas && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={() =>
                                                setPlatoEditandoNota(plato.id)
                                            }
                                            className="inline-flex h-auto cursor-pointer items-center gap-1 p-0 text-[11px] font-medium text-muted-foreground transition-colors hover:text-primary"
                                        >
                                            <MessageSquareQuote className="size-3.5" />
                                            <span>
                                                + Añadir nota para cocina
                                                (término, aderezos, alergias)
                                            </span>
                                        </Button>
                                    )
                                )}
                            </div>
                        );
                    })}
                </div>

                {/* Botón de avanzar */}
                <div className="mt-8 flex justify-end border-t border-border/60 pt-5">
                    <Button
                        type="button"
                        onClick={alAvanzar}
                        className="h-11 cursor-pointer gap-2 rounded-2xl px-6 text-xs font-bold shadow-md"
                    >
                        <span>Continuar a Datos de Entrega</span>
                        <ArrowRight className="size-4" />
                    </Button>
                </div>
            </div>
        </div>
    );
};

export default RestaurantePasoArticulos;
