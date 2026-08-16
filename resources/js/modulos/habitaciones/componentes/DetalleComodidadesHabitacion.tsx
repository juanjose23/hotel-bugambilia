import { Check, Wifi, Car, Coffee, Tv, Wind, Shield } from 'lucide-react';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesDetalleComodidadesHabitacion {
    amenities?: string[];
    included?: string[];
    policies?: {
        checkIn?: string;
        checkOut?: string;
        cancellation?: string;
        smoking?: string;
        pets?: string;
    };
}

const getAmenityIcon = (amenity: string) => {
    const low = amenity.toLowerCase();

    if (low.includes('wifi')) {
        return (
            <Wifi className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
        );
    }

    if (low.includes('tv')) {
        return (
            <Tv className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
        );
    }

    if (low.includes('aire')) {
        return (
            <Wind className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
        );
    }

    if (low.includes('cafe')) {
        return (
            <Coffee className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
        );
    }

    if (low.includes('estacion')) {
        return (
            <Car className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
        );
    }

    if (low.includes('caja')) {
        return (
            <Shield className="size-5 text-bugambilia-600 dark:text-bugambilia-400" />
        );
    }

    return <Check className="size-5 text-emerald-500" />;
};

export const DetalleComodidadesHabitacion = ({
    amenities = [],
    included = [],
    policies,
}: PropiedadesDetalleComodidadesHabitacion) => {
    if (amenities.length === 0 && included.length === 0 && !policies) {
        return null;
    }

    return (
        <div className="border-t border-border/40 bg-background py-12 font-sans">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid max-w-4xl grid-cols-1 gap-12 lg:grid-cols-2">
                    {amenities.length > 0 && (
                        <div>
                            <h3 className="mb-6 text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                Equipamiento de la Habitación
                            </h3>
                            <div className="space-y-4">
                                {amenities.map((amenity, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center gap-3"
                                    >
                                        {getAmenityIcon(amenity)}
                                        <span className="text-sm font-bold text-foreground">
                                            {amenity}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {included.length > 0 && (
                        <div>
                            <h3 className="mb-6 text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                Servicios Incluidos
                            </h3>
                            <div className="space-y-4">
                                {included.map((service, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center gap-3"
                                    >
                                        <div className="flex size-5 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600">
                                            <Check className="size-3.5" />
                                        </div>
                                        <span className="text-sm font-bold text-foreground">
                                            {service}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                {policies && (
                    <Card className="mt-12 max-w-4xl rounded-3xl border-border/80 bg-card p-6">
                        <CardContent className="flex flex-col gap-3 p-0">
                            <h4 className="text-base font-black text-foreground">
                                Políticas & Normas de Estancia
                            </h4>
                            <div className="grid grid-cols-1 gap-4 text-xs sm:grid-cols-3">
                                {policies.checkIn && (
                                    <div>
                                        <span className="block font-extrabold text-muted-foreground uppercase">
                                            Check-In
                                        </span>
                                        <span className="font-bold text-foreground">
                                            {policies.checkIn}
                                        </span>
                                    </div>
                                )}
                                {policies.checkOut && (
                                    <div>
                                        <span className="block font-extrabold text-muted-foreground uppercase">
                                            Check-Out
                                        </span>
                                        <span className="font-bold text-foreground">
                                            {policies.checkOut}
                                        </span>
                                    </div>
                                )}
                                {policies.cancellation && (
                                    <div>
                                        <span className="block font-extrabold text-muted-foreground uppercase">
                                            Cancelación
                                        </span>
                                        <span className="font-bold text-foreground">
                                            {policies.cancellation}
                                        </span>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </div>
    );
};

export default DetalleComodidadesHabitacion;
