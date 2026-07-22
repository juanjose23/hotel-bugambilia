import { usePage } from '@inertiajs/react';
import { Star } from 'lucide-react';

export default function AboutHero() {
    const pageProps = usePage().props;
    const hotelName = pageProps.hotel?.name || 'Hotel Bugambilias';
    const fundado = pageProps.hotel?.fundado || '1989';

    return (
        <section className="relative h-[55vh] max-h-[600px] min-h-[440px] overflow-hidden font-sans">
            <img
                src="/images/hero-secondary.jpg"
                alt={`${hotelName} - Nuestra historia en Estelí, Nicaragua`}
                className="absolute inset-0 h-full w-full scale-105 object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-r from-black/95 via-black/75 to-black/40" />

            <div className="absolute inset-0 flex items-center">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="max-w-3xl text-white">
                        <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-500/20 px-3.5 py-1 text-xs font-extrabold tracking-widest text-amber-300 uppercase backdrop-blur-md">
                            <Star className="h-3.5 w-3.5 fill-amber-400" />
                            Desde {fundado} • Estelí, Nicaragua
                        </div>

                        <h1 className="mb-4 text-3xl leading-tight font-black tracking-tight text-white drop-shadow-md sm:text-5xl lg:text-6xl">
                            Nuestra{' '}
                            <span className="font-serif font-normal text-amber-300 italic">
                                Historia & Legado
                            </span>
                        </h1>

                        <p className="max-w-2xl text-sm leading-relaxed font-medium text-white/90 drop-shadow-sm sm:text-base md:text-lg">
                            Más de tres décadas ofreciendo confort boutique,
                            hospitalidad artesanal y privacidad inigualable en
                            el corazón del norte de Nicaragua.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    );
}
