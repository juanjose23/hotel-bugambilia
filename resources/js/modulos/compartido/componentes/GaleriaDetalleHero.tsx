import { Maximize2, Tag } from 'lucide-react';
import React, { useState } from 'react';
import { VisorGaleriaModal } from './VisorGaleriaModal';

interface PropiedadesGaleriaDetalleHero {
    imagenes: string[];
    nombre: string;
    codigo?: string;
    categoria?: string;
    className?: string;
}

export const GaleriaDetalleHero: React.FC<PropiedadesGaleriaDetalleHero> = ({
    imagenes = [],
    nombre,
    codigo,
    categoria,
    className = '',
}) => {
    const [activeImageIndex, setActiveImageIndex] = useState(0);
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);

    const imagenesProcesadas =
        imagenes && imagenes.length > 0 ? imagenes : ['/images/main-room.webp'];
    const currentImage =
        imagenesProcesadas[activeImageIndex] || imagenesProcesadas[0];

    return (
        <div
            className={`w-full max-w-full space-y-3 font-sans sm:space-y-4 ${className}`}
        >
            {/* Foto Principal */}
            <div
                onClick={() => setIsLightboxOpen(true)}
                className="group relative aspect-16/10 w-full max-w-full cursor-pointer overflow-hidden rounded-3xl border border-border/80 bg-muted/40 shadow-lg transition-all duration-300 hover:shadow-xl"
            >
                <img
                    src={currentImage}
                    alt={nombre}
                    className="size-full object-cover transition-transform duration-700 group-hover:scale-105"
                />

                <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-90" />

                {categoria && (
                    <div className="absolute top-3 left-3 z-10 sm:top-4 sm:left-4">
                        <span className="rounded-full border border-white/20 bg-black/60 px-3 py-1 text-[11px] font-extrabold tracking-wider text-white uppercase backdrop-blur-md sm:px-3.5 sm:py-1.5 sm:text-xs">
                            {categoria}
                        </span>
                    </div>
                )}

                <button
                    type="button"
                    onClick={(e) => {
                        e.stopPropagation();
                        setIsLightboxOpen(true);
                    }}
                    className="absolute top-3 right-3 z-10 flex size-8 items-center justify-center rounded-full border border-white/30 bg-black/50 text-white backdrop-blur-md transition-all hover:scale-110 hover:bg-black/70 sm:top-4 sm:right-4 sm:size-10"
                    title="Ver en pantalla completa"
                >
                    <Maximize2 className="size-3.5 sm:size-4" />
                </button>

                <div className="absolute right-3 bottom-3 left-3 z-10 flex flex-col justify-end text-white sm:right-6 sm:bottom-6 sm:left-6">
                    {codigo && (
                        <span className="mb-0.5 inline-flex items-center gap-1 text-[9px] font-black tracking-widest text-white/90 uppercase sm:mb-1 sm:text-[10px]">
                            <Tag className="size-3 text-amber-400" />
                            {codigo}
                        </span>
                    )}
                    <h1 className="line-clamp-2 text-lg leading-tight font-black text-white drop-shadow-md sm:text-2xl md:text-3xl">
                        {nombre}
                    </h1>
                </div>
            </div>

            {/* Miniaturas de la Galería */}
            {imagenesProcesadas.length > 1 && (
                <div className="no-scrollbar flex w-full max-w-full gap-2 overflow-x-auto pb-1.5 sm:gap-3 sm:pb-2">
                    {imagenesProcesadas.map((img, idx) => (
                        <button
                            key={idx}
                            type="button"
                            onClick={() => setActiveImageIndex(idx)}
                            className={`relative h-16 w-22 shrink-0 overflow-hidden rounded-2xl border-2 transition-all sm:h-20 sm:w-28 ${
                                activeImageIndex === idx
                                    ? 'scale-105 border-bugambilia-600 shadow-md'
                                    : 'border-transparent opacity-70 hover:opacity-100'
                            }`}
                        >
                            <img
                                src={img}
                                alt={`${nombre} ${idx + 1}`}
                                className="size-full object-cover"
                            />
                        </button>
                    ))}
                </div>
            )}

            {/* Modal de Pantalla Completa */}
            <VisorGaleriaModal
                estaAbierto={isLightboxOpen}
                alCerrar={() => setIsLightboxOpen(false)}
                imagenes={imagenesProcesadas}
                indiceImagenActiva={activeImageIndex}
                alSeleccionarImagen={setActiveImageIndex}
                titulo={nombre}
            />
        </div>
    );
};

export default GaleriaDetalleHero;
