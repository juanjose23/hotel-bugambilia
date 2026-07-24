import { Head } from '@inertiajs/react';
import GaleriaHotel from '@/modules/about/components/GaleriaHotel';
import HistoriaHotel from '@/modules/about/components/HistoriaHotel';
import PortadaAcercaDe from '@/modules/about/components/PortadaAcercaDe';
import ValoresHotel from '@/modules/about/components/ValoresHotel';
const AcercaDe = () => {
    return (
        <>
            <Head>
                <title>Acerca de — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Conoce la historia y valores del Hotel Bugambilias — Hospitalidad nicaragüense desde su fundación en Estelí."
                />
            </Head>
            <PortadaAcercaDe />
            <HistoriaHotel />
            <ValoresHotel />
            <GaleriaHotel />
        </>
    );
};
export default AcercaDe;
