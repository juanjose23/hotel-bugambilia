import { Waves, UtensilsCrossed, Car, Wifi } from 'lucide-react';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import type { HabitacionGrupo } from '../interfaces/habitacionInterfaces';
import { TarjetaHabitacion } from './TarjetaHabitacion';

interface PropiedadesCuadriculaHabitaciones {
    habitaciones?: HabitacionGrupo[];
}

export const CuadriculaHabitaciones = ({
    habitaciones = [],
}: PropiedadesCuadriculaHabitaciones) => {
    if (!habitaciones || habitaciones.length === 0) {
        return null;
    }

    return (
        <section className="bg-background py-20 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-12 max-w-xl">
                    <Badge
                        variant="outline"
                        className="mb-3 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        Catálogo Seleccionado
                    </Badge>
                    <h2 className="text-3xl font-black tracking-tight text-foreground sm:text-4xl">
                        Nuestras Habitaciones
                    </h2>
                    <p className="mt-2 text-sm font-medium text-muted-foreground">
                        Seleccionadas cuidadosamente para su máximo confort.
                    </p>
                </div>

                <div className="mb-20 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {habitaciones.map((room) => (
                        <TarjetaHabitacion key={room.id} habitacion={room} />
                    ))}
                </div>

                {/* Bloque de Comodidades Destacadas */}
                <div className="grid gap-6 border-t border-border/40 pt-16 sm:grid-cols-2 lg:grid-cols-4">
                    {[
                        {
                            icon: Waves,
                            title: 'Piscina Relax',
                            desc: 'Climatizada y con vistas al jardín',
                        },
                        {
                            icon: UtensilsCrossed,
                            title: 'Gastronomía',
                            desc: 'Platos locales con toque gourmet',
                        },
                        {
                            icon: Car,
                            title: 'Seguridad & Parqueo',
                            desc: 'Vigilancia 24/7 y privado gratis',
                        },
                        {
                            icon: Wifi,
                            title: 'Alta Velocidad',
                            desc: 'Fibra óptica en cada habitación',
                        },
                    ].map((item, index) => {
                        const Icon = item.icon;

                        return (
                            <Card
                                key={index}
                                className="rounded-2xl border-border/60 bg-card p-5"
                            >
                                <CardContent className="flex items-center gap-4 p-0">
                                    <div className="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                                        <Icon className="size-6" />
                                    </div>
                                    <div>
                                        <h4 className="text-sm font-extrabold text-foreground">
                                            {item.title}
                                        </h4>
                                        <p className="text-xs text-muted-foreground">
                                            {item.desc}
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            </div>
        </section>
    );
};

export default CuadriculaHabitaciones;
