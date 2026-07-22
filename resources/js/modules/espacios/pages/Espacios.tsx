import { Link, router, useForm, usePage } from '@inertiajs/react';
import {
    Building2,
    Calendar,
    MapPin,
    Users,
    UtensilsCrossed,
    Sparkles,
    Eye,
    CheckCircle2,
    ChevronRight,
    X,
} from 'lucide-react';
import { useState } from 'react';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';
import { Badge } from '@/modules/shared/ui/badge';
import { Button } from '@/modules/shared/ui/button';
import { Card } from '@/modules/shared/ui/card';

interface EspacioItem {
    id: number;
    codigo: string;
    nombre: string;
    tipo: string;
    tipo_label: string;
    capacidad: number;
    descripcion: string;
    precio: number;
    moneda: string;
    ubicacion: string;
    web: boolean;
    reservable: boolean;
    imagenes: string[];
    es_restaurante: boolean;
    meta_datos?: {
        metros_cuadrados?: number | string;
        equipamiento_incluido?: string[];
        tipo_cocina?: string;
        tipo_servicio?: string;
        horario_comida?: string;
        capacidad_mesas?: number | string;
        restricciones_gimnasio?: string;
        caracteristicas?: string[];
    };
}

interface TipoItem {
    tipo: string;
    label: string;
}

interface EspaciosPageProps {
    espacios: EspacioItem[];
    tipos: TipoItem[];
    tipoSeleccionado: string;
}

