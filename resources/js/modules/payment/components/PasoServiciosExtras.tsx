import { Briefcase, Car, CheckCircle, Gift, HeartPulse } from 'lucide-react';
import type { ServicioExtra } from '@/modules/shared/types';
import { Button } from '@/modules/shared/ui/boton';

const ICONOS_SERVICIOS: Record<string, typeof Gift> = {
    romantic: Gift,
    transfer: Car,
    massage: HeartPulse,
    tour: Briefcase,
};

interface PropiedadesPasoServiciosExtras {
    servicios: ServicioExtra[];
    seleccionados: string[];
    alAlternar: (id: string) => void;
    alContinuar: () => void;
}

export const PasoServiciosExtras = ({
    servicios,
    seleccionados,
    alAlternar,
    alContinuar,
}: PropiedadesPasoServiciosExtras) => {
    return (
        <div className="animate-in fade-in slide-in-from-bottom-6 duration-700">
            <header className="mb-12">
                <h1 className="mb-4 text-4xl leading-none font-black tracking-tighter text-gray-900 md:text-6xl dark:text-white">
                    Mejora tu{' '}
                    <span className="text-bugambilia-gradient bg-clip-text text-transparent italic">
                        estancia
                    </span>
                </h1>
                <p className="text-lg font-medium text-gray-500">
                    Añade experiencias exclusivas a tu reserva.
                </p>
            </header>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                {servicios.map((servicio) => {
                    const Icono = ICONOS_SERVICIOS[servicio.id] ?? Gift;
                    const seleccionado = seleccionados.includes(servicio.id);

                    return (
                        <button
                            key={servicio.id}
                            type="button"
                            aria-pressed={seleccionado}
                            onClick={() => alAlternar(servicio.id)}
                            className={`group transition-airbnb relative flex cursor-pointer flex-col overflow-hidden rounded-[2.5rem] border-2 p-6 text-left ${
                                seleccionado
                                    ? 'border-black bg-white shadow-xl dark:border-white dark:bg-gray-800'
                                    : 'border-gray-100 bg-white/50 hover:border-gray-200 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-gray-700'
                            }`}
                        >
                            <div className="mb-6 flex items-start justify-between">
                                <span
                                    className={`transition-airbnb flex h-12 w-12 items-center justify-center rounded-2xl ${
                                        seleccionado
                                            ? 'bg-black text-white dark:bg-white dark:text-black'
                                            : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-400'
                                    }`}
                                >
                                    <Icono className="h-6 w-6" />
                                </span>
                                <span
                                    className={`transition-airbnb flex h-6 w-6 items-center justify-center rounded-full border-2 ${
                                        seleccionado
                                            ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black'
                                            : 'border-gray-200 dark:border-gray-700'
                                    }`}
                                >
                                    {seleccionado && (
                                        <CheckCircle className="h-4 w-4 fill-current" />
                                    )}
                                </span>
                            </div>
                            <h3 className="mb-2 text-lg font-black text-gray-900 dark:text-white">
                                {servicio.nombre}
                            </h3>
                            <p className="mb-6 text-xs leading-relaxed font-medium text-gray-500 dark:text-gray-300">
                                {servicio.descripcion}
                            </p>
                            <div className="mt-auto flex items-center justify-between">
                                <span className="text-sm font-black text-gray-900 dark:text-white">
                                    ${servicio.precio}
                                </span>
                                <span className="text-[10px] font-black tracking-widest text-bugambilia-600 uppercase">
                                    {seleccionado ? 'Añadido' : 'Añadir'}
                                </span>
                            </div>
                        </button>
                    );
                })}
            </div>

            <div className="flex flex-col items-center gap-6 pt-12 sm:flex-row">
                <Button
                    type="button"
                    onClick={alContinuar}
                    className="bg-bugambilia-gradient transition-airbnb h-16 w-full rounded-2xl border-none px-12 text-[10px] font-black tracking-[0.2em] text-white uppercase shadow-xl hover:scale-105 active:scale-95 sm:w-auto"
                >
                    Continuar al pago
                </Button>
                <button
                    type="button"
                    onClick={alContinuar}
                    className="text-[10px] font-black tracking-widest text-gray-400 uppercase transition-colors hover:text-black dark:hover:text-white"
                >
                    Saltar por ahora
                </button>
            </div>
        </div>
    );
};
