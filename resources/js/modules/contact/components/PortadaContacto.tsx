import { usePage } from '@inertiajs/react';
import { ArrowRight, Sparkles } from 'lucide-react';
import { PortadaHeroGeneral } from '@/modules/shared/components/PortadaHeroGeneral';

const PortadaContacto = () => {
    const { hotel } = usePage().props;
    const telefono = hotel?.telefono || '+505 8713 6805';

    return (
        <PortadaHeroGeneral
            imagenFondo="/images/terrace.webp"
            badgeLabel="Atención al Cliente 24/7"
            badgeIcon={Sparkles}
            badgeStyle="border-amber-400/40 bg-amber-500/20 text-amber-300"
            titulo="Hablemos de su"
            tituloEnfasis="próxima estancia"
            descripcion="Estamos disponibles para resolver sus inquietudes, gestionar reservas grupales y planificar su visita en Estelí."
            acciones={
                <>
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
                </>
            }
        />
    );
};

export default PortadaContacto;
