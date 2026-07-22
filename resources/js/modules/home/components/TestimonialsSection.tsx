import { usePage, Link } from '@inertiajs/react';
import { Star, Quote, CheckCircle2, Sparkles, ArrowRight } from 'lucide-react';

export default function TestimonialsSection() {
    const pageProps = usePage().props;
    const name = pageProps.hotel?.name || 'Hotel Bugambilias';

    const testimonials = [
        {
            name: 'María González',
            location: 'Managua, Nicaragua',
            rating: 5,
            date: 'Estancia en Mayo 2026',
            comment: `Una experiencia inolvidable en Estelí. El ${name} superó todas mis expectativas. La atención del personal es excepcional, el desayuno exquisito y las habitaciones impecables.`,
            avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80',
        },
        {
            name: 'Carlos Mendoza',
            location: 'San José, Costa Rica',
            rating: 5,
            date: 'Estancia en Junio 2026',
            comment:
                'El lugar ideal para viajes de negocios y turismo en el norte de Nicaragua. Excelente velocidad de internet por fibra óptica, parqueo privado 24/7 y una piscina reconfortante.',
            avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80',
        },
        {
            name: 'Ana Rodríguez',
            location: 'Tegucigalpa, Honduras',
            rating: 5,
            date: 'Estancia en Julio 2026',
            comment:
                'La verdadera hospitalidad nicaragüense. El diseño boutique con plantas y flores de bugambilia le da una atmósfera única. Sin duda volveremos en nuestras próximas vacaciones.',
            avatar: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=200&q=80',
        },
    ];

    return (
        <section className="overflow-hidden border-b border-border/40 bg-card py-24 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-16 max-w-3xl text-center">
                    <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3.5 py-1 text-xs font-extrabold tracking-widest text-amber-600 uppercase dark:text-amber-400">
                        <Sparkles className="h-3.5 w-3.5" />
                        Opiniones de Nuestros Huéspedes
                    </div>
                    <h2 className="mb-4 text-3xl leading-tight font-black tracking-tight text-foreground sm:text-4xl md:text-5xl">
                        Reseñas{' '}
                        <span className="font-serif font-normal text-amber-500 italic">
                            5 Estrellas
                        </span>
                    </h2>
                    <p className="text-base font-medium text-muted-foreground sm:text-lg">
                        Descubra por qué viajeros nacionales e internacionales
                        nos valoran como su hotel preferido en Estelí.
                    </p>
                </div>

                {/* Testimonial Cards (Airbnb Style) */}
                <div className="mb-16 grid gap-8 md:grid-cols-3">
                    {testimonials.map((item, index) => (
                        <div
                            key={index}
                            className="shadow-airbnb hover:shadow-airbnb-hover flex flex-col justify-between rounded-3xl border border-border/80 bg-background p-8 transition-all duration-300 hover:-translate-y-1"
                        >
                            <div>
                                {/* Header Rating & Verification */}
                                <div className="mb-4 flex items-center justify-between">
                                    <div className="flex gap-1 text-amber-400">
                                        {[...Array(item.rating)].map((_, i) => (
                                            <Star
                                                key={i}
                                                className="h-4 w-4 fill-amber-400"
                                            />
                                        ))}
                                    </div>
                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                        <CheckCircle2 className="h-3 w-3" />{' '}
                                        Verificado
                                    </span>
                                </div>

                                <Quote className="mb-3 h-8 w-8 text-bugambilia-500/20" />

                                <p className="mb-6 text-xs leading-relaxed font-medium text-foreground/90 italic sm:text-sm">
                                    "{item.comment}"
                                </p>
                            </div>

                            {/* Guest Profile Footer */}
                            <div className="flex items-center gap-3.5 border-t border-border/40 pt-4">
                                <img
                                    src={item.avatar}
                                    alt={item.name}
                                    className="h-11 w-11 rounded-full border border-border object-cover"
                                />
                                <div>
                                    <h3 className="text-xs font-extrabold text-foreground">
                                        {item.name}
                                    </h3>
                                    <p className="text-[11px] text-muted-foreground">
                                        {item.location}
                                    </p>
                                    <p className="text-[10px] text-muted-foreground/70">
                                        {item.date}
                                    </p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Bottom Booking Callout */}
                <div className="mx-auto max-w-4xl rounded-3xl border border-bugambilia-800/50 bg-gradient-to-r from-bugambilia-900 to-gray-950 p-8 text-center text-white shadow-2xl md:p-12">
                    <h3 className="mb-3 text-2xl font-black sm:text-3xl">
                        ¿Listo para disfrutar de una estancia inigualable?
                    </h3>
                    <p className="mx-auto mb-8 max-w-xl text-sm text-white/80">
                        Reserve directamente en nuestra plataforma para obtener
                        las mejores tarifas y atención personalizada.
                    </p>
                    <div className="flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <Link
                            href="/habitaciones"
                            className="inline-flex items-center gap-2 rounded-full bg-bugambilia-600 px-8 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase shadow-lg transition-all duration-300 hover:scale-105 hover:bg-bugambilia-500"
                        >
                            <span>Ver Disponibilidad</span>
                            <ArrowRight className="h-4 w-4" />
                        </Link>
                        <Link
                            href="/contacto"
                            className="rounded-full border border-white/20 bg-white/10 px-8 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase transition-colors hover:bg-white/20"
                        >
                            Contactar Concierge
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    );
}
