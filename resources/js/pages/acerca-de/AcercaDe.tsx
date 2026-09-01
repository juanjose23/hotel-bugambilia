import { Head } from '@inertiajs/react';
import { AcercaCta } from '@/modules/acerca-de/components/AcercaCta';
import { AcercaHero } from '@/modules/acerca-de/components/AcercaHero';
import { AcercaHistoria } from '@/modules/acerca-de/components/AcercaHistoria';
import { AcercaInstalaciones } from '@/modules/acerca-de/components/AcercaInstalaciones';
import { AcercaPilares } from '@/modules/acerca-de/components/AcercaPilares';
import { AcercaStats } from '@/modules/acerca-de/components/AcercaStats';

export const AcercaDe = () => {
    return (
        <>
            <Head>
                <title>Acerca de Nosotros — Hotel Bugambilias Estelí</title>
                <meta
                    name="description"
                    content="Descubre la historia, valores y hospitalidad de Hotel Bugambilias en Estelí, Nicaragua. Un refugio de descanso con jardines coloniales y atención personalizada."
                />
            </Head>

            <div className="flex flex-col">
                <AcercaHero />
                <AcercaHistoria />
                <AcercaPilares />
                <AcercaStats />
                <AcercaInstalaciones />
                <AcercaCta />
            </div>
        </>
    );
};

export default AcercaDe;
