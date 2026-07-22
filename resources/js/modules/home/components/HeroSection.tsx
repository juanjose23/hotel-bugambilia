import { Link, usePage } from '@inertiajs/react';
import {
    Wifi,
    Car,
    UtensilsCrossed,
    Search,
    Calendar,
    Users,
    ShieldCheck,
} from 'lucide-react';

interface HeroSectionProps {
    hotelInfo?: {
        nombre?: string;
        telefono?: string;
        email?: string;
        direccion?: string;
    };
}

export default function HeroSection({ hotelInfo }: HeroSectionProps) {
    const pageProps = usePage().props;
    const hotelName =
        hotelInfo?.nombre || pageProps.hotel?.name || 'Hotel Bugambilias';

    return (
        <section className="relative font-sans">
            {/* Main Hero Background & Overlay */}
            <div className="relative h-[88vh] max-h-[900px] min-h-[640px] overflow-hidden">
                <img
                    src="/images/hero-main.jpg"
                    alt={`${hotelName} - 5 Estrellas en Estelí, Nicaragua`}
                    className="h-full w-full scale-105 animate-slow-zoom object-cover"
                    fetchPriority="high"
                />

                {/* Gradient overlays for readability */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-black/60" />

                <div className="absolute inset-0 flex flex-col items-center justify-center px-4 pt-10 text-center">
                    <div className="animate-in fade-in slide-in-from-bottom-8 mb-6 max-w-4xl duration-700">
                        <h1 className="mb-6 text-4xl leading-[1.02] font-black tracking-tight text-white drop-shadow-lg sm:text-6xl md:text-7xl lg:text-8xl">
                            <span className="block">Donde Estelí</span>
                            <span className="my-1 block font-serif font-normal text-amber-300 italic drop-shadow-md">
                                Florece con Elegancia
                            </span>
                        </h1>

                        <p className="mx-auto max-w-2xl text-base leading-relaxed font-medium text-white/90 drop-shadow-md sm:text-lg md:text-xl">
                            Hospitalidad exclusiva de 5 estrellas con el encanto
                            artesanal y la calidez auténtica de Nicaragua.
                        </p>
                    </div>

                    {/* Airbnb Floating Search Bar Pill Widget */}
                    <div className="mx-auto mt-2 w-full max-w-4xl px-2 sm:px-4">
                        <div className="hover:shadow-airbnb-hover flex flex-col items-center rounded-3xl border border-white/20 bg-card/95 p-2.5 shadow-2xl backdrop-blur-2xl transition-all duration-300 md:flex-row md:rounded-full dark:border-gray-800">
                            {/* Check-in / Check-out */}
                            <div className="flex w-full cursor-pointer items-center gap-3.5 rounded-full px-6 py-3 text-left transition-all hover:bg-muted/60 md:w-1/3">
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-bugambilia-500/10">
                                    <Calendar className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                </div>
                                <div>
                                    <span className="mb-0.5 block text-[10px] font-extrabold tracking-widest text-muted-foreground uppercase">
                                        Llegada / Salida
                                    </span>
                                    <span className="block text-xs font-bold text-foreground">
                                        Seleccionar Fechas
                                    </span>
                                </div>
                            </div>

                            <div className="hidden h-10 w-px bg-border/80 md:block" />

                            {/* Guests */}
                            <div className="flex w-full cursor-pointer items-center gap-3.5 rounded-full px-6 py-3 text-left transition-all hover:bg-muted/60 md:w-1/3">
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-bugambilia-500/10">
                                    <Users className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                </div>
                                <div>
                                    <span className="mb-0.5 block text-[10px] font-extrabold tracking-widest text-muted-foreground uppercase">
                                        Huéspedes
                                    </span>
                                    <span className="block text-xs font-bold text-foreground">
                                        2 Huéspedes (1 Hab)
                                    </span>
                                </div>
                            </div>

                            <div className="hidden h-10 w-px bg-border/80 md:block" />

                            {/* Search CTA */}
                            <div className="flex w-full items-center justify-end p-1.5 md:w-1/3">
                                <Link
                                    href="/habitaciones"
                                    className="shadow-airbnb flex w-full items-center justify-center gap-2.5 rounded-full bg-bugambilia-600 px-6 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase transition-all duration-300 hover:scale-105 hover:bg-bugambilia-700"
                                >
                                    <Search className="h-4 w-4 stroke-[2.5]" />
                                    <span>Buscar Reserva</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Luxury Amenities Bar */}
            <div className="border-b border-border bg-card py-8 shadow-sm">
                <div className="container mx-auto px-4 sm:px-6">
                    <div className="grid grid-cols-2 gap-6 md:grid-cols-4 md:gap-8">
                        <div className="group flex items-center justify-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 transition-transform group-hover:scale-110">
                                <Wifi className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div className="text-left">
                                <span className="block text-xs font-black text-foreground">
                                    Fibra Óptica High-Speed
                                </span>
                                <span className="text-[10px] font-semibold text-muted-foreground">
                                    Internet de alta velocidad
                                </span>
                            </div>
                        </div>

                        <div className="group flex items-center justify-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 transition-transform group-hover:scale-110">
                                <UtensilsCrossed className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div className="text-left">
                                <span className="block text-xs font-black text-foreground">
                                    Desayuno Gourmet
                                </span>
                                <span className="text-[10px] font-semibold text-muted-foreground">
                                    Típico e internacional
                                </span>
                            </div>
                        </div>

                        <div className="group flex items-center justify-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 transition-transform group-hover:scale-110">
                                <Car className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div className="text-left">
                                <span className="block text-xs font-black text-foreground">
                                    Parqueo Privado 24/7
                                </span>
                                <span className="text-[10px] font-semibold text-muted-foreground">
                                    Monitoreo y seguridad
                                </span>
                            </div>
                        </div>

                        <div className="group flex items-center justify-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 transition-transform group-hover:scale-110">
                                <ShieldCheck className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div className="text-left">
                                <span className="block text-xs font-black text-foreground">
                                    Atención Personalizada
                                </span>
                                <span className="text-[10px] font-semibold text-muted-foreground">
                                    Concierge y servicio 24h
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
