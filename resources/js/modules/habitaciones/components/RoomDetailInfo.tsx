import { usePage } from '@inertiajs/react';
import { Shield, Wifi, Sparkles, Key, Navigation2, Star } from 'lucide-react';

interface Room {
    name: string;
    longDescription: string;
    view: string;
    floor: string;
    size: string;
}

interface RoomDetailInfoProps {
    room: Room;
}

export default function RoomDetailInfo({ room }: RoomDetailInfoProps) {
    const { hotel } = usePage().props;

    return (
        <div className="bg-white dark:bg-gray-950">
            <div className="container mx-auto px-4 py-10 sm:px-6 lg:px-8">
                <div className="max-w-4xl">
                    <div className="mb-10 flex items-center justify-between border-b border-gray-100 pb-10 dark:border-gray-900">
                        <div className="flex items-center gap-5">
                            <div className="group relative">
                                <div className="transition-airbnb flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-bugambilia-600 to-bugambilia-400 shadow-lg group-hover:rotate-6">
                                    <span className="text-xl font-black text-white">
                                        {hotel.name
                                            .split(' ')
                                            .map((w) => w[0])
                                            .join('')}
                                    </span>
                                </div>
                                <div className="absolute -right-1 -bottom-1 rounded-full bg-white p-1 shadow-sm dark:bg-gray-900">
                                    <Shield className="h-4 w-4 text-bugambilia-600" />
                                </div>
                            </div>
                            <div>
                                <h3 className="text-xl font-black tracking-tight text-gray-900 dark:text-white">{`Anfitrión: ${hotel.name}`}</h3>
                                <p className="flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <span>Legendario</span>
                                    <span>•</span>
                                    <span>{`Anfitrión hace ${new Date().getFullYear() - hotel.fundado} años`}</span>
                                </p>
                            </div>
                        </div>

                        <div className="hidden flex-col items-end sm:flex">
                            <div className="mb-1 flex items-center gap-1">
                                <Star className="h-4 w-4 fill-black dark:fill-white" />
                                <span className="text-sm font-black">4.92</span>
                            </div>
                            <span className="text-[10px] font-black tracking-widest text-gray-400 uppercase">
                                Puntuación del anfitrión
                            </span>
                        </div>
                    </div>

                    <div className="mb-12 space-y-10">
                        <div className="group flex items-start gap-6">
                            <div className="mt-1 flex-shrink-0">
                                <Navigation2 className="transition-airbnb h-8 w-8 text-black group-hover:scale-110 dark:text-white" />
                            </div>
                            <div className="flex-grow">
                                <h4 className="mb-1 text-lg font-black tracking-tight text-gray-900 dark:text-white">
                                    Ubicación Estratégica
                                </h4>
                                <p className="leading-relaxed font-medium text-gray-500 dark:text-gray-400">
                                    Situada en el {room.floor}, ofrece el
                                    equilibrio perfecto entre accesibilidad y
                                    paz absoluta.
                                </p>
                            </div>
                        </div>

                        <div className="group flex items-start gap-6">
                            <div className="mt-1 flex-shrink-0">
                                <Key className="transition-airbnb h-8 w-8 text-black group-hover:scale-110 dark:text-white" />
                            </div>
                            <div className="flex-grow">
                                <h4 className="mb-1 text-lg font-black tracking-tight text-gray-900 dark:text-white">
                                    Entrada autónoma
                                </h4>
                                <p className="leading-relaxed font-medium text-gray-500 dark:text-gray-400">
                                    Llega a la hora que prefieras con nuestro
                                    sistema de entrada digital boutique.
                                </p>
                            </div>
                        </div>

                        <div className="group flex items-start gap-6">
                            <div className="mt-1 flex-shrink-0">
                                <Sparkles className="transition-airbnb h-8 w-8 text-black group-hover:scale-110 dark:text-white" />
                            </div>
                            <div className="flex-grow">
                                <h4 className="mb-1 text-lg font-black tracking-tight text-gray-900 dark:text-white">
                                    Vistas de Estelí
                                </h4>
                                <p className="leading-relaxed font-medium text-gray-500 dark:text-gray-400">
                                    {room.view}. Disfruta de atardeceres únicos
                                    desde la comodidad de tu estancia.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="mb-12 border-b border-gray-100 pb-12 dark:border-gray-900">
                        <div className="rounded-3xl border border-gray-100 bg-gray-50 p-8 dark:border-gray-800 dark:bg-gray-900/50">
                            <p className="font-serif text-lg leading-relaxed text-gray-600 italic dark:text-gray-300">
                                &ldquo;{room.longDescription}&rdquo;
                            </p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div className="transition-airbnb flex items-center gap-4 rounded-2xl border border-gray-100 p-6 hover:border-black dark:border-gray-800 dark:hover:border-white">
                            <Wifi className="h-6 w-6 text-bugambilia-600" />
                            <span className="text-sm font-bold text-gray-700 dark:text-gray-300">
                                Conexión de Fibra Óptica
                            </span>
                        </div>
                        <div className="transition-airbnb flex items-center gap-4 rounded-2xl border border-gray-100 p-6 hover:border-black dark:border-gray-800 dark:hover:border-white">
                            <Shield className="h-6 w-6 text-bugambilia-600" />
                            <span className="text-sm font-bold text-gray-700 dark:text-gray-300">
                                Garantía de Privacidad Total
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
