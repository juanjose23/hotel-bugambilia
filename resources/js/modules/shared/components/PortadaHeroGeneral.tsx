import { MapPin, Award, ChevronLeft, ChevronRight } from 'lucide-react';
import React, { useState, useEffect, useCallback } from 'react';

export interface PropiedadesPortadaHeroGeneral {
    imagenFondo: string;
    titulo: string;
    tituloEnfasis?: string;
    subtitulo?: string;
    descripcion?: string;
    badgeLabel?: string;
    badgeIcon?: React.ElementType;
    badgeStyle?: string;
    ubicacion?: string;
    acciones?: React.ReactNode;
    alturaClass?: string;
    overlayGradient?: string;
    carruselImagenes?: string[];
    indiceImagenActiva?: number;
    alSeleccionarImagenCarrusel?: (idx: number) => void;
    className?: string;
}

export const PortadaHeroGeneral: React.FC<PropiedadesPortadaHeroGeneral> = ({
    imagenFondo,
    titulo,
    tituloEnfasis,
    subtitulo,
    descripcion,
    badgeLabel,
    badgeIcon: BadgeIcon = Award,
    badgeStyle = 'border-amber-400/40 bg-amber-500/20 text-amber-300',
    ubicacion,
    acciones,
    alturaClass = 'h-[55vh] max-h-[600px] min-h-[440px]',
    overlayGradient = 'from-black/95 via-black/75 to-black/40',
    carruselImagenes = [],
    indiceImagenActiva,
    alSeleccionarImagenCarrusel,
    className = '',
}) => {
    const [localIndex, setLocalIndex] = useState(0);

    const activeIndex =
        indiceImagenActiva !== undefined ? indiceImagenActiva : localIndex;

    const handleSelectImage = useCallback(
        (idx: number) => {
            setLocalIndex(idx);
            alSeleccionarImagenCarrusel?.(idx);
        },
        [alSeleccionarImagenCarrusel],
    );

    const prevImage = useCallback(() => {
        if (!carruselImagenes || carruselImagenes.length <= 1) {
            return;
        }

        const prevIdx =
            (activeIndex - 1 + carruselImagenes.length) %
            carruselImagenes.length;
        handleSelectImage(prevIdx);
    }, [activeIndex, carruselImagenes, handleSelectImage]);

    const nextImage = useCallback(() => {
        if (!carruselImagenes || carruselImagenes.length <= 1) {
            return;
        }

        const nextIdx = (activeIndex + 1) % carruselImagenes.length;
        handleSelectImage(nextIdx);
    }, [activeIndex, carruselImagenes, handleSelectImage]);

    // Auto-play carousel ONLY when uncontrolled (when parent does not manage active index)
    useEffect(() => {
        if (
            indiceImagenActiva !== undefined ||
            !carruselImagenes ||
            carruselImagenes.length <= 1
        ) {
            return;
        }

        const timer = setInterval(() => {
            setLocalIndex((prev) => (prev + 1) % carruselImagenes.length);
        }, 5000);

        return () => clearInterval(timer);
    }, [carruselImagenes, indiceImagenActiva]);

    // Accessibility Keyboard Controls (Left / Right Arrows)
    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            prevImage();
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            nextImage();
        }
    };

    return (
        <section
            role="region"
            aria-roledescription="carrusel"
            aria-label={`Portada principal: ${titulo}`}
            tabIndex={0}
            onKeyDown={handleKeyDown}
            className={`relative flex items-center justify-center overflow-hidden font-sans focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2 focus-visible:ring-offset-black ${alturaClass} ${className}`}
        >
            {/* Background Images Carousel */}
            <div className="absolute inset-0 z-0" aria-live="polite">
                {carruselImagenes && carruselImagenes.length > 0 ? (
                    carruselImagenes.map((img, idx) => (
                        <img
                            key={idx}
                            src={img}
                            alt={`${titulo} - Fotografía ${idx + 1}`}
                            aria-hidden={activeIndex !== idx}
                            className={`absolute inset-0 h-full w-full object-cover object-center transition-opacity duration-1000 ease-in-out ${
                                activeIndex === idx
                                    ? 'scale-105 opacity-100'
                                    : 'scale-100 opacity-0'
                            }`}
                        />
                    ))
                ) : (
                    <img
                        src={imagenFondo}
                        alt={titulo}
                        className="h-full w-full scale-105 object-cover object-center transition-all duration-700 ease-out"
                    />
                )}

                <div
                    className={`absolute inset-0 bg-gradient-to-r ${overlayGradient}`}
                />
                <div className="bugambilia-pattern absolute inset-0 opacity-15" />
            </div>

            {/* Mobile & Desktop Transparent Glassmorphism Arrow Controls */}
            {carruselImagenes && carruselImagenes.length > 1 && (
                <>
                    <button
                        type="button"
                        onClick={prevImage}
                        className="absolute top-1/2 left-3 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white backdrop-blur-md transition-all duration-300 hover:scale-105 hover:bg-white/25 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2 focus-visible:ring-offset-black active:scale-95 sm:left-6"
                        aria-label="Ver imagen anterior de la portada"
                        title="Ver imagen anterior"
                    >
                        <ChevronLeft className="h-5 w-5" />
                        <span className="sr-only">Imagen anterior</span>
                    </button>

                    <button
                        type="button"
                        onClick={nextImage}
                        className="absolute top-1/2 right-3 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white backdrop-blur-md transition-all duration-300 hover:scale-105 hover:bg-white/25 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2 focus-visible:ring-offset-black active:scale-95 sm:right-6"
                        aria-label="Ver siguiente imagen de la portada"
                        title="Ver siguiente imagen"
                    >
                        <ChevronRight className="h-5 w-5" />
                        <span className="sr-only">Siguiente imagen</span>
                    </button>

                    {/* Transparent Pill Dot Indicators */}
                    <div
                        className="absolute bottom-5 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2.5 rounded-full border border-white/15 bg-black/40 px-3.5 py-1.5 backdrop-blur-md"
                        role="tablist"
                        aria-label="Puntos de navegación del carrusel"
                    >
                        {carruselImagenes.map((_, idx) => (
                            <button
                                key={idx}
                                type="button"
                                role="tab"
                                aria-selected={activeIndex === idx}
                                aria-label={`Ver diapositiva ${idx + 1} de ${carruselImagenes.length}`}
                                onClick={() => handleSelectImage(idx)}
                                className={`h-2 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 ${
                                    activeIndex === idx
                                        ? 'w-7 bg-amber-400'
                                        : 'w-2 bg-white/40 hover:bg-white/80'
                                }`}
                            >
                                <span className="sr-only">
                                    Diapositiva {idx + 1}
                                </span>
                            </button>
                        ))}
                    </div>
                </>
            )}

            {/* Main Content Container */}
            <div className="relative z-10 container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="max-w-3xl text-white">
                    {/* Badge / Insignia */}
                    {badgeLabel && (
                        <div
                            className={`mb-4 inline-flex items-center gap-2 rounded-full border px-3.5 py-1 text-xs font-extrabold tracking-widest uppercase backdrop-blur-md ${badgeStyle}`}
                        >
                            <BadgeIcon
                                className="h-3.5 w-3.5"
                                aria-hidden="true"
                            />
                            <span>{badgeLabel}</span>
                        </div>
                    )}

                    {/* Título Principal */}
                    <h1 className="mb-4 text-3xl leading-tight font-black tracking-tight text-white drop-shadow-md sm:text-5xl lg:text-6xl">
                        {titulo}{' '}
                        {tituloEnfasis && (
                            <span className="font-serif font-normal text-amber-300 italic">
                                {tituloEnfasis}
                            </span>
                        )}
                        {subtitulo && (
                            <span className="mt-2 block text-xl font-normal text-white/90 sm:text-2xl">
                                {subtitulo}
                            </span>
                        )}
                    </h1>

                    {/* Descripción */}
                    {descripcion && (
                        <p className="mb-6 max-w-2xl text-sm leading-relaxed font-medium text-white/90 drop-shadow-sm sm:text-base md:text-lg">
                            {descripcion}
                        </p>
                    )}

                    {/* Ubicación */}
                    {ubicacion && (
                        <div className="mb-6 flex items-center gap-2 text-sm font-semibold text-white/90">
                            <MapPin
                                className="h-4.5 w-4.5 text-amber-400"
                                aria-hidden="true"
                            />
                            <span>{ubicacion}</span>
                        </div>
                    )}

                    {/* Acciones (Botones CTA) */}
                    {acciones && (
                        <div className="flex flex-wrap items-center gap-3 pt-1">
                            {acciones}
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
};
