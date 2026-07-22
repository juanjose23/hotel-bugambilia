import { Link, useForm, usePage } from '@inertiajs/react';
import {
    Users,
    ChevronLeft,
    Sparkles,
    Maximize2,
    CheckCircle2,
    AlertCircle,
    MapPin,
    Calendar,
    ArrowRight,
} from 'lucide-react';
import { useState } from 'react';
import VisorGaleriaModal from '@/modules/compartido/componentes/VisorGaleriaModal';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';
import { Badge } from '@/modules/shared/ui/badge';
import { Button } from '@/modules/shared/ui/button';

interface EspacioItem {
    id: number;
    codigo: string;
    nombre: string;
    tipo: string;
    tipo_label: string;
    descripcion: string;
    ubicacion: string;
    precio: number;
    moneda: string;
    capacidad: number;
    web: boolean;
    reservable: boolean;
    es_restaurante: boolean;
    imagenes: string[];
    meta_datos?: any;
    serviciosIncluidos?: string[];
    politicas?: Array<{ id?: number; nombre: string; descripcion: string }>;
}

interface SimilarSpace {
    id: number;
    nombre: string;
    tipo: string;
    precio: number;
    moneda: string;
    imagen: string;
}

interface EspacioDetalleProps {
    space: EspacioItem;
    similarSpaces?: SimilarSpace[];
}

