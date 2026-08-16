import { X } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import type { EspacioItem } from '../../interfaces/espacioInterfaces';

interface PropiedadesModalGaleriaEspacio {
    open: boolean;
    espacio?: EspacioItem;
    imgIndex: number;
    setImgIndex: (index: number) => void;
    onClose: () => void;
}

export const ModalGaleriaEspacio = ({
    open,
    espacio,
    imgIndex,
    setImgIndex,
    onClose,
}: PropiedadesModalGaleriaEspacio) => {
    if (!open || !espacio) {
        return null;
    }

    const imagenes =
        espacio.imagenes && espacio.imagenes.length > 0
            ? espacio.imagenes
            : ['/images/terrace.webp'];

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 font-sans backdrop-blur-md">
            <div className="relative w-full max-w-4xl rounded-3xl border border-border bg-card p-6 shadow-2xl">
                <div className="mb-4 flex items-center justify-between border-b border-border/50 pb-4">
                    <div>
                        <h3 className="text-xl font-black text-foreground">
                            {espacio.nombre}
                        </h3>
                        <p className="text-xs text-muted-foreground">
                            {espacio.tipo_label || espacio.tipo} •{' '}
                            {imagenes.length} fotografías
                        </p>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={onClose}
                        className="rounded-full"
                    >
                        <X className="size-5" />
                    </Button>
                </div>

                <div className="relative mb-4 aspect-16/9 w-full overflow-hidden rounded-2xl bg-black/40">
                    <img
                        src={imagenes[imgIndex]}
                        alt={`${espacio.nombre} fotograma ${imgIndex + 1}`}
                        className="h-full w-full object-contain"
                    />
                </div>

                {imagenes.length > 1 && (
                    <div className="no-scrollbar flex items-center gap-2 overflow-x-auto py-2">
                        {imagenes.map((img, idx) => (
                            <button
                                key={idx}
                                type="button"
                                onClick={() => setImgIndex(idx)}
                                className={`relative h-16 w-24 shrink-0 cursor-pointer overflow-hidden rounded-xl border-2 transition-all ${
                                    idx === imgIndex
                                        ? 'scale-105 border-bugambilia-600 shadow-md'
                                        : 'border-transparent opacity-60 hover:opacity-100'
                                }`}
                            >
                                <img
                                    src={img}
                                    alt=""
                                    className="h-full w-full object-cover"
                                />
                            </button>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};
