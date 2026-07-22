import { usePage } from '@inertiajs/react';
import { Maximize2, Sparkles } from 'lucide-react';
import { useState } from 'react';
import VisorGaleriaModal from '@/modules/compartido/componentes/VisorGaleriaModal';

const galleryItems = [
    {
        id: 1,
        src: '/images/hero-main.jpg',
        alt: 'Vista panorámica del hotel',
        title: 'Fachada & Entrada Principal',
        category: 'Instalaciones',
    },
    {
        id: 2,
        src: '/images/pool-front-view.jpg',
        alt: 'Piscina y solárium al atardecer',
        title: 'Piscina & Solárium Tropical',
        category: 'Piscina & Terraza',
    },
    {
        id: 3,
        src: '/images/main-room.jpg',
        alt: 'Habitación Matrimonial King',
        title: 'Habitación Matrimonial King',
        category: 'Habitaciones & Suites',
    },
    {
        id: 4,
        src: '/images/terrace.jpg',
        alt: 'Terraza Lounge y jardines',
        title: 'Terraza Lounge al Aire Libre',
        category: 'Piscina & Terraza',
    },
    {
        id: 5,
        src: '/images/service-kitchen.png',
        alt: 'Restaurante gourmet nicaragüense',
        title: 'Restaurante & Experiencia Culinaria',
        category: 'Gastronomía',
    },
    {
        id: 6,
        src: '/images/group-room.jpg',
        alt: 'Suite Familiar de lujo',
        title: 'Suite Doble Familiar',
        category: 'Habitaciones & Suites',
    },
    {
        id: 7,
        src: '/images/service-pool.png',
        alt: 'Área de relajación en la piscina',
        title: 'Área de Descanso Junto al Agua',
        category: 'Piscina & Terraza',
    },
    {
        id: 8,
        src: '/images/bathroom.jpg',
        alt: 'Baño privado de mármol',
        title: 'Baño Privado & Acabados de Lujo',
        category: 'Habitaciones & Suites',
    },
    {
        id: 9,
        src: '/images/service-bartender.png',
        alt: 'Bar y coctelería artesanal',
        title: 'Bar & Coctelería Artesanal',
        category: 'Gastronomía',
    },
];

const categories = [
    'Todas',
    'Habitaciones & Suites',
    'Piscina & Terraza',
    'Gastronomía',
    'Instalaciones',
];

export default function AboutGallery() {
    const pageProps = usePage().props;
    const hotelName = pageProps.hotel?.name || 'Hotel Bugambilias';

    const [activeCategory, setActiveCategory] = useState('Todas');
    const [activeImageIndex, setActiveImageIndex] = useState<number | null>(
        null,
    );

    const filteredItems =
        activeCategory === 'Todas'
            ? galleryItems
            : galleryItems.filter((item) => item.category === activeCategory);

    const imagenesUrls = filteredItems.map((item) => item.src);

    return (
        <section className="border-b border-border/40 bg-background py-16 font-sans md:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="mx-auto mb-12 max-w-3xl text-center">
                    <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-bugambilia-500/20 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-extrabold tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                        <Sparkles className="h-3.5 w-3.5" />
                        Galería Fotográfica
                    </div>
                    <h2 className="mb-4 text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Explore nuestras{' '}
                        <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                            Instalaciones
                        </span>
                    </h2>
                    <p className="text-sm font-medium text-muted-foreground sm:text-base">
                        Un recorrido visual por la arquitectura, habitaciones,
                        gastronomía y áreas verdes de {hotelName}.
                    </p>
                </div>

                {/* Category Pills Filter */}
                <div className="mb-10 flex flex-wrap items-center justify-center gap-2">
                    {categories.map((cat) => (
                        <button
                            key={cat}
                            onClick={() => {
                                setActiveCategory(cat);
                                setActiveImageIndex(null);
                            }}
                            className={`cursor-pointer rounded-full px-4 py-2 text-xs font-extrabold tracking-wider uppercase transition-all duration-200 ${
                                activeCategory === cat
                                    ? 'shadow-airbnb scale-105 bg-bugambilia-600 text-white'
                                    : 'border border-border/80 bg-card text-muted-foreground hover:border-gray-400 hover:text-foreground dark:hover:border-gray-600'
                            }`}
                        >
                            {cat}
                        </button>
                    ))}
                </div>

                {/* Photo Grid */}
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {filteredItems.map((item, idx) => (
                        <div
                            key={item.id}
                            onClick={() => setActiveImageIndex(idx)}
                            className="group shadow-airbnb hover:shadow-airbnb-hover relative cursor-pointer overflow-hidden rounded-3xl border border-border/80 bg-card transition-all duration-300 hover:-translate-y-1"
                        >
                            <div className="relative aspect-[4/3] overflow-hidden">
                                <img
                                    src={item.src}
                                    alt={item.alt}
                                    className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                    loading="lazy"
                                />
                                <div className="absolute inset-0 flex flex-col justify-between bg-gradient-to-t from-black/80 via-black/20 to-transparent p-6 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                    <div className="self-end rounded-full border border-white/20 bg-black/50 p-2 text-white backdrop-blur-md">
                                        <Maximize2 className="h-4 w-4" />
                                    </div>
                                    <div>
                                        <span className="mb-1 block text-[10px] font-extrabold tracking-widest text-amber-300 uppercase">
                                            {item.category}
                                        </span>
                                        <h3 className="text-sm leading-snug font-extrabold text-white">
                                            {item.title}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Visor de Galería Modal Reutilizable */}
            <VisorGaleriaModal
                estaAbierto={activeImageIndex !== null}
                alCerrar={() => setActiveImageIndex(null)}
                imagenes={imagenesUrls}
                indiceImagenActiva={activeImageIndex ?? 0}
                alSeleccionarImagen={(idx) => setActiveImageIndex(idx)}
                titulo={
                    activeImageIndex !== null
                        ? filteredItems[activeImageIndex]?.title
                        : undefined
                }
            />
        </section>
    );
}
