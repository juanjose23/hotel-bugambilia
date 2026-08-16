import { usePage } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { PortadaHeroGeneral } from '@/modulos/compartido/componentes/PortadaHeroGeneral';

const PortadaAcercaDe = () => {
    const pageProps = usePage().props;
    const fundado = pageProps.hotel?.fundado || '1989';

    return (
        <PortadaHeroGeneral
            imagenFondo="/images/hero-secondary.webp"
            badgeLabel={`Desde ${fundado} • Estelí, Nicaragua`}
            badgeIcon={Star}
            badgeStyle="border-amber-400/40 bg-amber-500/20 text-amber-300"
            titulo="Nuestra"
            tituloEnfasis="Historia & Legado"
            descripcion="Más de tres décadas ofreciendo confort boutique, hospitalidad artesanal y privacidad inigualable en el corazón del norte de Nicaragua."
        />
    );
};

export default PortadaAcercaDe;
