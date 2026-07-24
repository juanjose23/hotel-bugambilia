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
        <div className={`space-y-4 font-sans ${className}`}>
            {/* Foto Principal */}
            <div
                onClick={() => setIsLightboxOpen(true)}
                className="group relative aspect-16/10 cursor-pointer overflow-hidden rounded-3xl border border-border/80 bg-muted/40 shadow-lg transition-all duration-300 hover:shadow-xl"
            >
                <img
                    src={currentImage}
                    alt={nombre}
                    className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                />

                <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent" />

                {categoria && (
                    <div className="absolute top-4 left-4 z-10">
                        <span className="rounded-full border border-white/20 bg-black/60 px-3.5 py-1.5 text-xs font-extrabold tracking-wider text-white uppercase backdrop-blur-md">
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
                    className="absolute top-4 right-4 z-10 flex h-10 w-10 items-center justify-center rounded-full border border-white/30 bg-black/50 text-white backdrop-blur-md transition-all hover:scale-110 hover:bg-black/70"
                    title="Ver en pantalla completa"
                >
                    <Maximize2 className="h-4 w-4" />
                </button>

                <div className="absolute right-6 bottom-6 left-6 flex items-end justify-between text-white">
                    <div>
                        {codigo && (
                            <span className="mb-1 inline-flex items-center gap-1 text-[10px] font-black tracking-widest text-white/80 uppercase">
                                <Tag className="h-3 w-3 text-amber-400" />
                                {codigo}
                            </span>
                        )}
                        <h1 className="text-2xl font-black md:text-3xl">
                            {nombre}
                        </h1>
                    </div>
                </div>
            </div>

            {/* Miniaturas de la Galería */}
            {imagenesProcesadas.length > 1 && (
                <div className="flex gap-3 overflow-x-auto pb-2">
                    {imagenesProcesadas.map((img, idx) => (
                        <button
                            key={idx}
                            type="button"
                            onClick={() => setActiveImageIndex(idx)}
                            className={`relative h-20 w-28 shrink-0 overflow-hidden rounded-2xl border-2 transition-all ${
                                activeImageIndex === idx
                                    ? 'scale-105 border-bugambilia-600 shadow-md'
                                    : 'border-transparent opacity-70 hover:opacity-100'
                            }`}
                        >
                            <img
                                src={img}
                                alt={`${nombre} ${idx + 1}`}
                                className="h-full w-full object-cover"
                            />
                        </button>
                    ))}
                </div>
            )}

            {/* Modal Lightbox Reutilizable */}
            <VisorGaleriaModal
                estaAbierto={isLightboxOpen}
                alCerrar={() => setIsLightboxOpen(false)}
                imagenes={imagenesProcesadas}
                indiceImagenActiva={activeImageIndex}
                alSeleccionarImagen={(idx) => setActiveImageIndex(idx)}
                titulo={nombre}
            />
        </div>
    );
};
