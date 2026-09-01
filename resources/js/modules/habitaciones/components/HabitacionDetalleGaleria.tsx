import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselPrevious,
    CarouselNext,
} from '@/modules/shared/components/ui/carousel';

interface HabitacionDetalleGaleriaProps {
    imagenes: string[];
    nombreHabitacion: string;
}

export const HabitacionDetalleGaleria = ({
    imagenes,
    nombreHabitacion,
}: HabitacionDetalleGaleriaProps) => {
    const listaImagenes =
        imagenes.length > 0 ? imagenes : ['/images/main-room.webp'];

    return (
        <section
            aria-label={`Galería de fotos de ${nombreHabitacion}`}
            className="overflow-hidden rounded-3xl border border-border/80 bg-card shadow-lg"
        >
            {/* Carrusel shadcn de Imágenes */}
            <Carousel className="w-full">
                <CarouselContent>
                    {listaImagenes.map((img, idx) => (
                        <CarouselItem key={idx} className="basis-full">
                            <div className="relative aspect-16/10 w-full overflow-hidden bg-muted sm:aspect-21/9">
                                <img
                                    src={img}
                                    alt={`${nombreHabitacion} — Fotografía ${idx + 1}`}
                                    className="h-full w-full object-cover"
                                    loading={idx === 0 ? 'eager' : 'lazy'}
                                />
                                <div className="absolute right-4 bottom-4 rounded-full border border-white/20 bg-black/60 px-3.5 py-1 text-xs font-black text-white backdrop-blur-md">
                                    {idx + 1} / {listaImagenes.length}
                                </div>
                            </div>
                        </CarouselItem>
                    ))}
                </CarouselContent>

                {listaImagenes.length > 1 && (
                    <>
                        <CarouselPrevious className="left-4 border-border bg-background/80 shadow-md backdrop-blur-md hover:bg-background" />
                        <CarouselNext className="right-4 border-border bg-background/80 shadow-md backdrop-blur-md hover:bg-background" />
                    </>
                )}
            </Carousel>
        </section>
    );
};

export default HabitacionDetalleGaleria;
