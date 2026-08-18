import { useState } from 'react';
import { Utensils, CheckCircle2, ShoppingBag, X, Plus, Minus } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Label } from '@/modulos/compartido/ui/etiqueta';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { formatearNumero } from '@/modulos/compartido/utilidades/formato';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';

interface PropiedadesModalPedidoRestauranteHabitacion {
    reserva: ReservaClienteDomain | null;
    estaAbierto: boolean;
    alCerrar: () => void;
}

interface ItemMenu {
    id: number;
    nombre: string;
    descripcion: string;
    precio: number;
    categoria: string;
    emoji: string;
}

const MENU_ROOM_SERVICE: ItemMenu[] = [
    {
        id: 1,
        nombre: 'Desayuno Típico Nica Bugambilias',
        descripcion: 'Huevos al gusto, gallopinto tradicional, queso frito, plátanos y tortillas de maíz.',
        precio: 9.5,
        categoria: 'Desayunos',
        emoji: '🍳',
    },
    {
        id: 2,
        nombre: 'Filete Mignon en Salsa de Champiñones',
        descripcion: 'Corte magro a la parrilla con reducción de vino tinto, vegetales salteados y puré de papa.',
        precio: 18.0,
        categoria: 'Platos Fuertes',
        emoji: '🥩',
    },
    {
        id: 3,
        nombre: 'Club Sándwich Bugambilias & Papas Fritas',
        descripcion: 'Pollo desmenuzado, jamón, tocino crocante, queso fundido, lechuga y tomate fresco.',
        precio: 11.0,
        categoria: 'Snacks & Sándwiches',
        emoji: '🥪',
    },
    {
        id: 4,
        nombre: 'Café Gourmet Esteliano',
        descripcion: 'Café de estricta altura de Matagalpa/Estelí, recién molido.',
        precio: 3.0,
        categoria: 'Bebidas',
        emoji: '☕',
    },
    {
        id: 5,
        nombre: 'Limonada de Coco Helada',
        descripcion: 'Bebida refrescante de limón criollo con crema de coco orgánico.',
        precio: 4.5,
        categoria: 'Bebidas',
        emoji: '🍹',
    },
];

