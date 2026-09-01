import { Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Award,
    Tag,
    MessageCircle,
    MessageSquare,
} from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import type { ServicioItem } from '../types';

interface PropsServicioDetalleHero {
    service: ServicioItem;
    alAbrirConsulta: () => void;
    telefonoWhatsApp?: string;
}

export const ServicioDetalleHero = ({
    service,
    alAbrirConsulta,
    telefonoWhatsApp = '50584842323',
}: PropsServicioDetalleHero) => {
    const imagenes =
        service.imagenes && service.imagenes.length > 0
            ? service.imagenes
            : [service.imagen || '/images/service-kitchen.webp'];

    const mensajeWhatsApp = encodeURIComponent(
        `¡Hola Hotel Bugambilias! Deseo más información o reservar el servicio "${service.nombre}".`,
    );

    return (
        <div className="font-sans">
            {/* Barra de Navegación de Retorno */}
            <div className="border-b border-border bg-card/60 py-3.5 backdrop-blur-md">
                <div className="container mx-auto flex items-center justify-between px-4 sm:px-6">
                    <Link
                        href="/servicios"
                        className="inline-flex items-center gap-1.5 text-xs font-black text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        <span>Volver a servicios</span>
                    </Link>

                    {service.categoria && (
                        <span className="rounded-full border border-border bg-muted px-3 py-0.5 text-xs font-bold text-foreground">
                            {service.categoria}
                        </span>
                    )}
                </div>
            </div>

            <div className="container mx-auto px-4 py-8 sm:px-6 lg:max-w-5xl">
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    {/* Galería / Imagen Principal (7 cols) */}
                    <div className="flex flex-col gap-3 lg:col-span-7">
                        <div className="relative aspect-16/9 w-full overflow-hidden rounded-3xl border border-border bg-muted shadow-lg">
                            <img
                                src={imagenes[0]}
                                alt={service.nombre}
                                className="h-full w-full object-cover"
                            />
                            {service.precio !== undefined &&
                                service.precio !== null &&
                                Number(service.precio) > 0 && (
                                    <div className="absolute right-4 bottom-4 flex items-center gap-1.5 rounded-full border border-white/20 bg-foreground/95 px-3.5 py-1 text-sm font-black text-background shadow-lg backdrop-blur-md">
                                        <Tag className="size-4" />
                                        <span>
                                            {service.moneda || '$'}
                                            {Number(service.precio).toFixed(2)}
                                        </span>
                                    </div>
                                )}
                        </div>

                        {/* Miniaturas si hay múltiples imágenes */}
                        {imagenes.length > 1 && (
                            <div className="flex items-center gap-2 overflow-x-auto pb-1">
                                {imagenes.map((img, idx) => (
                                    <div
                                        key={idx}
                                        className="relative aspect-16/9 h-16 shrink-0 overflow-hidden rounded-xl border border-border bg-muted"
                                    >
                                        <img
                                            src={img}
                                            alt={`${service.nombre} ${idx + 1}`}
                                            className="h-full w-full object-cover"
                                        />
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Información y Acciones (5 cols) */}
                    <div className="flex flex-col justify-between lg:col-span-5">
                        <div>
                            <div className="inline-flex items-center gap-1.5 text-xs font-black tracking-wider text-primary uppercase dark:text-rose-400">
                                <Award className="size-3.5" />
                                <span>Hotel Bugambilias • Estelí</span>
                            </div>

                            <h1 className="mt-2 text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                                {service.nombre}
                            </h1>

                            <p className="mt-4 text-xs leading-relaxed text-muted-foreground sm:text-sm">
                                {service.descripcion ||
                                    'Disfruta de nuestros servicios exclusivos diseñados para brindar una estancia inolvidable a huéspedes y visitantes en Hotel Bugambilias.'}
                            </p>
                        </div>

                        {/* Botones de Acción */}
                        <div className="mt-8 flex flex-col gap-3 border-t border-border/60 pt-6">
                            <a
                                href={`https://wa.me/${telefonoWhatsApp}?text=${mensajeWhatsApp}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-3.5 text-xs font-black text-white shadow-md transition-all hover:bg-emerald-700 active:scale-95 dark:bg-emerald-700 dark:hover:bg-emerald-800"
                            >
                                <MessageCircle className="size-4" />
                                <span>Consultar o Reservar por WhatsApp</span>
                            </a>

                            <Button
                                type="button"
                                variant="outline"
                                onClick={alAbrirConsulta}
                                className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border border-border py-3 text-xs font-bold text-foreground shadow-xs transition-all hover:bg-muted active:scale-95"
                            >
                                <MessageSquare className="size-3.5" />
                                <span>Solicitar Cotización / Consulta</span>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ServicioDetalleHero;
