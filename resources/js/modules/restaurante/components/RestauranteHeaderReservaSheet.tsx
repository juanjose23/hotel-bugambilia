import { UtensilsCrossed } from 'lucide-react';
import {
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/modules/shared/components/ui/sheet';

interface RestauranteHeaderReservaSheetProps {
    nombreRestaurante: string;
}

export const RestauranteHeaderReservaSheet = ({
    nombreRestaurante,
}: RestauranteHeaderReservaSheetProps) => (
    <SheetHeader className="border-b border-border/80 pb-4">
        <div className="flex items-center gap-3">
            <div className="flex size-10 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary shadow-xs dark:text-rose-400">
                <UtensilsCrossed className="size-5" />
            </div>
            <div>
                <SheetTitle className="text-base font-black tracking-tight text-foreground">
                    Reservar Mesa en {nombreRestaurante}
                </SheetTitle>
                <SheetDescription className="mt-0.5 text-xs text-muted-foreground">
                    Reserva instantánea por turno horario con asignación
                    inteligente de mesas.
                </SheetDescription>
            </div>
        </div>
    </SheetHeader>
);

export default RestauranteHeaderReservaSheet;
