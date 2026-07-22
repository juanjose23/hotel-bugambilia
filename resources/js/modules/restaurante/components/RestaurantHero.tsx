import {
    Sparkles,
    Utensils,
    Clock,
    MessageCircle,
    ArrowDown,
    ChevronRight,
} from 'lucide-react';
import { useState } from 'react';
import type { RestauranteData } from '@/modules/restaurante/types';

interface RestaurantHeroProps {
    restaurante: RestauranteData;
    onScrollToSection: (sectionId: string) => void;
    whatsappNumber?: string;
}

export default function RestaurantHero({
    restaurante,
    onScrollToSection,
    whatsappNumber,
}: RestaurantHeroProps) {
    const [activeImageIndex, setActiveImageIndex] = useState(0);
    const images =
        restaurante.imagenes && restaurante.imagenes.length > 0
            ? restaurante.imagenes
            : [
                  '/images/terrace.jpg',
                  '/images/service-kitchen.png',
                  '/images/service-bartender.png',
              ];

    const currentImage = images[activeImageIndex] || images[0];

    return (
        <section className="relative flex min-h-[85vh] items-center justify-center overflow-hidden bg-zinc-950 pt-20 pb-16 text-white">
            {/* Background Image Carousel / Transition */}
            <div className="absolute inset-0 z-0">
                <img
                    src={currentImage}
                    alt={restaurante.nombre}
                    className="h-full w-full scale-105 object-cover object-center transition-all duration-1000 ease-out"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-background via-black/70 to-black/40" />
                <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-500/10 via-transparent to-black/80" />
            </div>

            <div className="relative z-10 container mx-auto max-w-6xl px-4">
                <div className="max-w-3xl">
                    {/* Badge */}
                    <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-500/20 px-4 py-1.5 text-xs font-black tracking-widest text-amber-300 uppercase shadow-lg shadow-amber-500/10 backdrop-blur-md">
                        <Sparkles className="h-3.5 w-3.5 animate-pulse text-amber-400" />
                        Gastronomía Bugambilias
                    </div>

                    {/* Main Title */}
                    <h1 className="mb-6 text-4xl leading-[1.08] font-black tracking-tight text-white sm:text-6xl md:text-7xl">
                        {restaurante.nombre}
                    </h1>

                    {/* Description / Subtitle */}
                    <p className="mb-8 max-w-2xl text-lg leading-relaxed font-medium text-zinc-300 md:text-xl">
                        {restaurante.descripcion ||
                            'Disfrute de una propuesta gastronómica excepcional con ingredientes frescos de Nicaragua y cortes selectos en ambientes de alta elegancia.'}
                    </p>

                    {/* Feature Badges */}
                    <div className="mb-10 flex flex-wrap items-center gap-3 text-xs font-extrabold">
                        <span className="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-3 py-1.5 text-zinc-200 backdrop-blur-md">
                            <Utensils className="h-4 w-4 text-amber-400" />
                            {restaurante.tipo_cocina}
                        </span>
                        <span className="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-3 py-1.5 text-zinc-200 backdrop-blur-md">
                            <Clock className="h-4 w-4 text-amber-400" />
                            {restaurante.tipo_servicio}
                        </span>
                        <span className="inline-flex items-center gap-2 rounded-xl border border-amber-400/30 bg-amber-500/30 px-3 py-1.5 text-amber-200 backdrop-blur-md">
                            Capacidad para {restaurante.capacidad} personas
                        </span>
                    </div>

                    {/* CTA Buttons */}
                    <div className="flex flex-wrap items-center gap-4">
                        <button
                            onClick={() =>
                                onScrollToSection('ambientes-section')
                            }
                            className="inline-flex cursor-pointer items-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 px-7 py-4 text-sm font-black text-zinc-950 shadow-xl shadow-amber-500/20 transition-all hover:scale-[1.03] hover:from-amber-600 hover:to-amber-700"
                        >
                            Explorar Ambientes
                            <ChevronRight className="h-4 w-4" />
                        </button>

                        <button
                            onClick={() => onScrollToSection('menu-section')}
                            className="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-7 py-4 text-sm font-bold text-white backdrop-blur-md transition-all hover:scale-[1.02] hover:bg-white/20"
                        >
                            Ver Menú Digital
                            <ArrowDown className="h-4 w-4 text-amber-400" />
                        </button>

                        {whatsappNumber && (
                            <a
                                href={`https://wa.me/${whatsappNumber.replace(/[^0-9]/g, '')}?text=Hola,%20quisiera%20reservar%20una%20mesa%20en%20el%20Restaurante%20Bugambilias`}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-emerald-400/30 bg-emerald-600/90 px-6 py-4 text-sm font-bold text-white backdrop-blur-md transition-all hover:scale-[1.02] hover:bg-emerald-600"
                            >
                                <MessageCircle className="h-4 w-4" />
                                Reservar por WhatsApp
                            </a>
                        )}
                    </div>
                </div>

                {/* Thumbnail Selector */}
                {images.length > 1 && (
                    <div className="mt-16 flex scrollbar-none items-center gap-3 overflow-x-auto pb-2">
                        {images.map((img, idx) => (
                            <button
                                key={idx}
                                onClick={() => setActiveImageIndex(idx)}
                                className={`relative h-16 w-24 shrink-0 cursor-pointer overflow-hidden rounded-xl border-2 transition-all ${
                                    activeImageIndex === idx
                                        ? 'scale-105 border-amber-400 ring-2 ring-amber-400/50'
                                        : 'border-white/20 opacity-60 hover:opacity-100'
                                }`}
                            >
                                <img
                                    src={img}
                                    alt={`Fotografía ${idx + 1}`}
                                    className="h-full w-full object-cover"
                                />
                            </button>
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
