import { X } from 'lucide-react';

interface ModalLightboxProps {
    isOpen: boolean;
    onClose: () => void;
    imagenes: string[];
    activeImageIndex: number;
    onSelectImage: (index: number) => void;
    title?: string;
}

export default function ModalLightbox({
    isOpen,
    onClose,
    imagenes,
    activeImageIndex,
    onSelectImage,
    title,
}: ModalLightboxProps) {
    if (!isOpen || !imagenes || imagenes.length === 0) {
        return null;
    }

    const currentImage = imagenes[activeImageIndex] || imagenes[0];

    return (
        <div className="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/95 p-4 font-sans backdrop-blur-xl duration-200">
            <button
                onClick={onClose}
                className="absolute top-6 right-6 z-50 cursor-pointer rounded-full bg-white/10 p-3 text-white transition-colors hover:bg-white/20"
                aria-label="Cerrar vista previa"
            >
                <X className="h-6 w-6" />
            </button>

            <div className="flex max-h-[85vh] w-full max-w-5xl flex-col items-center">
                {title && (
                    <h3 className="mb-3 text-base font-bold text-white">
                        {title}
                    </h3>
                )}

                <img
                    src={currentImage}
                    alt={title || 'Vista previa'}
                    className="mb-4 max-h-[75vh] w-auto max-w-full rounded-2xl object-contain shadow-2xl"
                />

                {imagenes.length > 1 && (
                    <div className="flex items-center gap-2 overflow-x-auto p-2">
                        {imagenes.map((img, idx) => (
                            <button
                                key={idx}
                                onClick={() => onSelectImage(idx)}
                                className={`relative h-16 w-16 cursor-pointer overflow-hidden rounded-xl border-2 transition-all ${
                                    activeImageIndex === idx
                                        ? 'scale-105 border-amber-400'
                                        : 'border-white/30 opacity-60 hover:opacity-100'
                                }`}
                            >
                                <img
                                    src={img}
                                    alt={`Galería ${idx + 1}`}
                                    className="h-full w-full object-cover"
                                />
                            </button>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
