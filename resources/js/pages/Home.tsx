import AboutPreview from '@/modules/acerca-de/components/AboutPreview';
import FeaturedRooms from '@/modules/habitaciones/components/FeaturedRooms';
import HeroSection from '@/modules/home/components/HeroSection';
import PromotionsSection from '@/modules/home/components/PromotionsSection';
import TestimonialsSection from '@/modules/home/components/TestimonialsSection';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

interface HomeProps {
    hotelInfo?: {
        nombre?: string;
        telefono?: string;
        email?: string;
        direccion?: string;
    };
    habitaciones?: Array<{
        id: number;
        codigo: string;
        numero: number;
        slug: string;
        nombre: string;
        descripcion: string;
        categoria: string;
        precio: number;
        moneda: string;
        capacidad: number;
        camas: string;
        imagen: string;
    }>;
    servicios?: Array<{
        id: number;
        codigo: string;
        nombre: string;
        descripcion: string;
        categoria: string;
        precio?: number | null;
        moneda: string;
        imagen: string;
    }>;
    promociones?: Array<{
        id: number;
        codigo: string;
        nombre: string;
        descripcion: string;
        badge: string;
        precio_paquete?: number | null;
        precio_final?: number | null;
        descuento_porcentaje?: number | null;
        descuento_monto?: number | null;
        moneda: string;
        imagen?: string | null;
        itemsIncluidos?: string[];
    }>;
    categoriasHabitacion?: string[];
}

export default function Home({
    hotelInfo,
    habitaciones = [],
    promociones = [],
    categoriasHabitacion = [],
}: HomeProps) {
    return (
        <LayoutPublico>
            <HeroSection hotelInfo={hotelInfo} />
            <FeaturedRooms
                rooms={habitaciones}
                categories={categoriasHabitacion}
            />
            <PromotionsSection promociones={promociones} />
            <AboutPreview />
            <TestimonialsSection />
        </LayoutPublico>
    );
}
