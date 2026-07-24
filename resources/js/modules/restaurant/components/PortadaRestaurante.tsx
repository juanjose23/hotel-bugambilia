import {
    Utensils,
    Clock,
    MessageCircle,
    ArrowDown,
    ChevronRight,
} from 'lucide-react';
import { useState } from 'react';
import type { RestauranteData } from '@/modules/restaurant/types';
import { PortadaHeroGeneral } from '@/modules/shared/components/PortadaHeroGeneral';

interface PortadaRestauranteProps {
    restaurante: RestauranteData;
    onScrollToSection: (sectionId: string) => void;
    whatsappNumber?: string;
}

const PortadaRestaurante = ({
    restaurante,
    onScrollToSection,
    whatsappNumber,
}: PortadaRestauranteProps) => {
    const [activeImageIndex, setActiveImageIndex] = useState(0);
    const images =
        restaurante.imagenes && restaurante.imagenes.length > 0
            ? restaurante.imagenes
            : [
                  '/images/terrace.webp',
                  '/images/service-kitchen.webp',
                  '/images/service-bartender.webp',
              ];
    const currentImage = images[activeImageIndex] || images[0];

    return (
        <PortadaHeroGeneral
            imagenFondo={currentImage}
            alturaClass="min-h-[85vh] pt-20 pb-16"
            badgeLabel="Gastronomía Bugambilias"
            badgeIcon={Utensils}
            badgeStyle="border-amber-400/40 bg-amber-500/20 text-amber-300"
            titulo={restaurante.nombre}
            descripcion={
                restaurante.descripcion ||
                'Disfrute de una propuesta gastronómica excepcional con ingredientes frescos de Nicaragua y cortes selectos en ambientes de alta elegancia.'
            }
            carruselImagenes={images}
            indiceImagenActiva={activeImageIndex}
            alSeleccionarImagenCarrusel={(idx) => setActiveImageIndex(idx)}
            acciones={
                <div className="space-y-6">
                    {/* Feature Badges */}
                    <div className="flex flex-wrap items-center gap-3 text-xs font-extrabold">
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
                            type="button"
                            onClick={() =>
                                onScrollToSection('ambientes-section')
                            }
                            className="inline-flex cursor-pointer items-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 px-7 py-4 text-sm font-black text-zinc-950 shadow-xl shadow-amber-500/20 transition-all hover:scale-[1.03] hover:from-amber-600 hover:to-amber-700"
                        >
                            Explorar Ambientes
                            <ChevronRight className="h-4 w-4" />
                        </button>

                        <button
                            type="button"
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
            }
        />
    );
};

export default PortadaRestaurante;
