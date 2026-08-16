import { Maximize2 } from 'lucide-react';
import { Card } from '@/modulos/compartido/ui/tarjeta';
import type { ElementoGaleria } from '../../interfaces/acercaDeInterfaces';

interface PropiedadesTarjetaGaleriaItem {
    item: ElementoGaleria;
    index: number;
    onAbrirVisor: (index: number) => void;
}

export function TarjetaGaleriaItem({
    item,
    index,
    onAbrirVisor,
}: PropiedadesTarjetaGaleriaItem) {
    return (
        <Card
            onClick={() => onAbrirVisor(index)}
            className="group relative cursor-pointer overflow-hidden rounded-3xl border-border/80 bg-card transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
        >
            <div className="relative aspect-4/3 w-full overflow-hidden">
                <img
                    src={item.src}
                    alt={item.alt}
                    loading="lazy"
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                <div className="absolute top-4 right-4 flex size-10 items-center justify-center rounded-full bg-black/40 text-white opacity-0 backdrop-blur-md transition-all duration-300 group-hover:opacity-100">
                    <Maximize2 className="size-4" />
                </div>
                <div className="absolute right-4 bottom-4 left-4 translate-y-2 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                    <span className="mb-1 block text-[10px] font-extrabold tracking-wider text-bugambilia-400 uppercase">
                        {item.category}
                    </span>
                    <h3 className="text-sm font-black text-white">
                        {item.title}
                    </h3>
                </div>
            </div>
        </Card>
    );
}
