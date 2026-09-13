import { ShoppingBag, ArrowRight } from 'lucide-react';
import type { RetornoCarritoRestaurante } from '../hooks/useCarritoRestaurante';

interface PropsRestauranteCarritoFlotante {
    carrito: RetornoCarritoRestaurante;
}

export const RestauranteCarritoFlotante = ({
    carrito,
}: PropsRestauranteCarritoFlotante) => {
    const { totalItems, total, moneda, setSheetAbierto } = carrito;

    if (totalItems === 0) {
        return null;
    }

    return (
        <aside
            aria-label="Carrito de compras flotante"
            className="animate-in fade-in slide-in-from-bottom-5 fixed right-4 bottom-20 z-40 duration-300 sm:right-6 sm:bottom-6"
        >
            <button
                type="button"
                onClick={() => setSheetAbierto(true)}
                className="group flex cursor-pointer items-center gap-3 rounded-full border-2 border-white/20 bg-emerald-600 px-5 py-3 text-white shadow-2xl transition-all duration-300 hover:scale-105 hover:bg-emerald-500 active:scale-95"
            >
                <div className="relative flex size-7 items-center justify-center rounded-full bg-black/20 text-white">
                    <ShoppingBag className="size-4" />
                    <span className="absolute -top-1.5 -right-1.5 flex size-4.5 items-center justify-center rounded-full bg-amber-400 text-[10px] font-black text-black shadow-xs">
                        {totalItems}
                    </span>
                </div>

                <div className="flex flex-col text-left">
                    <span className="text-[10px] font-extrabold tracking-wider text-emerald-100 uppercase">
                        Ver Mi Pedido
                    </span>
                    <span className="text-xs font-black">
                        {moneda} {total.toFixed(2)}
                    </span>
                </div>

                <ArrowRight className="size-4 transition-transform group-hover:translate-x-1" />
            </button>
        </aside>
    );
};
