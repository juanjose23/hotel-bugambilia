import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import RestaurantAmbientes from '@/modules/restaurante/components/RestaurantAmbientes';
import RestaurantHero from '@/modules/restaurante/components/RestaurantHero';
import RestaurantHorarios from '@/modules/restaurante/components/RestaurantHorarios';
import RestaurantMenu from '@/modules/restaurante/components/RestaurantMenu';
import RestaurantReservaSection from '@/modules/restaurante/components/RestaurantReservaSection';
import type {
    RestauranteData,
    AmbienteData,
    MesaData,
    MenuItemData,
} from '@/modules/restaurante/types';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

interface Props {
    restaurante?: RestauranteData | null;
    ambientes?: AmbienteData[];
    mesas?: MesaData[];
    menu?: MenuItemData[];
}

export default function Restaurante({
    restaurante,
    ambientes = [],
    menu = [],
}: Props) {
    const { hotel } = usePage().props as {
        hotel?: { whatsapp?: string; telefono?: string };
    };
    const [selectedAmbienteReserva, setSelectedAmbienteReserva] = useState<
        string | undefined
    >();

    if (!restaurante) {
        return (
            <LayoutPublico>
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
            </LayoutPublico>
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
        <LayoutPublico>
            <div className="min-h-screen bg-background font-sans selection:bg-amber-500 selection:text-zinc-950">
                {/* Hero Banner Section */}
                <RestaurantHero
                    restaurante={restaurante}
                    onScrollToSection={handleScrollToSection}
                    whatsappNumber={hotel?.whatsapp}
                />

                {/* Ambientes & Espacios Showcase */}
                <RestaurantAmbientes
                    ambientes={ambientes}
                    onSelectAmbienteReserva={handleSelectAmbienteReserva}
                />

                {/* Menú a la Carta Interactivo */}
                <RestaurantMenu menu={menu} />

                {/* Horarios & Tiempos de Comida */}
                <RestaurantHorarios restaurante={restaurante} />

                {/* Sección de Reserva de Mesas & Ambientes */}
                <RestaurantReservaSection
                    ambientes={ambientes}
                    selectedAmbienteNombre={selectedAmbienteReserva}
                    whatsappNumber={hotel?.whatsapp}
                />
            </div>
        </LayoutPublico>
    );
}
