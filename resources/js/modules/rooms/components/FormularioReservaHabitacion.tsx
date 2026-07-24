import { router } from '@inertiajs/react';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { Star, ChevronDown, ShieldCheck, HelpCircle } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/ui/boton';
import { Calendar } from '@/modules/shared/ui/calendario';
import { Label } from '@/modules/shared/ui/etiqueta';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/modules/shared/ui/ventana-emergente';
interface Room {
    id: number;
    name: string;
    price: number;
    originalPrice: number;
    guests: number;
}
interface FormularioReservaHabitacionProps {
    room: Room;
}
const FormularioReservaHabitacion = ({
    room,
}: FormularioReservaHabitacionProps) => {
    const [nights] = useState(2);
    const [checkInDate, setCheckInDate] = useState<Date>();
    const [checkOutDate, setCheckOutDate] = useState<Date>();
    const [guests, setGuests] = useState(2);
    const subtotal = room.price * nights;
    const taxes = subtotal * 0.16;
    const total = subtotal + taxes;

    return (
        <div className="space-y-4 lg:sticky lg:top-32">
            <div className="shadow-airbnb rounded-3xl border border-gray-100 bg-white p-6 md:p-8 dark:border-gray-800 dark:bg-gray-900">
                <div className="mb-8 flex items-start justify-between">
                    <div>
                        <div className="flex items-baseline gap-1.5">
                            <span className="text-3xl font-black text-black dark:text-white">
                                ${room.price}
                            </span>
                            <span className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                noche
                            </span>
                        </div>
                        {room.originalPrice > room.price && (
                            <p className="mt-1 text-sm font-bold tracking-tighter text-emerald-600 uppercase">
                                Oferta Limitada: -$
                                {room.originalPrice - room.price} menos
                            </p>
                        )}
                    </div>
                    <div className="flex items-center gap-1.5 rounded-2xl border border-gray-100 bg-gray-50 px-3 py-1.5 dark:border-gray-800 dark:bg-gray-900/50">
                        <Star className="h-3.5 w-3.5 fill-bugambilia-600 text-bugambilia-600" />
                        <span className="text-sm font-black">4.92</span>
                    </div>
                </div>

                <div className="mb-6 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700">
                    <div className="grid grid-cols-2 divide-x divide-gray-200 border-b border-gray-200 dark:divide-gray-700 dark:border-gray-700">
                        <div className="p-4">
                            <Label className="mb-1 block text-[10px] font-black tracking-widest text-black uppercase dark:text-white">
                                Llegada
                            </Label>
                            <Popover>
                                <PopoverTrigger asChild>
                                    <button className="transition-airbnb w-full text-left text-[13px] font-medium text-gray-500 hover:text-bugambilia-600">
                                        {checkInDate
                                            ? format(checkInDate, 'dd MMM', {
                                                  locale: es,
                                              })
                                            : 'Añadir fecha'}
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent
                                    className="shadow-airbnb w-auto rounded-3xl border border-gray-100 bg-white p-0 dark:border-gray-800 dark:bg-gray-900"
                                    align="start"
                                >
                                    <Calendar
                                        mode="single"
                                        selected={checkInDate}
                                        onSelect={setCheckInDate}
                                        disabled={(date: Date) =>
                                            date < new Date()
                                        }
                                        locale={es}
                                    />
                                </PopoverContent>
                            </Popover>
                        </div>
                        <div className="p-4">
                            <Label className="mb-1 block text-[10px] font-black tracking-widest text-black uppercase dark:text-white">
                                Salida
                            </Label>
                            <Popover>
                                <PopoverTrigger asChild>
                                    <button className="transition-airbnb w-full text-left text-[13px] font-medium text-gray-500 hover:text-bugambilia-600">
                                        {checkOutDate
                                            ? format(checkOutDate, 'dd MMM', {
                                                  locale: es,
                                              })
                                            : 'Añadir fecha'}
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent
                                    className="shadow-airbnb w-auto rounded-3xl border border-gray-100 bg-white p-0 dark:border-gray-800 dark:bg-gray-900"
                                    align="end"
                                >
                                    <Calendar
                                        mode="single"
                                        selected={checkOutDate}
                                        onSelect={setCheckOutDate}
                                        disabled={(date: Date) =>
                                            date < new Date() ||
                                            (checkInDate
                                                ? date <= checkInDate
                                                : false)
                                        }
                                        locale={es}
                                    />
                                </PopoverContent>
                            </Popover>
                        </div>
                    </div>
                    <div className="relative p-4">
                        <Label className="mb-1 block text-[10px] font-black tracking-widest text-black uppercase dark:text-white">
                            Huéspedes
                        </Label>
                        <Popover>
                            <PopoverTrigger asChild>
                                <button className="transition-airbnb flex w-full items-center justify-between text-[13px] font-medium text-gray-500 hover:text-bugambilia-600">
                                    <span>
                                        {guests}{' '}
                                        {guests > 1 ? 'huéspedes' : 'huésped'}
                                    </span>
                                    <ChevronDown className="h-4 w-4" />
                                </button>
                            </PopoverTrigger>
                            <PopoverContent className="shadow-airbnb w-full min-w-[300px] rounded-3xl border border-gray-100 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h4 className="text-sm font-black text-gray-900 dark:text-white">
                                            Adultos
                                        </h4>
                                        <p className="text-xs text-gray-500">
                                            Edad 13 o más
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-4">
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            className="h-8 w-8 rounded-full"
                                            onClick={() =>
                                                setGuests(
                                                    Math.max(1, guests - 1),
                                                )
                                            }
                                        >
                                            -
                                        </Button>
                                        <span className="w-4 text-center font-bold">
                                            {guests}
                                        </span>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            className="h-8 w-8 rounded-full"
                                            onClick={() =>
                                                setGuests(
                                                    Math.min(
                                                        room.guests,
                                                        guests + 1,
                                                    ),
                                                )
                                            }
                                        >
                                            +
                                        </Button>
                                    </div>
                                </div>
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>

                <Button
                    onClick={() => router.get('/pago')}
                    className="bg-bugambilia-gradient shadow-airbnb transition-airbnb mb-4 h-auto w-full rounded-2xl border-none py-8 text-sm font-black tracking-widest text-white uppercase hover:scale-[1.02] active:scale-[0.98]"
                >
                    Reservar ahora
                </Button>

                <p className="mb-8 text-center text-[10px] font-black tracking-widest text-gray-400 uppercase">
                    No se realizará ningún cargo todavía
                </p>

                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <span className="transition-airbnb cursor-pointer text-sm font-medium text-gray-600 underline decoration-gray-300 underline-offset-4 hover:text-black dark:text-gray-400 dark:decoration-gray-700">
                            ${room.price} x {nights} noches
                        </span>
                        <span className="text-sm font-bold text-black dark:text-white">
                            ${subtotal}
                        </span>
                    </div>
                    <div className="flex items-center justify-between">
                        <span className="transition-airbnb cursor-pointer text-sm font-medium text-gray-600 underline decoration-gray-300 underline-offset-4 hover:text-black dark:text-gray-400 dark:decoration-gray-700">
                            Impuestos (16%)
                        </span>
                        <span className="text-sm font-bold text-black dark:text-white">
                            ${taxes.toFixed(0)}
                        </span>
                    </div>

                    <div className="mt-6 flex items-center justify-between border-t border-gray-100 pt-6 dark:border-gray-800">
                        <span className="text-lg font-black tracking-tight text-black dark:text-white">
                            Total
                        </span>
                        <span className="text-xl font-black tracking-tighter text-bugambilia-600">
                            ${total.toFixed(0)}
                        </span>
                    </div>
                </div>
            </div>

            <div className="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-950">
                <ShieldCheck className="h-8 w-8 text-bugambilia-600" />
                <div>
                    <h4 className="text-[11px] font-black tracking-widest text-black uppercase dark:text-white">
                        Pago Seguro
                    </h4>
                    <p className="text-[10px] font-medium text-gray-500">
                        Transacción protegida por SSL de 256 bits
                    </p>
                </div>
                <HelpCircle className="transition-airbnb ml-auto h-4 w-4 cursor-pointer text-gray-300 hover:text-black" />
            </div>

            <div className="rounded-3xl border border-gray-100 bg-gray-50 p-6 text-center dark:border-gray-800 dark:bg-gray-900/30">
                <p className="mb-3 text-xs font-bold tracking-widest text-gray-500 uppercase dark:text-gray-400">
                    ¿Dudas sobre esta estancia?
                </p>
                <Button
                    variant="outline"
                    className="transition-airbnb h-12 w-full rounded-xl border-gray-200 text-xs font-bold tracking-widest uppercase hover:bg-black hover:text-white dark:border-gray-700 dark:hover:bg-white dark:hover:text-black"
                >
                    Contactar Soporte
                </Button>
            </div>
        </div>
    );
};
export default FormularioReservaHabitacion;
