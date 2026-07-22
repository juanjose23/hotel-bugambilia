import { usePage } from '@inertiajs/react';
import { ArrowRight, Sparkles } from 'lucide-react';

export default function ContactHero() {
    const { hotel } = usePage().props;
    const name = hotel?.name || 'Hotel Bugambilias';
    const direccion = hotel?.direccion_corta || 'Estelí, Nicaragua';
    const telefono = hotel?.telefono || '+505 8713 6805';

    return (
        <section className="relative h-[55vh] max-h-[600px] min-h-[440px] overflow-hidden font-sans">
            <img
                src="/images/terrace.jpg"
                alt={`Contacto ${name} - ${direccion}`}
                className="absolute inset-0 h-full w-full scale-105 object-cover"
            />

            <div className="absolute inset-0 bg-gradient-to-r from-black/95 via-black/75 to-black/40" />

            <div className="absolute inset-0 flex items-center">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="max-w-3xl text-white">
                        <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-500/20 px-3.5 py-1 text-xs font-extrabold tracking-widest text-amber-300 uppercase backdrop-blur-md">
                            <Sparkles className="h-3.5 w-3.5" />
                            Atención Concierge 24/7
                        </div>

                        <h1 className="mb-4 text-3xl leading-tight font-black tracking-tight text-white drop-shadow-md sm:text-5xl lg:text-6xl">
                            Hablemos de su{' '}
                            <span className="font-serif font-normal text-amber-300 italic">
                                próxima estancia
                            </span>
                        </h1>

                        <p className="mb-8 max-w-xl text-sm leading-relaxed font-medium text-white/90 drop-shadow-sm sm:text-base">
                            Estamos disponibles para resolver sus inquietudes,
                            gestionar reservas grupales y planificar su visita
                            en Estelí.
                        </p>

                        <div className="flex flex-wrap gap-3">
                            <a
                                href={`tel:${telefono.replace(/[^0-9+]/g, '')}`}
                                className="shadow-airbnb inline-flex items-center gap-2 rounded-full bg-bugambilia-600 px-7 py-3 text-xs font-extrabold tracking-wider text-white uppercase transition-all duration-300 hover:scale-105 hover:bg-bugambilia-700"
                            >
                                <span>Llamar Directamente</span>
                                <ArrowRight className="h-4 w-4" />
                            </a>
                            <a
                                href={`https://wa.me/${telefono.replace(/[^0-9]/g, '')}`}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-7 py-3 text-xs font-extrabold tracking-wider text-white uppercase backdrop-blur-md transition-all duration-300 hover:bg-white/20"
                            >
                                <span>WhatsApp Concierge</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
