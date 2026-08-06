import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Clock,
    FileQuestion,
    Home,
    RefreshCw,
    ServerCrash,
    ShieldAlert,
    Wrench,
    ZapOff,
} from 'lucide-react';

interface PropiedadesError {
    status?: number;
    message?: string;
}

interface InfoEstado {
    codigo: number;
    badge: string;
    titulo: string;
    descripcion: string;
    icono: React.ElementType;
    colorGradienteDot: string;
    colorIconoBg: string;
}

const OBTENER_INFO_ERROR = (
    status: number,
    messageCustom?: string,
): InfoEstado => {
    switch (status) {
        case 404:
            return {
                codigo: 404,
                badge: 'Página no encontrada',
                titulo: '¿Te has perdido?',
                descripcion:
                    messageCustom ||
                    'La página que estás buscando no existe, ha sido movida o la dirección ingresada es incorrecta.',
                icono: FileQuestion,
                colorGradienteDot: 'from-amber-500 to-orange-600',
                colorIconoBg:
                    'bg-amber-500/10 text-amber-600 dark:text-amber-400',
            };
        case 403:
            return {
                codigo: 403,
                badge: 'Acceso Restringido',
                titulo: 'Acceso Prohibido',
                descripcion:
                    messageCustom ||
                    'No cuentas con los permisos necesarios para visualizar este contenido o acceder a esta función.',
                icono: ShieldAlert,
                colorGradienteDot: 'from-red-500 to-rose-600',
                colorIconoBg: 'bg-destructive/10 text-destructive',
            };
        case 419:
            return {
                codigo: 419,
                badge: 'Sesión Expirada',
                titulo: 'La sesión ha caducado',
                descripcion:
                    messageCustom ||
                    'Por motivos de seguridad y tras un periodo de inactividad, tu sesión ha expirado. Por favor, recarga e intentalo de nuevo.',
                icono: Clock,
                colorGradienteDot: 'from-purple-500 to-indigo-600',
                colorIconoBg:
                    'bg-purple-500/10 text-purple-600 dark:text-purple-400',
            };
        case 429:
            return {
                codigo: 429,
                badge: 'Límite de Solicitudes',
                titulo: 'Demasiadas peticiones',
                descripcion:
                    messageCustom ||
                    'Has realizado demasiadas solicitudes en un periodo corto. Por favor, aguarda unos segundos antes de reintentar.',
                icono: ZapOff,
                colorGradienteDot: 'from-blue-500 to-cyan-600',
                colorIconoBg: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
            };
        case 503:
            return {
                codigo: 503,
                badge: 'Servicio en Mantenimiento',
                titulo: 'Volveremos muy pronto',
                descripcion:
                    messageCustom ||
                    'Estamos realizando tareas de optimización y mantenimiento en nuestros sistemas para ofrecerte un mejor servicio.',
                icono: Wrench,
                colorGradienteDot: 'from-emerald-500 to-teal-600',
                colorIconoBg:
                    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            };
        case 500:
        default:
            return {
                codigo: status || 500,
                badge: 'Error del Servidor',
                titulo: 'Algo no salió como esperábamos',
                descripcion:
                    messageCustom ||
                    'Ocurrió un error inesperado al procesar tu solicitud. Nuestro equipo técnico ha sido notificado.',
                icono: ServerCrash,
                colorGradienteDot: 'from-bugambilia-500 to-bugambilia-700',
                colorIconoBg:
                    'bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-300',
            };
    }
};

export default function Error({ status = 500, message }: PropiedadesError) {
    const info = OBTENER_INFO_ERROR(status, message);
    const Icono = info.icono;

    const manejarReintentar = () => {
        window.location.reload();
    };

    const manejarVolverAtras = () => {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/';
        }
    };

    return (
        <>
            <Head title={`${info.codigo} - ${info.badge}`} />

            <div className="relative flex min-h-[75vh] w-full items-center justify-center bg-background px-4 py-12 text-foreground sm:px-6 lg:px-8">
                {/* Glow decorativo del sistema */}
                <div className="pointer-events-none absolute inset-0 flex items-center justify-center overflow-hidden opacity-30">
                    <div className="h-[450px] w-[450px] rounded-full bg-gradient-to-tr from-bugambilia-400/20 via-primary/20 to-purple-500/20 blur-3xl" />
                </div>

                <div className="relative z-10 w-full max-w-xl text-center">
                    {/* Tarjeta Principal usando variables del sistema */}
                    <div className="luxury-glass shadow-airbnb-hover overflow-hidden rounded-3xl border border-border p-8 backdrop-blur-xl transition-all sm:p-12">
                        {/* Insignia del Código de Estado */}
                        <div className="mb-6 flex justify-center">
                            <span className="inline-flex items-center gap-2 rounded-full border border-border bg-muted px-4 py-1.5 text-xs font-black tracking-widest text-muted-foreground uppercase shadow-xs">
                                <span
                                    className={`h-2 w-2 rounded-full bg-gradient-to-r ${info.colorGradienteDot}`}
                                />
                                {info.codigo} • {info.badge}
                            </span>
                        </div>

                        {/* Ícono de Error Principal */}
                        <div className="mb-6 flex justify-center">
                            <div
                                className={`flex h-20 w-20 items-center justify-center rounded-3xl ${info.colorIconoBg} shadow-inner transition-transform hover:scale-105`}
                            >
                                <Icono className="h-10 w-10" />
                            </div>
                        </div>

                        {/* Título Principal */}
                        <h1 className="mb-3 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                            {info.titulo}
                        </h1>

                        {/* Descripción */}
                        <p className="mx-auto mb-8 max-w-md text-sm leading-relaxed text-muted-foreground sm:text-base">
                            {info.descripcion}
                        </p>

                        {/* Botones de Acción usando variables del tema */}
                        <div className="flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                            <Link
                                href="/"
                                className="bugambilia-gradient inline-flex w-full items-center justify-center gap-2 rounded-full px-6 py-3.5 text-xs font-bold tracking-wider text-white uppercase shadow-lg transition-all hover:opacity-90 active:scale-95 sm:w-auto"
                            >
                                <Home className="h-4 w-4" />
                                Ir al inicio
                            </Link>

                            <button
                                type="button"
                                onClick={manejarReintentar}
                                className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border bg-card px-6 py-3.5 text-xs font-bold tracking-wider text-card-foreground uppercase shadow-xs transition-all hover:bg-accent hover:text-accent-foreground active:scale-95 sm:w-auto"
                            >
                                <RefreshCw className="h-4 w-4" />
                                Reintentar
                            </button>

                            <button
                                type="button"
                                onClick={manejarVolverAtras}
                                className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border bg-muted px-6 py-3.5 text-xs font-bold tracking-wider text-muted-foreground uppercase shadow-xs transition-all hover:bg-secondary hover:text-secondary-foreground active:scale-95 sm:w-auto"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Volver atrás
                            </button>
                        </div>
                    </div>

                    {/* Pie decorativo */}
                    <div className="mt-8 text-center text-xs font-medium text-muted-foreground">
                        Hotel Bugambilias — Servicio y Confort
                    </div>
                </div>
            </div>
        </>
    );
}
