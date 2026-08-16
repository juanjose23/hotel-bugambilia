import { usePage } from '@inertiajs/react';
import { Shield, Key, Navigation2, Star } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import type { HabitacionReservable } from '../interfaces/reservaHabitacion';

interface PropiedadesInformacionDetalleHabitacion {
    room: HabitacionReservable;
}

export const InformacionDetalleHabitacion = ({
    room,
}: PropiedadesInformacionDetalleHabitacion) => {
    const { hotel } = usePage().props as {
        hotel?: { name?: string; fundado?: number };
    };
    const nombreHotel = hotel?.name || 'Hotel Bugambilias';

    return (
        <div className="bg-background py-10 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="max-w-4xl">
                    <div className="mb-10 flex items-center justify-between border-b border-border/50 pb-10">
                        <div className="flex items-center gap-5">
                            <div className="group relative">
                                <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-bugambilia-600 to-bugambilia-400 shadow-lg">
                                    <span className="text-xl font-black text-white">
                                        {nombreHotel
                                            .split(' ')
                                            .map((w) => w[0])
                                            .join('')}
                                    </span>
                                </div>
                                <div className="absolute -right-1 -bottom-1 rounded-full bg-background p-1 shadow-sm">
                                    <Shield className="size-4 text-bugambilia-600 dark:text-bugambilia-400" />
                                </div>
                            </div>
                            <div>
                                <h3 className="text-xl font-black text-foreground">{`Anfitrión: ${nombreHotel}`}</h3>
                                <p className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <span>Atención Boutique 24/7</span>
                                    <span>•</span>
                                    <span>Estelí, Nicaragua</span>
                                </p>
                            </div>
                        </div>

                        <div className="hidden flex-col items-end sm:flex">
                            <div className="mb-1 flex items-center gap-1">
                                <Star className="size-4 fill-amber-500 text-amber-500" />
                                <span className="text-sm font-black text-foreground">
                                    5.0
                                </span>
                            </div>
                            <Badge
                                variant="outline"
                                className="text-[10px] font-extrabold uppercase"
                            >
                                Garantía de Servicio
                            </Badge>
                        </div>
                    </div>

                    <div className="mb-12 space-y-10">
                        <div className="flex items-start gap-6">
                            <div className="mt-1 shrink-0">
                                <Navigation2 className="size-8 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div>
                                <h4 className="mb-1 text-lg font-black text-foreground">
                                    Ubicación Estratégica
                                </h4>
                                <p className="text-sm leading-relaxed text-muted-foreground">
                                    Situada en una zona preferencial del hotel,
                                    ofrece el equilibrio perfecto entre
                                    accesibilidad y paz absoluta.
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start gap-6">
                            <div className="mt-1 shrink-0">
                                <Key className="size-8 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div>
                                <h4 className="mb-1 text-lg font-black text-foreground">
                                    Check-In Rápido & Digital
                                </h4>
                                <p className="text-sm leading-relaxed text-muted-foreground">
                                    Disfrute de check-in exprés y asistencia
                                    constante durante su estancia.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="border-t border-border/50 pt-8">
                        <h4 className="mb-4 text-xl font-black text-foreground">
                            Acerca de esta Habitación
                        </h4>
                        <p className="text-sm leading-relaxed text-muted-foreground">
                            {room.descripcion}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default InformacionDetalleHabitacion;