export default function EspacioDetalle({
    space,
    similarSpaces = [],
}: EspacioDetalleProps) {
    const [activeImageIndex, setActiveImageIndex] = useState(0);
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);
    const [horaInicio, setHoraInicio] = useState('12:00');
    const [horaFin, setHoraFin] = useState('14:00');

    const pageProps = usePage().props as any;
    const authUser = pageProps.auth?.user;

    const { data, setData, post, processing } = useForm({
        nombre_cliente: authUser?.name || '',
        telefono_cliente: '',
        email_cliente: authUser?.email || '',
        tipo_reserva: 'servicio',
        espacio_id: String(space?.id || ''),
        fecha_check_in: new Date().toISOString().split('T')[0],
        hora_reserva: '12:00 - 14:00',
        adultos: 2,
        ninos: 0,
        notas: '',
    });

    const imagenes =
        space?.imagenes && space.imagenes.length > 0
            ? space.imagenes
            : ['/images/terrace.jpg'];
    const currentImage = imagenes[activeImageIndex] || imagenes[0];

    const serviciosIncluidos = space?.serviciosIncluidos || [];
    const politicas = space?.politicas || [];

    const handleSubmitReserva = (e: React.FormEvent) => {
        e.preventDefault();
        post('/reservas');
    };

    return (
        <LayoutPublico>
            {/* Migas de Pan / Breadcrumbs */}
            <div className="border-b border-border/40 bg-card py-3 font-sans">
                <div className="container mx-auto flex items-center gap-2 px-4 text-xs font-semibold text-muted-foreground sm:px-6 lg:px-8">
                    <Link
                        href="/espacios"
                        className="inline-flex items-center gap-1 transition-colors hover:text-foreground"
                    >
                        <ChevronLeft className="h-3.5 w-3.5" />
                        Espacios
                    </Link>
                    <span>/</span>
                    <span className="font-bold text-foreground">
                        {space?.tipo_label || 'Instalación'}
                    </span>
                    <span>/</span>
                    <span className="max-w-[220px] truncate font-bold text-bugambilia-600 dark:text-bugambilia-400">
                        {space?.nombre}
                    </span>
                </div>
            </div>

            {/* Hero Principal con Galería */}
            <section className="relative border-b border-border/40 bg-background py-10 font-sans md:py-14">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid items-start gap-8 lg:grid-cols-12 lg:gap-12">
                        {/* Fotografía Principal & Miniaturas */}
                        <div className="space-y-4 lg:col-span-7">
                            <div
                                onClick={() => setIsLightboxOpen(true)}
                                className="group shadow-airbnb relative aspect-[16/10] cursor-pointer overflow-hidden rounded-3xl border border-border/80 bg-muted/40"
                            >
                                <img
                                    src={currentImage}
                                    alt={space?.nombre}
                                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />

                                <div className="absolute top-4 left-4 z-10 flex items-center gap-2">
                                    <span className="rounded-full border border-white/20 bg-black/70 px-3.5 py-1.5 text-xs font-extrabold tracking-wider text-white uppercase backdrop-blur-md">
                                        {space?.tipo_label}
                                    </span>
                                </div>

                                <div className="absolute right-4 bottom-4 z-10">
                                    <span className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-black/60 px-3 py-1.5 text-xs font-bold text-white opacity-90 backdrop-blur-md transition-opacity group-hover:opacity-100">
                                        <Maximize2 className="h-3.5 w-3.5" />
                                        Ver Pantalla Completa
                                    </span>
                                </div>
                            </div>

                            {/* Miniaturas de Galería */}
                            {imagenes.length > 1 && (
                                <div className="flex scrollbar-none items-center gap-3 overflow-x-auto pb-2">
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
                                                alt=""
                                                className="h-full w-full object-cover"
                                            />
                                        </button>
                                    ))}
                                </div>
                            )}

                            {/* Datos & Amenidades */}
                            <div className="space-y-6 pt-6">
                                <div className="flex items-center gap-2 text-xs font-bold text-amber-600 dark:text-amber-400">
                                    <MapPin className="h-4 w-4" />
                                    <span>{space?.ubicacion}</span>
                                </div>

                                <h1 className="text-3xl font-black tracking-tight text-foreground sm:text-4xl">
                                    {space?.nombre}
                                </h1>

                                <p className="text-sm leading-relaxed text-muted-foreground">
                                    {space?.descripcion}
                                </p>

                                {/* Características dinámicas específicas de meta_datos */}
                                {space?.meta_datos && (
                                    <div className="border-t border-border/60 pt-6">
                                        <h3 className="mb-4 text-sm font-extrabold tracking-wider text-foreground uppercase">
                                            Detalles de Instalación
                                        </h3>
                                        <div className="grid grid-cols-2 gap-4 text-xs font-semibold text-foreground">
                                            {space.meta_datos
                                                .metros_cuadrados && (
                                                <div className="flex flex-col gap-1 rounded-2xl border border-border/80 bg-card p-3">
                                                    <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                        Superficie útil
                                                    </span>
                                                    <span className="font-extrabold text-amber-600 dark:text-amber-400">
                                                        {
                                                            space.meta_datos
                                                                .metros_cuadrados
                                                        }{' '}
                                                        m²
                                                    </span>
                                                </div>
                                            )}
                                            {space.meta_datos.tipo_servicio && (
                                                <div className="flex flex-col gap-1 rounded-2xl border border-border/80 bg-card p-3">
                                                    <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                        Servicio disponible
                                                    </span>
                                                    <span className="font-extrabold">
                                                        {
                                                            space.meta_datos
                                                                .tipo_servicio
                                                        }
                                                    </span>
                                                </div>
                                            )}
                                            {space.meta_datos
                                                .horario_comida && (
                                                <div className="flex flex-col gap-1 rounded-2xl border border-border/80 bg-card p-3">
                                                    <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                        Horario General
                                                    </span>
                                                    <span className="font-extrabold text-bugambilia-600">
                                                        {
                                                            space.meta_datos
                                                                .horario_comida
                                                        }
                                                    </span>
                                                </div>
                                            )}
                                            {space.meta_datos
                                                .restricciones_gimnasio && (
                                                <div className="col-span-2 space-y-1 rounded-2xl border border-border/80 bg-card p-4">
                                                    <span className="text-[10px] font-bold text-muted-foreground uppercase">
                                                        Políticas de Acceso
                                                    </span>
                                                    <p className="text-[11px] leading-relaxed text-muted-foreground">
                                                        {
                                                            space.meta_datos
                                                                .restricciones_gimnasio
                                                        }
                                                    </p>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {/* Servicios Incluidos */}
                                {serviciosIncluidos.length > 0 && (
                                    <div className="border-t border-border/60 pt-6">
                                        <h3 className="mb-4 text-sm font-extrabold tracking-wider text-foreground uppercase">
                                            Servicios y Comodidades
                                        </h3>
                                        <ul className="grid gap-3 sm:grid-cols-2">
                                            {serviciosIncluidos.map(
                                                (serv, idx) => (
                                                    <li
                                                        key={idx}
                                                        className="flex items-start gap-2.5 text-xs text-muted-foreground"
                                                    >
                                                        <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                                        <span className="font-medium text-foreground">
                                                            {serv}
                                                        </span>
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Caja Flotante de Reserva (Columna derecha) */}
                        <div className="lg:sticky lg:top-28 lg:col-span-5">
                            <div className="shadow-airbnb space-y-6 rounded-3xl border border-border bg-card p-6 md:p-8">
                                {/* Cabecera Tarjeta */}
                                <div className="flex items-baseline justify-between border-b border-border/40 pb-4">
                                    <div>
                                        <span className="block text-xs font-bold text-muted-foreground">
                                            Precio del Espacio
                                        </span>
                                        <span className="text-3xl font-black text-foreground">
                                            {space?.precio && space.precio > 0
                                                ? `${space.moneda} ${numberFormat(space.precio)}`
                                                : 'Acceso Libre'}
                                        </span>
                                        {space?.precio && space.precio > 0 ? (
                                            <span className="text-xs font-semibold text-muted-foreground">
                                                {' '}
                                                / evento o reserva
                                            </span>
                                        ) : null}
                                    </div>
                                    <Badge
                                        variant="outline"
                                        className="border-emerald-500/25 bg-emerald-500/5 px-3 py-1 text-[10px] font-bold text-emerald-600"
                                    >
                                        <Users className="mr-1 h-3.5 w-3.5" />
                                        Max: {space?.capacidad} pers
                                    </Badge>
                                </div>

                                {/* Sección Formulario o Redirección a Inicio de Sesión */}
                                {!authUser ? (
                                    <div className="space-y-4 py-4 text-center">
                                        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-bugambilia-500/10">
                                            <Sparkles className="h-6 w-6 text-bugambilia-600" />
                                        </div>
                                        <div className="space-y-1">
                                            <h3 className="text-sm font-extrabold text-foreground">
                                                Inicie Sesión para Reservar
                                            </h3>
                                            <p className="text-xs text-muted-foreground">
                                                Debe tener una cuenta de usuario
                                                para realizar y gestionar sus
                                                reservas de ambientes.
                                            </p>
                                        </div>
                                        <Button
                                            asChild
                                            className="w-full cursor-pointer rounded-2xl bg-bugambilia-600 py-4 font-black text-white shadow-md hover:bg-bugambilia-700"
                                        >
                                            <Link href="/login">
                                                Iniciar Sesión
                                                <ArrowRight className="ml-2 h-4 w-4" />
                                            </Link>
                                        </Button>
                                    </div>
                                ) : space?.reservable ? (
                                    <form
                                        onSubmit={handleSubmitReserva}
                                        className="space-y-4"
                                    >
                                        <h3 className="mb-2 text-sm font-black tracking-wider text-foreground uppercase">
                                            Detalles de su Reserva
                                        </h3>

                                        <div className="space-y-3">
                                            <div>
                                                <label className="mb-1 block text-[10px] font-black text-muted-foreground uppercase">
                                                    Nombre para la Reserva
                                                </label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={data.nombre_cliente}
                                                    onChange={(e) =>
                                                        setData(
                                                            'nombre_cliente',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="w-full rounded-2xl border border-border bg-background px-4 py-2.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                                    placeholder="Ej. Carlos Mendoza"
                                                />
                                            </div>

                                            <div>
                                                <label className="mb-1 block text-[10px] font-black text-muted-foreground uppercase">
                                                    Teléfono Móvil
                                                </label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={
                                                        data.telefono_cliente
                                                    }
                                                    onChange={(e) =>
                                                        setData(
                                                            'telefono_cliente',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="w-full rounded-2xl border border-border bg-background px-4 py-2.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                                    placeholder="+505 8888 8888"
                                                />
                                            </div>

                                            <div className="grid grid-cols-2 gap-3">
                                                <div className="col-span-2">
                                                    <label className="mb-1 block text-[10px] font-black text-muted-foreground uppercase">
                                                        Fecha
                                                    </label>
                                                    <input
                                                        type="date"
                                                        required
                                                        value={
                                                            data.fecha_check_in
                                                        }
                                                        onChange={(e) =>
                                                            setData(
                                                                'fecha_check_in',
                                                                e.target.value,
                                                            )
                                                        }
                                                        className="w-full rounded-2xl border border-border bg-background px-4 py-2.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-2 gap-3 border-t border-border/40 pt-3">
                                                <div>
                                                    <label className="mb-1 block text-[10px] font-black text-muted-foreground uppercase">
                                                        Hora Inicio
                                                    </label>
                                                    <input
                                                        type="time"
                                                        required
                                                        value={horaInicio}
                                                        onChange={(e) => {
                                                            const newInicio =
                                                                e.target.value;
                                                            setHoraInicio(
                                                                newInicio,
                                                            );
                                                            setData(
                                                                'hora_reserva',
                                                                `${newInicio} - ${horaFin}`,
                                                            );
                                                        }}
                                                        className="w-full rounded-2xl border border-border bg-background px-4 py-2.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                                    />
                                                </div>
                                                <div>
                                                    <label className="mb-1 block text-[10px] font-black text-muted-foreground uppercase">
                                                        Hora Fin
                                                    </label>
                                                    <input
                                                        type="time"
                                                        required
                                                        value={horaFin}
                                                        onChange={(e) => {
                                                            const newFin =
                                                                e.target.value;
                                                            setHoraFin(newFin);
                                                            setData(
                                                                'hora_reserva',
                                                                `${horaInicio} - ${newFin}`,
                                                            );
                                                        }}
                                                        className="w-full rounded-2xl border border-border bg-background px-4 py-2.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-2 gap-3 border-t border-border/40 pt-3">
                                                <div className="col-span-2">
                                                    <label className="mb-1 block text-[10px] font-black text-muted-foreground uppercase">
                                                        Asistentes
                                                    </label>
                                                    <input
                                                        type="number"
                                                        min="1"
                                                        max={space.capacidad}
                                                        value={data.adultos}
                                                        onChange={(e) =>
                                                            setData(
                                                                'adultos',
                                                                parseInt(
                                                                    e.target
                                                                        .value,
                                                                ),
                                                            )
                                                        }
                                                        className="w-full rounded-2xl border border-border bg-background px-4 py-2.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                                    />
                                                </div>
                                            </div>

                                            <div>
                                                <label className="mb-1 block text-[10px] font-black text-muted-foreground uppercase">
                                                    Notas especiales
                                                </label>
                                                <textarea
                                                    value={data.notas}
                                                    onChange={(e) =>
                                                        setData(
                                                            'notas',
                                                            e.target.value,
                                                        )
                                                    }
                                                    className="w-full rounded-2xl border border-border bg-background px-4 py-2.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                                    rows={2}
                                                    placeholder="Montaje, comida, refrescos, etc..."
                                                />
                                            </div>
                                        </div>

                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="mt-2 w-full cursor-pointer rounded-2xl bg-bugambilia-600 py-4 font-black text-white shadow-md transition-colors hover:bg-bugambilia-700"
                                        >
                                            <Calendar className="mr-2 h-4 w-4" />
                                            {processing
                                                ? 'Procesando...'
                                                : 'Confirmar Reserva'}
                                        </Button>
                                    </form>
                                ) : (
                                    <div className="rounded-2xl border border-dashed border-border bg-muted/40 py-4 text-center text-xs font-semibold text-muted-foreground">
                                        Este ambiente no requiere reserva
                                        anticipada. Puede visitarlo libremente
                                        durante sus horarios de apertura.
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Políticas del Espacio */}
            {politicas.length > 0 && (
                <section className="border-t border-border/40 bg-background py-12 font-sans md:py-16">
                    <div className="container mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                        <h2 className="mb-6 text-xl font-black tracking-tight text-foreground sm:text-2xl">
                            Políticas de la{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                Instalación
                            </span>
                        </h2>

                        <div className="grid gap-4 sm:grid-cols-3">
                            {politicas.map((pol, index) => (
                                <div
                                    key={index}
                                    className="rounded-2xl border border-border/80 bg-card p-5"
                                >
                                    <div className="mb-2 flex items-center gap-2">
                                        <AlertCircle className="h-4 w-4 shrink-0 text-bugambilia-600 dark:text-bugambilia-400" />
                                        <h3 className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            {pol.nombre}
                                        </h3>
                                    </div>
                                    {pol.descripcion && (
                                        <p className="text-xs leading-relaxed text-muted-foreground">
                                            {pol.descripcion}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Visor de Galería Modal Reutilizable */}
            <VisorGaleriaModal
                estaAbierto={isLightboxOpen}
                alCerrar={() => setIsLightboxOpen(false)}
                imagenes={imagenes}
                indiceImagenActiva={activeImageIndex}
                alSeleccionarImagen={(idx) => setActiveImageIndex(idx)}
                titulo={space?.nombre}
            />

            {/* Espacios Similares */}
            {similarSpaces.length > 0 && (
                <section className="border-t border-border/50 bg-card/60 py-16 font-sans">
                    <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                        <h2 className="mb-8 text-2xl font-black tracking-tight text-foreground">
                            Otros Ambientes{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                Disponibles
                            </span>
                        </h2>

                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {similarSpaces.map((sim) => (
                                <article
                                    key={sim.id}
                                    className="group shadow-airbnb hover:shadow-airbnb-hover overflow-hidden rounded-3xl border border-border/80 bg-background transition-all duration-300 hover:-translate-y-1"
                                >
                                    <Link
                                        href={`/espacios/${sim.id}`}
                                        className="relative block aspect-[4/3] overflow-hidden bg-muted/40"
                                    >
                                        <img
                                            src={sim.imagen}
                                            alt={sim.nombre}
                                            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        />
                                    </Link>
                                    <div className="p-5">
                                        <span className="mb-1 block text-[10px] font-extrabold tracking-wider text-bugambilia-600 uppercase">
                                            {sim.tipo}
                                        </span>
                                        <h3 className="mb-2 text-sm font-extrabold text-foreground transition-colors group-hover:text-bugambilia-600">
                                            {sim.nombre}
                                        </h3>
                                        <div className="flex items-center justify-between border-t border-border/40 pt-3">
                                            <span className="text-base font-black text-foreground">
                                                {sim.precio > 0
                                                    ? `${sim.moneda} ${numberFormat(sim.precio)}`
                                                    : 'Acceso Libre'}
                                            </span>
                                            <Link
                                                href={`/espacios/${sim.id}`}
                                                className="text-xs font-bold text-bugambilia-600 hover:underline"
                                            >
                                                Ver Detalles →
                                            </Link>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </LayoutPublico>
    );
}

function numberFormat(val: number | string): string {
    const num = typeof val === 'number' ? val : parseFloat(val);

    return isNaN(num)
        ? '0.00'
        : num.toLocaleString('es-NI', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
          });
}
