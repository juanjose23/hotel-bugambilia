import { Link, router, Head } from '@inertiajs/react';
import {
    Building2,
    MapPin,
    Users,
    Compass,
    Eye,
    CheckCircle2,
    X,
} from 'lucide-react';
import { useState } from 'react';
import { PortadaHeroGeneral } from '@/modules/shared/components/PortadaHeroGeneral';
import { Button } from '@/modules/shared/ui/boton';
import { Badge } from '@/modules/shared/ui/insignia';
import { Card } from '@/modules/shared/ui/tarjeta';
interface SubEspacioItem {
    id: number;
    codigo: string;
    slug: string;
    nombre: string;
    capacidad: number;
    reservable: boolean;
}
interface EspacioItem {
    id: number;
    codigo: string;
    slug?: string;
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
    sub_espacios?: SubEspacioItem[];
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
const Espacios = ({
    espacios = [],
    tipos = [],
    tipoSeleccionado = 'TODOS',
}: EspaciosPageProps) => {
    const [activeTipo, setActiveTipo] = useState(tipoSeleccionado);
    const [modalGaleria, setModalGaleria] = useState<{
        open: boolean;
        espacio?: EspacioItem;
    }>({ open: false });
    const [imgIndex, setImgIndex] = useState(0);
    const handleFilterTipo = (tipo: string) => {
        setActiveTipo(tipo);
        router.get(
            '/espacios',
            { tipo },
            { preserveState: true, preserveScroll: true },
        );
    };

    return (
        <>
            <Head>
                <title>Espacios & Ambientes — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Espacios para eventos, restaurante y áreas recreativas en Hotel Bugambilias Estelí. Diseñados para cenas inolvidables, conferencias y descanso."
                />
            </Head>
            {/* Hero Header Reutilizable */}
            <PortadaHeroGeneral
                imagenFondo="/images/terrace.webp"
                badgeLabel="Hotel Bugambilias"
                badgeIcon={Compass}
                badgeStyle="border-bugambilia-500/40 bg-bugambilia-500/20 text-bugambilia-300"
                titulo="Ambientes & Espacios"
                tituloEnfasis="Exclusivos"
                descripcion="Explore nuestras instalaciones diseñadas para eventos, cenas inolvidables, descanso junto a la piscina y experiencias únicas."
                alturaClass="py-16 md:py-24"
            />

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
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {espacios.map((espacio) => (
                                <Card
                                    key={espacio.id}
                                    className="group shadow-airbnb hover:shadow-airbnb-hover flex cursor-pointer flex-col justify-between overflow-hidden rounded-3xl border border-border/80 bg-card transition-all duration-300 hover:-translate-y-1"
                                >
                                    <div className="flex-grow">
                                        {/* Fotografía Compacta y Fina */}
                                        <div className="relative aspect-[16/9] overflow-hidden bg-muted/40">
                                            <img
                                                src={
                                                    espacio.imagenes[0] ||
                                                    '/images/terrace.webp'
                                                }
                                                alt={espacio.nombre}
                                                className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            />
                                            <div className="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-black/20" />

                                            <div className="absolute top-3 right-3 left-3 flex items-center justify-between">
                                                <Badge className="rounded-full bg-black/60 px-2.5 py-0.5 text-[10px] font-extrabold text-white uppercase backdrop-blur-md">
                                                    {espacio.tipo_label}
                                                </Badge>
                                                {espacio.reservable && (
                                                    <Badge className="flex items-center gap-1 rounded-full bg-emerald-600/90 px-2.5 py-0.5 text-[10px] font-extrabold text-white uppercase shadow-xs">
                                                        <CheckCircle2 className="h-3 w-3" />{' '}
                                                        Reservable
                                                    </Badge>
                                                )}
                                            </div>

                                            {espacio.imagenes.length > 1 && (
                                                <button
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        setImgIndex(0);
                                                        setModalGaleria({
                                                            open: true,
                                                            espacio,
                                                        });
                                                    }}
                                                    className="absolute right-3 bottom-3 flex cursor-pointer items-center gap-1 rounded-full bg-black/60 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-md transition hover:bg-black/80"
                                                >
                                                    <Eye className="h-3 w-3" />{' '}
                                                    ({espacio.imagenes.length})
                                                </button>
                                            )}
                                        </div>

                                        {/* Detalles Finos del Ambiente */}
                                        <div className="p-4 sm:p-5">
                                            <div className="mb-1 flex items-center gap-1.5 text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                                <MapPin className="h-3.5 w-3.5 shrink-0" />
                                                <span className="truncate">
                                                    {espacio.ubicacion}
                                                </span>
                                            </div>

                                            <Link
                                                href={`/espacios/${espacio.slug || espacio.id}`}
                                                prefetch="hover"
                                            >
                                                <h3 className="mb-1.5 text-base font-black text-foreground transition-colors group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                                                    {espacio.nombre}
                                                </h3>
                                            </Link>

                                            <p className="mb-3 line-clamp-2 text-xs leading-relaxed font-medium text-muted-foreground">
                                                {espacio.descripcion}
                                            </p>

                                            {/* Sub-ambientes / Sub-espacios Integrados */}
                                            {espacio.sub_espacios &&
                                                espacio.sub_espacios.length >
                                                    0 && (
                                                    <div className="mt-3 border-t border-border/50 pt-3">
                                                        <span className="mb-1.5 block flex items-center gap-1 text-[10px] font-extrabold tracking-wider text-muted-foreground uppercase">
                                                            <Building2 className="h-3 w-3 text-bugambilia-600 dark:text-bugambilia-400" />
                                                            Sub-ambientes
                                                            Incluidos (
                                                            {
                                                                espacio
                                                                    .sub_espacios
                                                                    .length
                                                            }
                                                            )
                                                        </span>
                                                        <div className="flex flex-wrap gap-1">
                                                            {espacio.sub_espacios
                                                                .slice(0, 4)
                                                                .map((sub) => (
                                                                    <Badge
                                                                        key={
                                                                            sub.id
                                                                        }
                                                                        variant="outline"
                                                                        className="border-border/60 bg-muted/40 px-2 py-0.5 text-[10px] font-semibold text-foreground"
                                                                    >
                                                                        {
                                                                            sub.nombre
                                                                        }
                                                                    </Badge>
                                                                ))}
                                                            {espacio
                                                                .sub_espacios
                                                                .length > 4 && (
                                                                <Badge
                                                                    variant="outline"
                                                                    className="border-bugambilia-500/20 bg-bugambilia-500/10 text-[10px] font-bold text-bugambilia-700 dark:text-bugambilia-300"
                                                                >
                                                                    +
                                                                    {espacio
                                                                        .sub_espacios
                                                                        .length -
                                                                        4}{' '}
                                                                    más
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    </div>
                                                )}

                                            <div className="mt-3 flex items-center justify-between gap-3 border-t border-border/50 pt-3 text-xs font-bold text-muted-foreground">
                                                <div className="flex items-center gap-1.5">
                                                    <Users className="h-3.5 w-3.5 text-bugambilia-600 dark:text-bugambilia-400" />
                                                    <span>
                                                        Hasta{' '}
                                                        {espacio.capacidad} pers
                                                    </span>
                                                </div>
                                                <div className="text-sm font-black text-foreground">
                                                    {espacio.precio &&
                                                    espacio.precio > 0
                                                        ? `${espacio.moneda} ${Number(espacio.precio).toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                                                        : 'Acceso Libre'}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Botón Ver Detalles */}
                                    <div className="p-4 pt-0">
                                        <Button
                                            className="w-full cursor-pointer rounded-2xl bg-bugambilia-600 py-4 text-xs font-black text-white shadow-xs transition-all hover:bg-bugambilia-700"
                                            asChild
                                        >
                                            <Link
                                                href={`/espacios/${espacio.slug || espacio.id}`}
                                                prefetch="hover"
                                            >
                                                <Eye className="mr-1.5 h-3.5 w-3.5" />
                                                {espacio.es_restaurante
                                                    ? 'Ver Detalles & Carta'
                                                    : 'Ver Detalles del Ambiente'}
                                            </Link>
                                        </Button>
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
        </>
    );
};
export default Espacios;
