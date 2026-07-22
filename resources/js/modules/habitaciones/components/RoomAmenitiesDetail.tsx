import {
    Check,
    Clock,
    Wifi,
    Car,
    Coffee,
    Tv,
    Wind,
    Shield,
    Info,
    CalendarCheck,
} from 'lucide-react';

interface RoomAmenitiesDetailProps {
    amenities: string[];
    included: string[];
    policies: {
        checkIn: string;
        checkOut: string;
        cancellation: string;
        smoking: string;
        pets: string;
    };
}

const getAmenityIcon = (amenity: string) => {
    const low = amenity.toLowerCase();

    if (low.includes('wifi')) {
        return <Wifi className="h-5 w-5 text-black dark:text-white" />;
    }

    if (low.includes('tv')) {
        return <Tv className="h-5 w-5 text-black dark:text-white" />;
    }

    if (low.includes('aire')) {
        return <Wind className="h-5 w-5 text-black dark:text-white" />;
    }

    if (low.includes('cafe')) {
        return <Coffee className="h-5 w-5 text-black dark:text-white" />;
    }

    if (low.includes('estacion')) {
        return <Car className="h-5 w-5 text-black dark:text-white" />;
    }

    if (low.includes('caja')) {
        return <Shield className="h-5 w-5 text-black dark:text-white" />;
    }

    return <Check className="h-5 w-5 text-bugambilia-600" />;
};

export default function RoomAmenitiesDetail({
    amenities,
    included,
    policies,
}: RoomAmenitiesDetailProps) {
    return (
        <div className="bg-white dark:bg-gray-950">
            <div className="container mx-auto px-4 py-16 sm:px-6 lg:px-8">
                <div className="max-w-4xl">
                    <div className="grid grid-cols-1 gap-16 lg:grid-cols-2">
                        <div>
                            <h2 className="mb-10 border-b border-gray-100 pb-4 text-2xl text-xs font-black tracking-tighter tracking-widest text-gray-900 uppercase dark:border-gray-900 dark:text-white">
                                Amenidades Sugeridas
                            </h2>
                            <div className="space-y-6">
                                {amenities.map((amenity, index) => (
                                    <div
                                        key={index}
                                        className="group flex items-center gap-4"
                                    >
                                        <div className="transition-airbnb flex-shrink-0 group-hover:scale-110">
                                            {getAmenityIcon(amenity)}
                                        </div>
                                        <span className="transition-airbnb text-[15px] font-medium text-gray-600 group-hover:text-black dark:text-gray-400 dark:group-hover:text-white">
                                            {amenity}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div>
                            <h2 className="mb-10 border-b border-gray-100 pb-4 text-2xl text-xs font-black tracking-tighter tracking-widest text-gray-900 uppercase dark:border-gray-900 dark:text-white">
                                Servicios a tu Disposición
                            </h2>
                            <div className="space-y-6">
                                {included.map((service, index) => (
                                    <div
                                        key={index}
                                        className="group flex items-center gap-4"
                                    >
                                        <div className="transition-airbnb flex h-5 w-5 items-center justify-center rounded-full bg-gray-50 group-hover:bg-bugambilia-600 dark:bg-gray-900">
                                            <Check className="transition-airbnb h-3 w-3 text-gray-400 group-hover:text-white" />
                                        </div>
                                        <span className="transition-airbnb text-[15px] font-medium text-gray-600 group-hover:text-black dark:text-gray-400 dark:group-hover:text-white">
                                            {service}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="mt-20 border-t border-gray-100 pt-16 dark:border-gray-900">
                        <h2 className="mb-10 text-2xl font-black tracking-tighter text-gray-900 dark:text-white">
                            Reglas de la Casa
                        </h2>

                        <div className="mb-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div className="transition-airbnb rounded-3xl border border-transparent bg-gray-50 p-8 hover:border-gray-100 dark:bg-gray-900/50 dark:hover:border-gray-800">
                                <Clock className="mb-4 h-6 w-6 text-black dark:text-white" />
                                <h3 className="mb-1 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                    Check-in
                                </h3>
                                <p className="text-lg font-bold text-gray-900 dark:text-white">
                                    {policies.checkIn}
                                </p>
                            </div>

                            <div className="transition-airbnb rounded-3xl border border-transparent bg-gray-50 p-8 hover:border-gray-100 dark:bg-gray-900/50 dark:hover:border-gray-800">
                                <Clock className="mb-4 h-6 w-6 text-black dark:text-white" />
                                <h3 className="mb-1 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                    Check-out
                                </h3>
                                <p className="text-lg font-bold text-gray-900 dark:text-white">
                                    {policies.checkOut}
                                </p>
                            </div>

                            <div className="transition-airbnb rounded-3xl border border-transparent bg-gray-50 p-8 hover:border-gray-100 dark:bg-gray-900/50 dark:hover:border-gray-800">
                                <CalendarCheck className="mb-4 h-6 w-6 text-black dark:text-white" />
                                <h3 className="mb-1 text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                    Cancelación
                                </h3>
                                <p
                                    className="line-clamp-2 text-sm font-bold text-gray-900 dark:text-white"
                                    title={policies.cancellation}
                                >
                                    {policies.cancellation}
                                </p>
                            </div>
                        </div>

                        <div className="flex flex-col gap-4">
                            <div className="flex items-center gap-4 rounded-2xl border border-gray-100 px-6 py-4 dark:border-gray-900">
                                <Info className="h-5 w-5 text-bugambilia-600" />
                                <p className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <span className="font-bold text-black dark:text-white">
                                        Importante:
                                    </span>{' '}
                                    {policies.smoking} &bull; {policies.pets}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
