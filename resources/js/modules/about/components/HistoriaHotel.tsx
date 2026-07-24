import { usePage } from '@inertiajs/react';
import { Heart, Users, Award, Leaf, Sparkles } from 'lucide-react';
const HistoriaHotel = () => {
    const pageProps = usePage().props;
    const hotelName = pageProps.hotel?.name || 'Hotel Bugambilias';
    const fundado = pageProps.hotel?.fundado || '1989';

    return (
        <section className="border-b border-border/40 bg-background py-16 font-sans lg:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    {/* Text Content */}
                    <div className="space-y-6">
                        <div className="inline-flex items-center gap-2 rounded-full border border-bugambilia-500/20 bg-bugambilia-500/10 px-3.5 py-1 text-xs font-extrabold tracking-widest text-bugambilia-600 uppercase dark:text-bugambilia-400">
                            <Sparkles className="h-3.5 w-3.5" />
                            Nuestra Esencia
                        </div>

                        <h2 className="text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                            Más de 35 años de{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                hospitalidad nicaragüense
                            </span>
                        </h2>

                        <p className="text-base leading-relaxed text-muted-foreground">
                            Fundado en {fundado}, {hotelName} nació con la firme
                            visión de ofrecer un refugio donde cada visitante
                            experimente la calidez humana y la serenidad de
                            Estelí. A lo largo de los años, hemos crecido junto
                            a nuestra comunidad manteniendo estándares de clase
                            mundial.
                        </p>

                        <p className="text-base leading-relaxed text-muted-foreground">
                            Combinamos la riqueza cultural nicaragüense con las
                            comodidades tecnológicas modernas, como fibra óptica
                            de alta velocidad, parqueo monitoreado 24/7 y
                            servicio de concierge personalizado.
                        </p>

                        {/* Key Stats Bar */}
                        <div className="grid grid-cols-2 gap-4 pt-4 sm:grid-cols-4">
                            <div className="shadow-airbnb-subtle rounded-2xl border border-border/80 bg-card p-4 text-center">
                                <Heart className="mx-auto mb-2 h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                <div className="text-2xl font-black text-foreground">
                                    35+
                                </div>
                                <div className="text-[11px] font-semibold text-muted-foreground">
                                    Años de Tradición
                                </div>
                            </div>
                            <div className="shadow-airbnb-subtle rounded-2xl border border-border/80 bg-card p-4 text-center">
                                <Users className="mx-auto mb-2 h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                <div className="text-2xl font-black text-foreground">
                                    50K+
                                </div>
                                <div className="text-[11px] font-semibold text-muted-foreground">
                                    Huéspedes Felices
                                </div>
                            </div>
                            <div className="shadow-airbnb-subtle rounded-2xl border border-border/80 bg-card p-4 text-center">
                                <Award className="mx-auto mb-2 h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                <div className="text-2xl font-black text-foreground">
                                    15+
                                </div>
                                <div className="text-[11px] font-semibold text-muted-foreground">
                                    Reconocimientos
                                </div>
                            </div>
                            <div className="shadow-airbnb-subtle rounded-2xl border border-border/80 bg-card p-4 text-center">
                                <Leaf className="mx-auto mb-2 h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                <div className="text-2xl font-black text-foreground">
                                    100%
                                </div>
                                <div className="text-[11px] font-semibold text-muted-foreground">
                                    Eco Sostenible
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Photo Collage */}
                    <div className="relative">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-4">
                                <div className="shadow-airbnb h-48 overflow-hidden rounded-2xl border border-border/80">
                                    <img
                                        src="/images/pool-front-view.webp"
                                        alt={`Piscina ${hotelName}`}
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                                <div className="shadow-airbnb h-36 overflow-hidden rounded-2xl border border-border/80">
                                    <img
                                        src="/images/service-pool.webp"
                                        alt="Servicios del hotel"
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                            </div>
                            <div className="mt-6 space-y-4">
                                <div className="shadow-airbnb h-36 overflow-hidden rounded-2xl border border-border/80">
                                    <img
                                        src="/images/main-room.webp"
                                        alt="Habitación principal"
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                                <div className="shadow-airbnb h-48 overflow-hidden rounded-2xl border border-border/80">
                                    <img
                                        src="/images/terrace.webp"
                                        alt="Terraza del hotel"
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};
export default HistoriaHotel;
