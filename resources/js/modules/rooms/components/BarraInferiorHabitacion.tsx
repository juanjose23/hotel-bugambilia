import { router } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { Button } from '@/modules/shared/ui/boton';
interface Room {
    id: number;
    name: string;
    price: number;
    originalPrice: number;
    guests: number;
    rating?: number;
    reviews?: number;
}
interface BarraInferiorHabitacionProps {
    room: Room;
}
const BarraInferiorHabitacion = ({ room }: BarraInferiorHabitacionProps) => {
    return (
        <div className="safe-area-bottom fixed right-0 bottom-0 left-0 z-50 border-t border-gray-100 bg-white/80 px-4 pt-4 pb-6 backdrop-blur-xl lg:hidden dark:border-gray-900 dark:bg-gray-950/80">
            <div className="mx-auto flex max-w-md items-center justify-between gap-6">
                <div>
                    <div className="flex items-baseline gap-1.5 overflow-hidden">
                        <span className="text-xl font-black tracking-tight text-black dark:text-white">
                            ${room.price}
                        </span>
                        <span className="text-xs font-medium text-gray-500 underline decoration-gray-300 underline-offset-4">
                            noche
                        </span>
                    </div>
                    <div className="mt-0.5 flex items-center gap-1">
                        <Star className="h-2.5 w-2.5 fill-bugambilia-600 text-bugambilia-600" />
                        <span className="text-[10px] font-black tracking-tighter text-black uppercase dark:text-white">
                            4.92 &bull; 127 reseñas
                        </span>
                    </div>
                </div>

                <Button
                    className="bg-bugambilia-gradient shadow-airbnb transition-airbnb h-auto flex-1 rounded-2xl border-none py-6 text-[11px] font-black tracking-widest text-white uppercase hover:scale-[1.02] active:scale-[0.98]"
                    onClick={() => router.get('/pago')}
                >
                    Reservar ahora
                </Button>
            </div>
        </div>
    );
};
export default BarraInferiorHabitacion;
