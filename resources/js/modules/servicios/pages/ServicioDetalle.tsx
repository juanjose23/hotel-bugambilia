import { Link } from '@inertiajs/react';
import { ShieldCheck, ChevronLeft, Sparkles, AlertCircle } from 'lucide-react';
import { useState } from 'react';
import BotonReservaWhatsApp from '@/modules/compartido/componentes/BotonReservaWhatsApp';
import type { ServicioItem } from '@/modules/compartido/tipos';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

interface ServicioDetalleProps {
    service: ServicioItem & {
        imagenes: string[];
    };
}

export default function ServicioDetalle({ service }: ServicioDetalleProps) {
    const [activeImageIndex, setActiveImageIndex] = useState(0);

    const imagenes =
        service.imagenes && service.imagenes.length > 0
            ? service.imagenes
            : ['/images/terrace.jpg'];
    const currentImage = imagenes[activeImageIndex] || imagenes[0];

    return (
        <LayoutPublico>
            {/* Migas de Pan / Breadcrumbs */}
            <div className="border-b border-border/40 bg-card py-3 font-sans">
                <div className="container mx-auto flex items-center gap-2 px-4 text-xs font-semibold text-muted-foreground sm:px-6 lg:px-8">
                    <Link
                        href="/servicios"
                        className="inline-flex items-center gap-1 transition-colors hover:text-foreground"
                    >
                        <ChevronLeft className="h-3.5 w-3.5" />
                        Servicios
                    </Link>
                    <span>/</span>
                    <span className="font-bold text-foreground">
                        {service.categoria || 'Servicio'}
                    </span>
                    <span>/</span>
                    <span className="max-w-[220px] truncate font-bold text-bugambilia-600 dark:text-bugambilia-400">
                        {service.nombre}
                    </span>
                </div>
            </div>

            {/* Hero Principal del Servicio */}
            <section className="relative border-b border-border/40 bg-background py-10 font-sans md:py-14">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid items-start gap-8 lg:grid-cols-12 lg:gap-12">
                        {/* Contenedor Fotográfico con Miniaturas */}
                        <div className="space-y-4 lg:col-span-7">
                            <div className="shadow-airbnb relative aspect-[16/10] overflow-hidden rounded-3xl border border-border/80 bg-muted/40">
                                <img
                                    src={currentImage}
                                    alt={service.nombre}
                                    className="h-full w-full object-cover transition-all duration-300"
                                />
                                {service.categoria && (
                                    <div className="absolute top-4 left-4 z-10">
                                        <span className="rounded-full border border-white/20 bg-black/70 px-3.5 py-1.5 text-xs font-extrabold tracking-wider text-white uppercase backdrop-blur-md">
                                            {service.categoria}
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Galería de Miniaturas */}
                            {imagenes.length > 1 && (
                                <div className="flex items-center gap-3 overflow-x-auto pb-2">
                                    {imagenes.map((img, idx) => (
                                        <button
                                            key={idx}
                                            onClick={() =>
                                                setActiveImageIndex(idx)
                                            }
                                            className={`relative h-20 w-20 shrink-0 cursor-pointer overflow-hidden rounded-2xl border-2 transition-all ${
                                                activeImageIndex === idx
                                                    ? 'scale-95 border-bugambilia-600 shadow-md'
                                                    : 'border-border/60 opacity-70 hover:opacity-100'
                                            }`}
                                        >
                                            <img
                                                src={img}
                                                alt={`Galería ${idx + 1}`}
                                                className="h-full w-full object-cover"
                                            />
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Resumen & Tarjeta de Acción */}
                        <div className="flex flex-col justify-between lg:col-span-5">
                            <div>
                                {service.codigo && (
                                    <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-extrabold tracking-widest text-amber-500 uppercase">
                                        <Sparkles className="h-3.5 w-3.5" />
                                        Código: {service.codigo}
                                    </div>
                                )}

                                <h1 className="mb-4 text-2xl leading-tight font-black tracking-tight text-foreground sm:text-3xl lg:text-4xl">
                                    {service.nombre}
                                </h1>

                                {service.descripcion && (
                                    <p className="mb-6 text-sm leading-relaxed font-medium text-muted-foreground sm:text-base">
                                        {service.descripcion}
                                    </p>
                                )}

                                {/* Caja de Precio */}
                                {service.precio !== null &&
                                    service.precio !== undefined && (
                                        <div className="shadow-airbnb-subtle mb-6 flex items-center justify-between rounded-2xl border border-border/80 bg-card p-5">
                                            <div>
                                                <span className="mb-0.5 block text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                                    Precio del Servicio
                                                </span>
                                                <div className="text-3xl font-black text-bugambilia-600 dark:text-bugambilia-400">
                                                    {service.moneda || '$'}
                                                    {service.precio}
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                    <ShieldCheck className="h-4 w-4" />
                                                    Servicio Activo
                                                </span>
                                            </div>
                                        </div>
                                    )}
                            </div>

                            {/* Botón Reutilizable de WhatsApp */}
                            <BotonReservaWhatsApp
                                nombreItem={service.nombre}
                                codigoItem={service.codigo}
                                tipo="servicio"
                            />
                        </div>
                    </div>
                </div>
            </section>

            {/* Sección de Políticas */}
            {service.politicas && service.politicas.length > 0 && (
                <section className="border-t border-border/40 bg-background py-12 font-sans md:py-16">
                    <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="max-w-4xl">
                            <h2 className="mb-6 text-xl font-black tracking-tight text-foreground sm:text-2xl">
                                Políticas del{' '}
                                <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                    Servicio
                                </span>
                            </h2>

                            <div className="grid gap-4 sm:grid-cols-2">
                                {service.politicas.map((pol) => (
                                    <div
                                        key={pol.id || pol.nombre}
                                        className="rounded-2xl border border-border/80 bg-card p-5"
                                    >
                                        <div className="mb-2 flex items-center gap-2">
                                            <AlertCircle className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                            <h3 className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                                                {pol.nombre}
                                            </h3>
                                        </div>
                                        {pol.descripcion && (
                                            <p className="pl-6 text-xs leading-relaxed text-muted-foreground">
                                                {pol.descripcion}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>
            )}
        </LayoutPublico>
    );
}
