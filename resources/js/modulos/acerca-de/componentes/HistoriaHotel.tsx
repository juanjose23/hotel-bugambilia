import { usePage } from '@inertiajs/react';
import { Heart, Users, Award, Leaf, BadgeCheck } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { TarjetaEstadisticaItem } from './secciones/TarjetaEstadisticaItem';

export default function HistoriaHotel() {
    const pageProps = usePage().props;
    const hotelName =
        (pageProps.hotel as { name?: string })?.name || 'Hotel Bugambilias';
    const fundado = String(
        (pageProps.hotel as { fundado?: number | string })?.fundado ?? '1989',
    );

    return (
        <section className="border-b border-border/40 bg-background py-16 font-sans lg:py-24">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    {/* Contenido de Texto */}
                    <div className="flex flex-col gap-6">
                        <div>
                            <Badge
                                variant="outline"
                                className="border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                            >
                                <BadgeCheck
                                    className="mr-1 size-3.5"
                                    data-icon="inline-start"
                                />{' '}
                                Nuestra Esencia
                            </Badge>
                        </div>

                        <h2 className="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                            Más de 35 años de{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                hospitalidad nicaragüense
                            </span>
                        </h2>

                        <p className="text-base leading-relaxed text-muted-foreground">
                            Fundado en {fundado}, {hotelName} nació con la firme
                            visión de ofrecer un refugio donde cada visitante
                            experimente la calidez humana y la serenidad de
                            Estelí.
                        </p>

                        <p className="text-base leading-relaxed text-muted-foreground">
                            Combinamos la riqueza cultural nicaragüense con las
                            comodidades tecnológicas modernas.
                        </p>

                        {/* Barra de Estadísticas Clave */}
                        <div className="grid grid-cols-2 gap-4 pt-4 sm:grid-cols-4">
                            <TarjetaEstadisticaItem
                                Icono={Heart}
                                valor="35+"
                                etiqueta="Años Tradición"
                            />
                            <TarjetaEstadisticaItem
                                Icono={Users}
                                valor="50K+"
                                etiqueta="Huéspedes Felices"
                            />
                            <TarjetaEstadisticaItem
                                Icono={Award}
                                valor="15+"
                                etiqueta="Reconocimientos"
                            />
                            <TarjetaEstadisticaItem
                                Icono={Leaf}
                                valor="100%"
                                etiqueta="Eco Sostenible"
                            />
                        </div>
                    </div>

                    {/* Collage de Fotos */}
                    <div className="relative">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="flex flex-col gap-4">
                                <div className="h-48 overflow-hidden rounded-2xl border border-border/80 shadow-md">
                                    <img
                                        src="/images/pool-front-view.webp"
                                        alt={`Piscina ${hotelName}`}
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                                <div className="h-36 overflow-hidden rounded-2xl border border-border/80 shadow-md">
                                    <img
                                        src="/images/service-pool.webp"
                                        alt="Servicios del hotel"
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                            </div>
                            <div className="flex flex-col gap-4 pt-8">
                                <div className="h-36 overflow-hidden rounded-2xl border border-border/80 shadow-md">
                                    <img
                                        src="/images/main-room.webp"
                                        alt="Habitación principal"
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                                <div className="h-48 overflow-hidden rounded-2xl border border-border/80 shadow-md">
                                    <img
                                        src="/images/service-kitchen.webp"
                                        alt="Restaurante gourmet"
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
}
