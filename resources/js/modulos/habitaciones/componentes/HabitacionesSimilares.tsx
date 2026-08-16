import { Link } from '@inertiajs/react';
import { ArrowRight, BedDouble } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import type { HabitacionGrupo } from '../interfaces/habitacionInterfaces';
import { TarjetaHabitacion } from './TarjetaHabitacion';

interface PropiedadesHabitacionesSimilares {
    currentRoomId?: number;
    habitacionesSimilares?: HabitacionGrupo[];
}

export const HabitacionesSimilares = ({
    currentRoomId,
    habitacionesSimilares = [],
}: PropiedadesHabitacionesSimilares) => {
    const listado = habitacionesSimilares
        .filter((r) => r.id !== currentRoomId)
        .slice(0, 3);

    if (listado.length === 0) {
        return null;
    }

    return (
        <section className="border-t border-border/40 bg-muted/20 py-20 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="mb-12 flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
                    <div>
                        <Badge
                            variant="outline"
                            className="mb-3 border-bugambilia-500/20 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400"
                        >
                            <BedDouble
                                className="mr-1 size-3.5"
                                data-icon="inline-start"
                            />{' '}
                            Continuar Explorando
                        </Badge>
                        <h3 className="text-3xl font-black tracking-tight text-foreground md:text-4xl">
                            Otras Estancias Boutique
                        </h3>
                    </div>

                    <Button
                        asChild
                        variant="link"
                        className="gap-2 p-0 font-extrabold text-bugambilia-600 dark:text-bugambilia-400"
                    >
                        <Link href="/habitaciones" prefetch>
                            Ver catálogo completo
                            <ArrowRight
                                className="size-4"
                                data-icon="inline-end"
                            />
                        </Link>
                    </Button>
                </div>

                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {listado.map((room) => (
                        <TarjetaHabitacion key={room.id} habitacion={room} />
                    ))}
                </div>
            </div>
        </section>
    );
};

export default HabitacionesSimilares;
