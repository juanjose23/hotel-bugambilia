import { router } from '@inertiajs/react';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { Star, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Calendar } from '@/modulos/compartido/ui/calendario';
import { Label } from '@/modulos/compartido/ui/etiqueta';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/modulos/compartido/ui/ventana-emergente';
import type { HabitacionReservable } from '../interfaces/reservaHabitacion';

interface PropiedadesFormularioReservaHabitacion {
    room: HabitacionReservable;
}

export const FormularioReservaHabitacion = ({
    room,
}: PropiedadesFormularioReservaHabitacion) => {
    const [checkInDate, setCheckInDate] = useState<Date>();
    const [checkOutDate, setCheckOutDate] = useState<Date>();
    const [adultos] = useState(room.adultos || 2);
    const [ninos] = useState(room.ninos || 0);

    const handleIniciarReserva = () => {
        router.get(`/habitaciones/${room.slug}/reservar`, {
            check_in: checkInDate
                ? format(checkInDate, 'yyyy-MM-dd')
                : undefined,
            check_out: checkOutDate
                ? format(checkOutDate, 'yyyy-MM-dd')
                : undefined,
            adultos,
            ninos,
        });
    };

    return (
        <Card className="rounded-3xl border-border/80 bg-card p-6 font-sans shadow-xl lg:sticky lg:top-32">
            <CardContent className="flex flex-col gap-6 p-0">
                <div className="flex items-start justify-between border-b border-border/50 pb-4">
                    <div>
                        <div className="flex items-baseline gap-1">
                            <span className="text-3xl font-black text-foreground">
                                ${room.precio}
                            </span>
                            <span className="text-xs font-semibold text-muted-foreground">
                                {room.moneda || 'USD'} / noche
                            </span>
                        </div>
                        <span className="block text-[11px] font-medium text-muted-foreground">
                            Impuestos e IVA incluidos
                        </span>
                    </div>

                    <Badge
                        variant="outline"
                        className="flex items-center gap-1 border-amber-400/40 bg-amber-400/10 font-extrabold text-amber-500"
                    >
                        <Star className="size-3.5 fill-amber-500" />
                        <span>5.0</span>
                    </Badge>
                </div>

                <div className="overflow-hidden rounded-2xl border border-border bg-background">
                    <div className="grid grid-cols-2 divide-x divide-border border-b border-border">
                        <div className="p-3">
                            <Label className="mb-1 block text-[10px] font-extrabold tracking-wider text-muted-foreground uppercase">
                                Llegada
                            </Label>
                            <Popover>
                                <PopoverTrigger asChild>
                                    <button className="w-full text-left text-xs font-bold text-foreground hover:text-bugambilia-600">
                                        {checkInDate
                                            ? format(
                                                  checkInDate,
                                                  'dd MMM yyyy',
                                                  { locale: es },
                                              )
                                            : 'Seleccionar'}
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent
                                    align="start"
                                    className="w-auto rounded-3xl border-border p-0"
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

                        <div className="p-3">
                            <Label className="mb-1 block text-[10px] font-extrabold tracking-wider text-muted-foreground uppercase">
                                Salida
                            </Label>
                            <Popover>
                                <PopoverTrigger asChild>
                                    <button className="w-full text-left text-xs font-bold text-foreground hover:text-bugambilia-600">
                                        {checkOutDate
                                            ? format(
                                                  checkOutDate,
                                                  'dd MMM yyyy',
                                                  { locale: es },
                                              )
                                            : 'Seleccionar'}
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent
                                    align="start"
                                    className="w-auto rounded-3xl border-border p-0"
                                >
                                    <Calendar
                                        mode="single"
                                        selected={checkOutDate}
                                        onSelect={setCheckOutDate}
                                        disabled={(date: Date) =>
                                            checkInDate
                                                ? date <= checkInDate
                                                : date < new Date()
                                        }
                                        locale={es}
                                    />
                                </PopoverContent>
                            </Popover>
                        </div>
                    </div>

                    <div className="p-3">
                        <Label className="mb-1 block text-[10px] font-extrabold tracking-wider text-muted-foreground uppercase">
                            Huéspedes
                        </Label>
                        <div className="flex items-center justify-between text-xs font-bold text-foreground">
                            <span>
                                {adultos} Adultos{' '}
                                {ninos > 0 ? `• ${ninos} Niños` : ''}
                            </span>
                            <span className="text-[10px] font-medium text-muted-foreground">
                                Máx. {room.capacidad || 4}
                            </span>
                        </div>
                    </div>
                </div>

                <Button
                    size="lg"
                    onClick={handleIniciarReserva}
                    className="w-full rounded-2xl bg-bugambilia-600 py-6 font-extrabold text-white hover:bg-bugambilia-700"
                >
                    Comprobar Disponibilidad & Reservar
                </Button>

                <div className="flex items-center justify-center gap-2 pt-2 text-xs text-muted-foreground">
                    <ShieldCheck className="size-4 text-emerald-500" />
                    <span>
                        Sin cargos sorpresa • Cancelación según política
                    </span>
                </div>
            </CardContent>
        </Card>
    );
};

export default FormularioReservaHabitacion;
