import { Link } from '@inertiajs/react';
import { ShoppingBag, Truck, ArrowRight, Lock } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/modules/shared/components/ui/sheet';
import type { RetornoCarritoRestaurante } from '../hooks/useCarritoRestaurante';
import { RestauranteCarritoItem } from './RestauranteCarritoItem';

interface PropsRestauranteCarritoSheet {
    carrito: RetornoCarritoRestaurante;
    whatsappRestaurante?: string;
    nombreRestaurante?: string;
}

export const RestauranteCarritoSheet = ({
    carrito,
}: PropsRestauranteCarritoSheet) => {
    const {
        items,
        subtotal,
        costoDelivery,
        total,
        moneda,
        sheetAbierto,
        setSheetAbierto,
        agregarItem,
        disminuirItem,
        actualizarNotasItem,
        eliminarItem,
        vaciarCarrito,
    } = carrito;

    return (
        <Sheet open={sheetAbierto} onOpenChange={setSheetAbierto}>
            <SheetContent
                side="right"
                className="flex w-full flex-col overflow-y-auto bg-card p-6 font-sans text-foreground sm:max-w-md"
            >
                <SheetHeader className="border-b border-border/80 pb-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <div className="flex size-8 items-center justify-center rounded-xl bg-primary/10 text-primary dark:text-rose-400">
                                <ShoppingBag className="size-4" />
                            </div>
                            <SheetTitle className="text-base font-black text-foreground">
                                Tu Pedido (Delivery)
                            </SheetTitle>
                        </div>
                    </div>
                    <SheetDescription className="text-xs text-muted-foreground">
                        Revisa tus platillos y continúa al checkout seguro para
                        ingresar dirección y método de pago.
                    </SheetDescription>
                </SheetHeader>

                {items.length === 0 ? (
                    <div className="flex grow flex-col items-center justify-center py-12 text-center text-muted-foreground">
                        <div className="flex size-14 items-center justify-center rounded-full bg-muted/60">
                            <ShoppingBag className="size-6 text-muted-foreground/60" />
                        </div>
                        <h4 className="mt-4 text-sm font-bold text-foreground">
                            Tu carrito está vacío
                        </h4>
                        <p className="mt-1 max-w-xs text-xs text-muted-foreground">
                            Explora nuestro menú y presiona &quot;+ Pedir&quot;
                            para agregar platillos a tu entrega.
                        </p>
                    </div>
                ) : (
                    <div className="flex grow flex-col justify-between pt-4">
                        {/* Lista de Platillos */}
                        <div className="divide-y divide-border/60">
                            <div className="flex items-center justify-between pb-2">
                                <span className="text-xs font-black tracking-wider text-muted-foreground uppercase">
                                    Platillos Seleccionados ({items.length})
                                </span>
                                <button
                                    type="button"
                                    onClick={vaciarCarrito}
                                    className="cursor-pointer text-[11px] font-bold text-rose-500 hover:underline"
                                >
                                    Vaciar todo
                                </button>
                            </div>

                            <div className="max-h-80 space-y-3 overflow-y-auto py-3 pr-1">
                                {items.map((item) => (
                                    <RestauranteCarritoItem
                                        key={item.plato.id}
                                        item={item}
                                        moneda={moneda}
                                        onAgregar={agregarItem}
                                        onDisminuir={disminuirItem}
                                        onEliminar={eliminarItem}
                                        onGuardarNotas={actualizarNotasItem}
                                    />
                                ))}
                            </div>
                        </div>

                        {/* Resumen y Botón de Checkout */}
                        <div className="mt-4 space-y-3 border-t border-border/80 pt-4 text-xs">
                            <div className="space-y-1.5">
                                <div className="flex justify-between text-muted-foreground">
                                    <span>Subtotal:</span>
                                    <span>
                                        {moneda} {subtotal.toFixed(2)}
                                    </span>
                                </div>
                                <div className="flex justify-between text-muted-foreground">
                                    <span className="inline-flex items-center gap-1">
                                        <Truck className="size-3.5 text-emerald-500" />
                                        Envío estimado:
                                    </span>
                                    <span>
                                        {moneda} {costoDelivery.toFixed(2)}
                                    </span>
                                </div>
                                <div className="flex justify-between border-t border-border/60 pt-2 text-sm font-black text-foreground">
                                    <span>Total estimado:</span>
                                    <span className="text-primary dark:text-rose-400">
                                        {moneda} {total.toFixed(2)}
                                    </span>
                                </div>
                            </div>

                            {/* Botón Principal para ir a Checkout */}
                            <Link
                                href="/restaurante/checkout"
                                onClick={() => setSheetAbierto(false)}
                                className="block w-full"
                            >
                                <Button
                                    type="button"
                                    className="flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-primary text-xs font-black text-primary-foreground shadow-lg transition-all hover:bg-primary/90 active:scale-95"
                                >
                                    <span>Proceder al Checkout Seguro</span>
                                    <ArrowRight className="size-4" />
                                </Button>
                            </Link>

                            <div className="flex items-center justify-center gap-2 pt-1 text-[10px] text-muted-foreground">
                                <Lock className="size-3 text-emerald-600 dark:text-emerald-400" />
                                <span>
                                    Aceptamos Efectivo y Tarjeta con Stripe
                                </span>
                            </div>
                        </div>
                    </div>
                )}
            </SheetContent>
        </Sheet>
    );
};

export default RestauranteCarritoSheet;