export const ModalPedidoRestauranteHabitacion = ({
    reserva,
    estaAbierto,
    alCerrar,
}: PropiedadesModalPedidoRestauranteHabitacion) => {
    const [carrito, setCarrito] = useState<Record<number, number>>({});
    const [instrucciones, setInstrucciones] = useState<string>('');
    const [enviado, setEnviado] = useState<boolean>(false);
    const [cargando, setCargando] = useState<boolean>(false);

    if (!estaAbierto || !reserva) {
        return null;
    }

    const cambiarCantidad = (itemId: number, delta: number) => {
        setCarrito((prev) => {
            const actual = prev[itemId] || 0;
            const nueva = Math.max(0, actual + delta);
            if (nueva === 0) {
                const copia = { ...prev };
                delete copia[itemId];
                return copia;
            }
            return { ...prev, [itemId]: nueva };
        });
    };

    const totalCalculado = MENU_ROOM_SERVICE.reduce((acc, item) => {
        const cant = carrito[item.id] || 0;
        return acc + item.precio * cant;
    }, 0);

    const totalItems = Object.values(carrito).reduce((a, b) => a + b, 0);

    const manejarPedido = (e: React.FormEvent) => {
        e.preventDefault();
        if (totalItems === 0) return;

        setCargando(true);
        setTimeout(() => {
            setCargando(false);
            setEnviado(true);
        }, 900);
    };

    const resetearYcerrar = () => {
        setEnviado(false);
        setCarrito({});
        setInstrucciones('');
        alCerrar();
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs animate-in fade-in duration-200">
            <div className="relative w-full max-w-xl overflow-hidden rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-2xl md:p-8">
                {/* Botón de cierre */}
                <button
                    type="button"
                    onClick={resetearYcerrar}
                    className="absolute top-4 right-4 flex size-8 cursor-pointer items-center justify-center rounded-full border border-border bg-background text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    <X className="size-4" />
                </button>

                {enviado ? (
                    <div className="space-y-4 text-center py-6">
                        <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 className="size-8" />
                        </div>
                        <h3 className="text-xl font-black text-foreground">
                            ¡Comanda de Restaurante Enviada!
                        </h3>
                        <p className="text-xs font-medium text-muted-foreground">
                            Su pedido para la reserva <span className="font-mono font-bold text-bugambilia-600 dark:text-bugambilia-400">{reserva.codigo_reserva}</span> ha sido enviado a la cocina del restaurante. Se cargará a su estado de cuenta.
                        </p>
                        <div className="rounded-2xl border border-border/60 bg-muted/30 p-4 text-left space-y-1 text-xs">
                            <p className="font-bold text-foreground">Resumen del Pedido:</p>
                            {MENU_ROOM_SERVICE.filter(i => (carrito[i.id] || 0) > 0).map(i => (
                                <div key={i.id} className="flex justify-between text-muted-foreground">
                                    <span>{carrito[i.id]}x {i.nombre}</span>
                                    <span className="font-mono font-bold">${formatearNumero(i.precio * carrito[i.id])}</span>
                                </div>
                            ))}
                            <div className="border-t border-border/60 pt-2 flex justify-between font-black text-foreground text-sm">
                                <span>Total a Cargar:</span>
                                <span className="text-bugambilia-600 dark:text-bugambilia-400">${formatearNumero(totalCalculado)}</span>
                            </div>
                        </div>
                        <div className="pt-2">
                            <Button
                                onClick={resetearYcerrar}
                                className="w-full rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                            >
                                Aceptar
                            </Button>
                        </div>
                    </div>
                ) : (
                    <form onSubmit={manejarPedido} className="space-y-5">
                        <div className="space-y-1">
                            <Badge className="border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                <Utensils className="mr-1 size-3" />
                                Room Service & Restaurante
                            </Badge>
                            <h2 className="text-xl font-black text-foreground md:text-2xl">
                                Ordenar al Restaurante
                            </h2>
                            <p className="text-xs font-medium text-muted-foreground">
                                Entrega directa en {reserva.detalles} — Reserva: <span className="font-mono font-bold text-bugambilia-600 dark:text-bugambilia-400">{reserva.codigo_reserva}</span>
                            </p>
                        </div>

                        {/* Lista del Menú */}
                        <div className="space-y-2.5 max-h-64 overflow-y-auto pr-1">
                            {MENU_ROOM_SERVICE.map((item) => {
                                const cantidad = carrito[item.id] || 0;
                                return (
                                    <div
                                        key={item.id}
                                        className="flex items-center justify-between gap-3 rounded-2xl border border-border/70 bg-background p-3 shadow-2xs"
                                    >
                                        <div className="flex items-start gap-2.5 grow">
                                            <span className="text-2xl">{item.emoji}</span>
                                            <div>
                                                <span className="block text-xs font-black text-foreground">
                                                    {item.nombre}
                                                </span>
                                                <span className="block text-[10px] font-medium text-muted-foreground line-clamp-1">
                                                    {item.descripcion}
                                                </span>
                                                <span className="mt-0.5 inline-block font-mono text-xs font-extrabold text-bugambilia-600 dark:text-bugambilia-400">
                                                    ${formatearNumero(item.precio)}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Controles de cantidad */}
                                        <div className="flex items-center gap-1.5 shrink-0 rounded-full border border-border/80 bg-muted/40 p-1">
                                            <button
                                                type="button"
                                                onClick={() => cambiarCantidad(item.id, -1)}
                                                className="flex size-7 cursor-pointer items-center justify-center rounded-full bg-background text-foreground transition-colors hover:bg-muted"
                                            >
                                                <Minus className="size-3" />
                                            </button>
                                            <span className="w-5 text-center font-mono text-xs font-extrabold text-foreground">
                                                {cantidad}
                                            </span>
                                            <button
                                                type="button"
                                                onClick={() => cambiarCantidad(item.id, 1)}
                                                className="flex size-7 cursor-pointer items-center justify-center rounded-full bg-bugambilia-600 text-white transition-colors hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                                            >
                                                <Plus className="size-3" />
                                            </button>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                        {/* Instrucciones especiales */}
                        <div className="space-y-1">
                            <Label className="text-xs font-bold text-foreground">
                                Indicaciones para la Cocina / Horario de Entrega
                            </Label>
                            <Input
                                value={instrucciones}
                                onChange={(e) => setInstrucciones(e.target.value)}
                                placeholder="Ej: Sin cebolla, entregar con servilletas adicionales"
                                className="rounded-xl border-border/80 text-xs"
                            />
                        </div>

                        {/* Pie con Total y Botones */}
                        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-border/60 pt-4">
                            <div>
                                <span className="block text-[10px] font-bold text-muted-foreground uppercase">
                                    Total Pedido ({totalItems} items)
                                </span>
                                <span className="font-mono text-lg font-black text-foreground">
                                    ${formatearNumero(totalCalculado)}
                                </span>
                            </div>

                            <div className="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={resetearYcerrar}
                                    className="rounded-full font-bold text-xs"
                                >
                                    Cancelar
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={totalItems === 0 || cargando}
                                    className="rounded-full bg-bugambilia-600 px-5 text-xs font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500 disabled:opacity-50"
                                >
                                    <ShoppingBag className="mr-1.5 size-3.5" />
                                    {cargando ? 'Procesando...' : 'Confirmar Pedido'}
                                </Button>
                            </div>
                        </div>
                    </form>
                )}
            </div>
        </div>
    );
};

export default ModalPedidoRestauranteHabitacion;
