import { Link } from '@inertiajs/react';
import { CheckCircle, Lock, ShieldCheck } from 'lucide-react';

interface PasoPago {
    id: number;
    titulo: string;
}

interface PropiedadesCabeceraProcesoPago {
    nombreHotel: string;
    pasoActual: number;
    pasos: PasoPago[];
}

export const CabeceraProcesoPago = ({
    nombreHotel,
    pasoActual,
    pasos,
}: PropiedadesCabeceraProcesoPago) => {
    const iniciales = nombreHotel
        .split(' ')
        .map((palabra) => palabra[0])
        .join('');

    return (
        <header className="sticky top-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/80">
            <div className="container mx-auto flex h-20 items-center justify-between px-4 md:px-8">
                <Link href="/" className="group flex items-center gap-2">
                    <span className="transition-airbnb flex h-8 w-8 items-center justify-center rounded-lg bg-bugambilia-600 text-xs font-black text-white group-hover:scale-110">
                        {iniciales}
                    </span>
                    <span className="hidden text-xl font-black tracking-tighter text-gray-900 sm:block dark:text-white">
                        {nombreHotel.replace('Hotel ', '')}
                    </span>
                </Link>

                <nav
                    aria-label="Progreso del pago"
                    className="xs:gap-2 flex items-center gap-1.5 md:gap-10"
                >
                    {pasos.map((paso, indice) => {
                        const activo = pasoActual === paso.id;
                        const completado = pasoActual > paso.id;

                        return (
                            <div
                                key={paso.id}
                                className="xs:gap-2 flex items-center gap-1"
                            >
                                <span
                                    className={`xs:h-7 xs:w-7 xs:text-[10px] transition-airbnb flex h-6 w-6 items-center justify-center rounded-full text-[9px] font-black ${
                                        activo || completado
                                            ? 'bg-black text-white dark:bg-white dark:text-black'
                                            : 'bg-gray-100 text-gray-400 dark:bg-gray-800'
                                    }`}
                                >
                                    {completado ? (
                                        <CheckCircle className="xs:h-3.5 xs:w-3.5 h-3 w-3" />
                                    ) : (
                                        paso.id
                                    )}
                                </span>
                                <span
                                    className={`xs:text-[10px] transition-airbnb hidden text-[9px] font-black tracking-widest uppercase sm:inline ${
                                        activo
                                            ? 'text-black dark:text-white'
                                            : 'text-gray-400'
                                    }`}
                                >
                                    {paso.titulo}
                                </span>
                                {indice < pasos.length - 1 && (
                                    <span className="hidden h-px w-4 bg-gray-200 sm:block dark:bg-gray-700" />
                                )}
                            </div>
                        );
                    })}
                </nav>

                <div className="hidden items-center gap-4 lg:flex">
                    <EstadoSeguridad
                        icono={Lock}
                        texto="Pago seguro"
                        resaltado
                    />
                    <EstadoSeguridad
                        icono={ShieldCheck}
                        texto="Reserva garantizada"
                    />
                </div>
            </div>
        </header>
    );
};

interface PropiedadesEstadoSeguridad {
    icono: typeof Lock;
    texto: string;
    resaltado?: boolean;
}

const EstadoSeguridad = ({
    icono: Icono,
    texto,
    resaltado = false,
}: PropiedadesEstadoSeguridad) => {
    return (
        <div
            className={
                resaltado
                    ? 'flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-emerald-600 dark:border-emerald-800 dark:bg-emerald-900/20'
                    : 'flex items-center gap-1.5 text-gray-400'
            }
        >
            <Icono className={resaltado ? 'h-3 w-3' : 'h-4 w-4'} />
            <span className="text-[9px] font-black tracking-widest uppercase">
                {texto}
            </span>
        </div>
    );
};
