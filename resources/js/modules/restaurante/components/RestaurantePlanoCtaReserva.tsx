import { Calendar } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';

interface RestaurantePlanoCtaReservaProps {
    alAbrirReserva: () => void;
}

export const RestaurantePlanoCtaReserva = ({
    alAbrirReserva,
}: RestaurantePlanoCtaReservaProps) => (
    <div className="mt-2 flex flex-col items-center justify-between gap-4 rounded-3xl border border-primary/20 bg-gradient-to-r from-primary/10 via-background to-primary/5 p-6 shadow-sm sm:flex-row">
        <div className="space-y-1 text-center sm:text-left">
            <h4 className="text-base font-black text-foreground">
                ¿Listo para reservar tu mesa favorita?
            </h4>
            <p className="text-xs text-muted-foreground">
                Garantiza tu espacio con reserva inmediata y asignación
                inteligente.
            </p>
        </div>

        <Button
            type="button"
            onClick={alAbrirReserva}
            className="cursor-pointer rounded-2xl px-6 font-black shadow-md"
        >
            <Calendar className="mr-2 size-4" />
            Reservar Mesa Ahora
        </Button>
    </div>
);

export default RestaurantePlanoCtaReserva;
