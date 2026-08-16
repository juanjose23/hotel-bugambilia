import { Link } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import type { HabitacionReservable } from '../interfaces/reservaHabitacion';

interface PropiedadesBarraInferiorHabitacion {
    room: HabitacionReservable;
}

export const BarraInferiorHabitacion = ({
    room,
}: PropiedadesBarraInferiorHabitacion) => {
    return (
        <div className="fixed right-0 bottom-0 left-0 z-50 border-t border-border/60 bg-card/90 px-4 py-3 backdrop-blur-xl lg:hidden">
            <div className="mx-auto flex max-w-md items-center justify-between gap-4">
                <div>
                    <div className="flex items-baseline gap-1">
                        <span className="text-xl font-black text-foreground">
                            ${room.precio}
                        </span>
                        <span className="text-xs font-semibold text-muted-foreground">
                            {room.moneda || 'USD'} / noche
                        </span>
                    </div>
                    <div className="mt-0.5 flex items-center gap-1">
                        <Star className="size-3 fill-amber-500 text-amber-500" />
                        <span className="text-[10px] font-extrabold text-foreground uppercase">
                            Garantía Bugambilias
                        </span>
                    </div>
                </div>

                <Button
                    asChild
                    size="lg"
                    className="rounded-2xl bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700"
                >
                    <Link href={`/habitaciones/${room.slug}/reservar`} prefetch>
                        Reservar Ahora
                    </Link>
                </Button>
            </div>
        </div>
    );
};

export default BarraInferiorHabitacion;
