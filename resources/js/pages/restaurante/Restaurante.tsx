import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { useHotel } from '@/modulos/compartido/hooks/useHotel';
import AmbientesRestaurante from '@/modulos/restaurante/componentes/AmbientesRestaurante';
import HorariosRestaurante from '@/modulos/restaurante/componentes/HorariosRestaurante';
import MenuRestaurante from '@/modulos/restaurante/componentes/MenuRestaurante';
import PortadaRestaurante from '@/modulos/restaurante/componentes/PortadaRestaurante';
import SeccionReservaRestaurante from '@/modulos/restaurante/componentes/SeccionReservaRestaurante';
import type {
    RestauranteData,
    AmbienteData,
    MesaData,
    MenuItemData,
} from '@/modulos/restaurante/types';
interface Props {
    restaurante?: RestauranteData | null;
    ambientes?: AmbienteData[];
    mesas?: MesaData[];
    menu?: MenuItemData[];
}
const Restaurante = ({ restaurante, ambientes = [], menu = [] }: Props) => {
    const hotel = useHotel();
    const [selectedAmbienteReserva, setSelectedAmbienteReserva] = useState<
        string | undefined
    >();

    if (!restaurante) {
        return (
            <>
                <div className="flex min-h-[70vh] items-center justify-center bg-background py-24 font-sans">
                    <div className="max-w-md rounded-3xl border border-border bg-card p-8 text-center shadow-xl">
                        <p className="mb-2 text-lg font-bold text-foreground">
                            Información No Disponible
                        </p>
                        <p className="text-sm text-muted-foreground">
                            En este momento la información del restaurante no se
                            encuentra configurada en el sistema.
                        </p>
                    </div>
                </div>
            </>
        );
    }

    const handleScrollToSection = (sectionId: string) => {
        const el = document.getElementById(sectionId);

        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
        }
    };
    const handleSelectAmbienteReserva = (ambienteNombre: string) => {
        setSelectedAmbienteReserva(ambienteNombre);
        handleScrollToSection('reserva-section');
    };

    return (
        <>
            <Head>
                <title>Restaurante — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Restaurante del Hotel Bugambilias. Disfruta de la mejor gastronomía nicaragüense e internacional en un ambiente acogedor y exclusivo en Estelí."
                />
            </Head>
            <div className="min-h-screen bg-background font-sans selection:bg-amber-500 selection:text-zinc-950">
                {/* Hero Banner Section */}
                <PortadaRestaurante
                    restaurante={restaurante}
                    onScrollToSection={handleScrollToSection}
                    whatsappNumber={hotel?.whatsapp}
                />

                {/* Ambientes & Espacios Showcase */}
                <AmbientesRestaurante
                    ambientes={ambientes}
                    onSelectAmbienteReserva={handleSelectAmbienteReserva}
                />

                {/* Menú a la Carta Interactivo */}
                <MenuRestaurante menu={menu} />

                {/* Horarios & Tiempos de Comida */}
                <HorariosRestaurante restaurante={restaurante} />

                {/* Sección de Reserva de Mesas & Ambientes */}
                <SeccionReservaRestaurante
                    ambientes={ambientes}
                    selectedAmbienteNombre={selectedAmbienteReserva}
                    whatsappNumber={hotel?.whatsapp}
                />
            </div>
        </>
    );
};
export default Restaurante;