export default function Espacios({
    espacios = [],
    tipos = [],
    tipoSeleccionado = 'TODOS',
}: EspaciosPageProps) {
    const [activeTipo, setActiveTipo] = useState(tipoSeleccionado);
    const [modalGaleria, setModalGaleria] = useState<{
        open: boolean;
        espacio?: EspacioItem;
    }>({ open: false });
    const [imgIndex, setImgIndex] = useState(0);
    const [modalReserva, setModalReserva] = useState<{
        open: boolean;
        espacio?: EspacioItem;
    }>({ open: false });
    const [horaInicio, setHoraInicio] = useState('12:00');
    const [horaFin, setHoraFin] = useState('14:00');

    const { data, setData, post, processing, reset } = useForm({
        nombre_cliente: '',
        telefono_cliente: '',
        email_cliente: '',
        tipo_reserva: 'restaurante',
        espacio_id: '',
        fecha_check_in: new Date().toISOString().split('T')[0],
        hora_reserva: '19:00',
        adultos: 2,
        ninos: 0,
        notas: '',
    });

    const pageProps = usePage().props as any;
    const authUser = pageProps.auth?.user;

    const handleFilterTipo = (tipo: string) => {
        setActiveTipo(tipo);
        router.get(
            '/espacios',
            { tipo },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleAbrirReserva = (espacio: EspacioItem) => {
        if (!authUser) {
            router.visit('/login');

            return;
        }

        setData((prev) => ({
            ...prev,
            espacio_id: String(espacio.id),
            tipo_reserva: espacio.es_restaurante ? 'restaurante' : 'servicio',
            nombre_cliente: authUser.name || '',
            email_cliente: authUser.email || '',
            hora_reserva: espacio.es_restaurante ? '19:00' : '12:00 - 14:00',
        }));
        setModalReserva({ open: true, espacio });
    };

    const handleSubmitReserva = (e: React.FormEvent) => {
        e.preventDefault();
        post('/reservas', {
            onSuccess: () => {
                setModalReserva({ open: false });
                reset();
            },
        });
    };

    return (
        <LayoutPublico>
            {/* Hero Header */}
            <section className="relative overflow-hidden bg-gray-950 py-20 text-white md:py-28">
                <div
                    className="absolute inset-0 scale-105 bg-cover bg-center opacity-30"
                    style={{ backgroundImage: 'url("/images/terrace.jpg")' }}
                />
                <div className="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/70 to-transparent" />

                <div className="relative container mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                    <Badge className="mb-4 inline-flex items-center gap-2 rounded-full border-bugambilia-500/40 bg-bugambilia-500/20 px-4 py-1 text-xs font-bold tracking-widest text-bugambilia-300 uppercase">
                        <Sparkles className="h-3.5 w-3.5" />
                        Hotel Bugambilias
                    </Badge>
                    <h1 className="mb-4 text-4xl font-black tracking-tight sm:text-5xl md:text-6xl">
                        Ambientes & Espacios Exclusivos
                    </h1>
                    <p className="mx-auto max-w-2xl text-base leading-relaxed text-gray-300 sm:text-lg">
                        Explore nuestras instalaciones diseñadas para eventos,
                        cenas inolvidables, descanso junto a la piscina y
                        experiencias únicas.
                    </p>
                </div>
            </section>

            {/* Filter Tabs */}
            <section className="sticky top-16 z-30 border-b border-gray-100 bg-white bg-white/90 py-8 shadow-sm backdrop-blur-md dark:border-gray-800 dark:bg-gray-900 dark:bg-gray-900/90">
                <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex scrollbar-none items-center justify-start gap-2 overflow-x-auto pb-2 md:justify-center">
                        <button
                            onClick={() => handleFilterTipo('TODOS')}
                            className={`cursor-pointer rounded-full px-5 py-2.5 text-xs font-bold tracking-wide whitespace-nowrap transition-all ${
                                activeTipo === 'TODOS'
                                    ? 'bg-bugambilia-600 text-white shadow-md shadow-bugambilia-500/20'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                            }`}
                        >
                            Todos los Espacios ({espacios.length})
                        </button>
                        {tipos.map((t) => (
                            <button
                                key={t.tipo}
                                onClick={() => handleFilterTipo(t.tipo)}
                                className={`cursor-pointer rounded-full px-5 py-2.5 text-xs font-bold tracking-wide whitespace-nowrap transition-all ${
                                    activeTipo === t.tipo
                                        ? 'bg-bugambilia-600 text-white shadow-md shadow-bugambilia-500/20'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                                }`}
                            >
                                {t.label}
                            </button>
                        ))}
                    </div>
                </div>
            </section>

            {/* Grid of Espacios */}
            <section className="min-h-screen bg-gray-50 py-16 dark:bg-gray-950">
                <div className="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {espacios.length === 0 ? (
                        <div className="mx-auto max-w-lg rounded-3xl border border-gray-200 bg-white p-8 py-20 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <Building2 className="mx-auto mb-4 h-14 w-14 text-gray-400" />
                            <h3 className="mb-2 text-xl font-bold text-gray-900 dark:text-white">
                                No hay espacios disponibles
                            </h3>
                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                No se encontraron espacios configurados para el
                                filtro seleccionado.
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                            {espacios.map((espacio) => (
                                <Card
                                    key={espacio.id}
                                    onClick={(e) => {
                                        // Evitar redirigir si se hace clic en botones o galería
                                        const target = e.target as HTMLElement;

                                        if (
                                            target.closest('button') ||
                                            target.closest('a')
                                        ) {
                                            return;
                                        }

                                        if (espacio.es_restaurante) {
                                            router.visit('/restaurante');
                                        } else {
                                            router.visit(
                                                `/espacios/${espacio.id}`,
                                            );
                                        }
                                    }}
                                    className="group flex cursor-pointer flex-col justify-between overflow-hidden rounded-3xl border-gray-200/80 bg-white shadow-sm transition-all duration-300 hover:shadow-xl dark:border-gray-800 dark:bg-gray-900"
                                >
                                    <div className="flex-grow">
                                        {/* Image Header with Zoom */}
                                        <div className="relative h-64 overflow-hidden bg-gray-900">
                                            <img
                                                src={
                                                    espacio.imagenes[0] ||
                                                    '/images/terrace.jpg'
                                                }
                                                alt={espacio.nombre}
                                                className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                            />
                                            <div className="absolute inset-0 bg-gradient-to-t from-gray-950/80 via-transparent to-black/20" />

                                            <div className="absolute top-4 right-4 left-4 flex items-center justify-between">
                                                <Badge className="rounded-full bg-white/90 px-3 py-1 text-[10px] font-extrabold text-gray-900 uppercase shadow backdrop-blur-md dark:bg-gray-900/90 dark:text-white">
                                                    {espacio.tipo_label}
                                                </Badge>
                                                {espacio.reservable && (
                                                    <Badge className="flex items-center gap-1 rounded-full bg-emerald-500/90 px-3 py-1 text-[10px] font-extrabold text-white uppercase shadow">
                                                        <CheckCircle2 className="h-3 w-3" />{' '}
                                                        Reservable
                                                    </Badge>
                                                )}
                                            </div>

                                            {espacio.imagenes.length > 1 && (
                                                <button
                                                    onClick={() => {
                                                        setImgIndex(0);
                                                        setModalGaleria({
                                                            open: true,
                                                            espacio,
                                                        });
                                                    }}
                                                    className="absolute right-4 bottom-4 flex cursor-pointer items-center gap-1.5 rounded-full bg-black/60 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-md transition hover:bg-black/80"
                                                >
                                                    <Eye className="h-3.5 w-3.5" />
                                                    Galería (
                                                    {espacio.imagenes.length})
                                                </button>
                                            )}
                                        </div>

                                        {/* Content */}
                                        <div className="p-6">
                                            <div className="mb-1 flex items-center gap-2 text-xs font-bold text-amber-600 dark:text-amber-400">
                                                <MapPin className="h-3.5 w-3.5" />
                                                <span>{espacio.ubicacion}</span>
                                            </div>
                                            <h3 className="mb-2 text-xl font-extrabold text-gray-900 transition-colors group-hover:text-bugambilia-600 dark:text-white dark:group-hover:text-bugambilia-400">
                                                {espacio.nombre}
                                            </h3>
                                            <p className="mb-4 line-clamp-3 text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                                                {espacio.descripcion}
                                            </p>

                                            {/* Metadatos dinámicos específicos */}
                                            {!espacio.es_restaurante &&
                                                espacio.meta_datos && (
                                                    <div className="mb-4 flex flex-wrap gap-1.5">
                                                        {espacio.meta_datos
                                                            .metros_cuadrados && (
                                                            <Badge
                                                                variant="outline"
                                                                className="border-gray-200 px-2.5 py-0.5 text-[10px] font-bold text-amber-600 dark:border-gray-800 dark:text-amber-400"
                                                            >
                                                                {
                                                                    espacio
                                                                        .meta_datos
                                                                        .metros_cuadrados
                                                                }{' '}
                                                                m²
                                                            </Badge>
                                                        )}
                                                        {espacio.meta_datos.equipamiento_incluido?.map(
                                                            (eq) => (
                                                                <Badge
                                                                    key={eq}
                                                                    variant="outline"
                                                                    className="border-gray-100 bg-gray-50 px-2.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:border-gray-800 dark:bg-gray-800/30 dark:text-gray-400"
                                                                >
                                                                    {eq ===
                                                                    'proyector'
                                                                        ? 'Proyector HD'
                                                                        : eq ===
                                                                            'sonido'
                                                                          ? 'Audio & Consola'
                                                                          : eq ===
                                                                              'clima'
                                                                            ? 'Climatizado'
                                                                            : eq ===
                                                                                'pizarra'
                                                                              ? 'Pizarra'
                                                                              : eq ===
                                                                                  'luces'
                                                                                ? 'Iluminación Dimmer'
                                                                                : eq}
                                                                </Badge>
                                                            ),
                                                        )}
                                                        {espacio.meta_datos.caracteristicas?.map(
                                                            (carac) => (
                                                                <Badge
                                                                    key={carac}
                                                                    variant="outline"
                                                                    className="border-gray-100 bg-gray-50 px-2.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:border-gray-800 dark:bg-gray-800/30 dark:text-gray-400"
                                                                >
                                                                    {carac}
                                                                </Badge>
                                                            ),
                                                        )}
                                                    </div>
                                                )}

                                            <div className="flex items-center justify-between gap-4 border-t border-gray-100 py-3 text-xs font-bold text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                                <div className="flex items-center gap-1.5">
                                                    <Users className="h-4 w-4 text-bugambilia-500" />
                                                    <span>
                                                        Hasta{' '}
                                                        {espacio.capacidad}{' '}
                                                        personas
                                                    </span>
                                                </div>
                                                <div className="text-sm font-black text-gray-900 dark:text-white">
                                                    {espacio.precio &&
                                                    espacio.precio > 0
                                                        ? `${espacio.moneda} ${Number(espacio.precio).toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                                                        : 'Acceso Libre'}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Actions footer */}
                                    <div className="flex items-center gap-3 p-6 pt-0">
                                        {espacio.es_restaurante ? (
                                            <Button
                                                className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 py-5 text-xs font-black text-white shadow-md hover:from-amber-600 hover:to-amber-700"
                                                asChild
                                            >
                                                <Link href="/restaurante">
                                                    <UtensilsCrossed className="h-4 w-4" />
                                                    Ver Carta & Restaurante
                                                    <ChevronRight className="ml-auto h-4 w-4" />
                                                </Link>
                                            </Button>
                                        ) : espacio.reservable ? (
                                            <Button
                                                onClick={() =>
                                                    handleAbrirReserva(espacio)
                                                }
                                                className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-bugambilia-600 py-5 text-xs font-black text-white shadow-md hover:bg-bugambilia-700"
                                            >
                                                <Calendar className="h-4 w-4" />
                                                Reservar Espacio
                                                <ChevronRight className="ml-auto h-4 w-4" />
                                            </Button>
                                        ) : (
                                            <Button
                                                variant="outline"
                                                className="w-full cursor-default rounded-2xl border-gray-300 py-5 text-xs font-bold text-gray-700 opacity-80 dark:border-gray-700 dark:text-gray-300"
                                            >
                                                Espacio de Libre Acceso
                                            </Button>
                                        )}
                                    </div>
                                </Card>
                            ))}
                        </div>
                    )}
                </div>
            </section>

            {/* Gallery Modal */}
            {modalGaleria.open && modalGaleria.espacio && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-md">
                    <div className="relative w-full max-w-4xl overflow-hidden rounded-3xl border border-gray-800 bg-gray-950 text-white">
                        <button
                            onClick={() => setModalGaleria({ open: false })}
                            className="absolute top-4 right-4 z-10 cursor-pointer rounded-full bg-white/10 p-2 hover:bg-white/20"
                        >
                            <X className="h-6 w-6 text-white" />
                        </button>
                        <div className="relative flex h-[450px] items-center justify-center bg-black">
                            <img
                                src={modalGaleria.espacio.imagenes[imgIndex]}
                                alt={modalGaleria.espacio.nombre}
                                className="max-h-full max-w-full object-contain"
                            />
                        </div>
                        <div className="flex items-center justify-between bg-gray-900 p-6">
                            <div>
                                <h4 className="text-lg font-bold">
                                    {modalGaleria.espacio.nombre}
                                </h4>
                                <p className="text-xs text-gray-400">
                                    Foto {imgIndex + 1} de{' '}
                                    {modalGaleria.espacio.imagenes.length}
                                </p>
                            </div>
                            <div className="flex gap-2">
                                {modalGaleria.espacio.imagenes.map((_, i) => (
                                    <button
                                        key={i}
                                        onClick={() => setImgIndex(i)}
                                        className={`h-3 w-3 rounded-full ${imgIndex === i ? 'bg-bugambilia-500' : 'bg-gray-700'}`}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Booking Modal */}
            {modalReserva.open && modalReserva.espacio && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
                    <div className="relative w-full max-w-md rounded-3xl border border-gray-200 bg-white p-6 text-gray-900 shadow-2xl dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        <button
                            onClick={() => setModalReserva({ open: false })}
                            className="absolute top-4 right-4 cursor-pointer p-2 text-gray-400 hover:text-gray-600 dark:hover:text-white"
                        >
                            <X className="h-5 w-5" />
                        </button>

                        <h3 className="mb-1 text-xl font-black">
                            Solicitar Reserva de Espacio
                        </h3>
                        <p className="mb-4 text-xs text-gray-500 dark:text-gray-400">
                            Espacio:{' '}
                            <span className="font-bold text-bugambilia-600">
                                {modalReserva.espacio.nombre}
                            </span>
                        </p>

                        <form
                            onSubmit={handleSubmitReserva}
                            className="space-y-4"
                        >
                            <div>
                                <label className="mb-1 block text-xs font-bold">
                                    Nombre Completo
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
                                    className="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-semibold dark:border-gray-700 dark:bg-gray-800"
                                    placeholder="Ej. Juan Pérez"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="mb-1 block text-xs font-bold">
                                        Teléfono
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={data.telefono_cliente}
                                        onChange={(e) =>
                                            setData(
                                                'telefono_cliente',
                                                e.target.value,
                                            )
                                        }
                                        className="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-semibold dark:border-gray-700 dark:bg-gray-800"
                                        placeholder="+505 8888 8888"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-xs font-bold">
                                        Fecha
                                    </label>
                                    <input
                                        type="date"
                                        required
                                        value={data.fecha_check_in}
                                        onChange={(e) =>
                                            setData(
                                                'fecha_check_in',
                                                e.target.value,
                                            )
                                        }
                                        className="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-semibold dark:border-gray-700 dark:bg-gray-800"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                {modalReserva.espacio.es_restaurante ? (
                                    <div>
                                        <label className="mb-1 block text-xs font-bold">
                                            Hora Estimada
                                        </label>
                                        <input
                                            type="time"
                                            value={data.hora_reserva}
                                            onChange={(e) =>
                                                setData(
                                                    'hora_reserva',
                                                    e.target.value,
                                                )
                                            }
                                            className="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-semibold dark:border-gray-700 dark:bg-gray-800"
                                        />
                                    </div>
                                ) : (
                                    <div className="col-span-2 grid grid-cols-2 gap-3">
                                        <div>
                                            <label className="mb-1 block text-xs font-bold">
                                                Hora Inicio
                                            </label>
                                            <input
                                                type="time"
                                                value={horaInicio}
                                                onChange={(e) => {
                                                    const newInicio =
                                                        e.target.value;
                                                    setHoraInicio(newInicio);
                                                    setData(
                                                        'hora_reserva',
                                                        `${newInicio} - ${horaFin}`,
                                                    );
                                                }}
                                                className="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-semibold dark:border-gray-700 dark:bg-gray-800"
                                            />
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-xs font-bold">
                                                Hora Fin
                                            </label>
                                            <input
                                                type="time"
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
                                                className="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-semibold dark:border-gray-700 dark:bg-gray-800"
                                            />
                                        </div>
                                    </div>
                                )}

                                <div className="col-span-2">
                                    <label className="mb-1 block text-xs font-bold">
                                        Cantidad de Personas
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max={modalReserva.espacio.capacidad}
                                        value={data.adultos}
                                        onChange={(e) =>
                                            setData(
                                                'adultos',
                                                parseInt(e.target.value),
                                            )
                                        }
                                        className="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-semibold dark:border-gray-700 dark:bg-gray-800"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="mb-1 block text-xs font-bold">
                                    Notas / Requerimientos
                                </label>
                                <textarea
                                    rows={2}
                                    value={data.notas}
                                    onChange={(e) =>
                                        setData('notas', e.target.value)
                                    }
                                    className="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-2 text-xs dark:border-gray-700 dark:bg-gray-800"
                                    placeholder="Ej. Decoración especial, mesa cerca de la ventana..."
                                />
                            </div>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="w-full cursor-pointer rounded-2xl bg-bugambilia-600 py-3 text-xs font-bold text-white hover:bg-bugambilia-700"
                            >
                                Confirmar Solicitud de Reserva
                            </Button>
                        </form>
                    </div>
                </div>
            )}
        </LayoutPublico>
    );
}
