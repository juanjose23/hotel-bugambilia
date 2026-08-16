import {
    Users,
    Eye,
    CheckCircle2,
    LayoutGrid,
    MapPin,
    BadgeCheck,
    Coffee,
} from 'lucide-react';
import { useState } from 'react';
import { VisorGaleriaModal } from '@/modulos/compartido/componentes/VisorGaleriaModal';
import type { AmbienteData } from '@/modulos/restaurante/types';
interface AmbientesRestauranteProps {
    ambientes: AmbienteData[];
    onSelectAmbienteReserva?: (ambienteNombre: string) => void;
}
const AmbientesRestaurante = ({
    ambientes,
    onSelectAmbienteReserva,
}: AmbientesRestauranteProps) => {
    const [selectedAmbienteId, setSelectedAmbienteId] = useState<number>(
        ambientes[0]?.id || 1,
    );
    const [modalGaleria, setModalGaleria] = useState<{
        abierto: boolean;
        imagenes: string[];
        indice: number;
        titulo: string;
    }>({
        abierto: false,
        imagenes: [],
        indice: 0,
        titulo: '',
    });

    if (!ambientes || ambientes.length === 0) {
        return null;
    }

    const activeAmbiente =
        ambientes.find((a) => a.id === selectedAmbienteId) || ambientes[0];
    const abrirGaleria = (imagenes: string[], titulo: string) => {
        setModalGaleria({
            abierto: true,
            imagenes,
            indice: 0,
            titulo,
        });
    };

    return (
        <section
            id="ambientes-section"
            className="border-y border-border/50 bg-muted/20 py-20"
        >
            <div className="container mx-auto max-w-6xl px-4">
                {/* Header */}
                <div className="mx-auto mb-14 max-w-2xl text-center">
                    <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-black tracking-widest text-amber-600 uppercase dark:text-amber-400">
                        <BadgeCheck className="h-3.5 w-3.5" />
                        Espacios & Atmósferas
                    </div>
                    <h2 className="mb-4 text-3xl font-black tracking-tight text-foreground md:text-5xl">
                        Ambientes del Restaurante
                    </h2>
                    <p className="text-base text-muted-foreground md:text-lg">
                        Elija la atmósfera perfecta para su experiencia
                        culinaria: desde la elegancia climatizada del salón
                        principal hasta la frescura tropical de nuestra terraza.
                    </p>
                </div>

                {/* Ambientes Tab Selector */}
                <div className="mb-10 flex scrollbar-none items-center justify-center gap-2 overflow-x-auto pb-4">
                    {ambientes.map((amb) => {
                        const isSelected = amb.id === selectedAmbienteId;

                        return (
                            <button
                                key={amb.id}
                                onClick={() => setSelectedAmbienteId(amb.id)}
                                className={`flex cursor-pointer items-center gap-2.5 rounded-2xl px-5 py-3.5 text-sm font-extrabold whitespace-nowrap transition-all ${
                                    isSelected
                                        ? 'scale-[1.02] bg-amber-500 text-zinc-950 shadow-lg shadow-amber-500/20'
                                        : 'border border-border bg-card text-foreground hover:border-amber-500/30 hover:bg-accent'
                                }`}
                            >
                                <Coffee
                                    className={`h-4 w-4 ${isSelected ? 'text-zinc-950' : 'text-amber-500'}`}
                                />
                                {amb.nombre}
                            </button>
                        );
                    })}
                </div>

                {/* Active Ambiente Display Card */}
                {activeAmbiente && (
                    <div className="grid items-stretch gap-8 overflow-hidden rounded-3xl border border-border/80 bg-card p-6 shadow-xl transition-all duration-300 md:p-10 lg:grid-cols-12">
                        {/* Gallery / Image Showcase Column */}
                        <div className="flex flex-col justify-between space-y-4 lg:col-span-7">
                            <div className="group relative h-72 w-full overflow-hidden rounded-2xl shadow-md sm:h-96">
                                <img
                                    src={
                                        activeAmbiente.imagenes[0] ||
                                        '/images/terrace.webp'
                                    }
                                    alt={activeAmbiente.nombre}
                                    className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                />
                                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />

                                {/* Open Gallery Overlay Button */}
                                <button
                                    onClick={() =>
                                        abrirGaleria(
                                            activeAmbiente.imagenes,
                                            activeAmbiente.nombre,
                                        )
                                    }
                                    className="absolute right-4 bottom-4 inline-flex cursor-pointer items-center gap-2 rounded-xl border border-white/20 bg-black/70 px-4 py-2.5 text-xs font-bold text-white shadow-lg backdrop-blur-md transition-all hover:bg-black/90"
                                >
                                    <Eye className="h-4 w-4 text-amber-400" />
                                    Ver {activeAmbiente.imagenes.length}{' '}
                                    {activeAmbiente.imagenes.length === 1
                                        ? 'foto'
                                        : 'fotos'}
                                </button>

                                <div className="absolute top-4 left-4">
                                    <span className="rounded-full bg-amber-500 px-3 py-1 text-xs font-black tracking-wider text-zinc-950 uppercase shadow">
                                        {activeAmbiente.zona.toUpperCase()}
                                    </span>
                                </div>
                            </div>

                            {/* Thumbnails grid */}
                            {activeAmbiente.imagenes.length > 1 && (
                                <div className="grid grid-cols-4 gap-3">
                                    {activeAmbiente.imagenes
                                        .slice(0, 4)
                                        .map((img, idx) => (
                                            <button
                                                key={idx}
                                                onClick={() =>
                                                    abrirGaleria(
                                                        activeAmbiente.imagenes,
                                                        activeAmbiente.nombre,
                                                    )
                                                }
                                                className="group relative h-20 cursor-pointer overflow-hidden rounded-xl border border-border transition-all hover:border-amber-400"
                                            >
                                                <img
                                                    src={img}
                                                    alt={`Preview ${idx + 1}`}
                                                    className="h-full w-full object-cover transition-transform group-hover:scale-110"
                                                />
                                            </button>
                                        ))}
                                </div>
                            )}
                        </div>

                        {/* Information Column */}
                        <div className="flex flex-col justify-between space-y-6 lg:col-span-5">
                            <div>
                                <div className="mb-3 flex items-center justify-between gap-2">
                                    <span className="text-xs font-black tracking-widest text-amber-600 uppercase dark:text-amber-400">
                                        Ambiente Configurable
                                    </span>
                                    <span className="inline-flex items-center gap-1 rounded-lg bg-muted px-2.5 py-1 text-xs font-bold text-muted-foreground">
                                        <Users className="h-3.5 w-3.5 text-amber-500" />
                                        Capacidad: {activeAmbiente.capacidad}{' '}
                                        pers.
                                    </span>
                                </div>

                                <h3 className="mb-3 text-2xl font-black tracking-tight text-foreground md:text-3xl">
                                    {activeAmbiente.nombre}
                                </h3>

                                <p className="mb-6 text-sm leading-relaxed text-muted-foreground md:text-base">
                                    {activeAmbiente.descripcion}
                                </p>

                                {/* Characteristics / Features */}
                                {activeAmbiente.caracteristicas &&
                                    activeAmbiente.caracteristicas.length >
                                        0 && (
                                        <div className="mb-6">
                                            <h4 className="mb-3 flex items-center gap-1.5 text-xs font-extrabold tracking-wider text-foreground uppercase">
                                                <BadgeCheck className="h-3.5 w-3.5 text-amber-500" />
                                                Comodidades del Ambiente
                                            </h4>
                                            <div className="grid grid-cols-2 gap-2.5">
                                                {activeAmbiente.caracteristicas.map(
                                                    (carac, i) => (
                                                        <div
                                                            key={i}
                                                            className="flex items-center gap-2 rounded-xl bg-muted/40 p-2.5 text-xs font-semibold text-foreground"
                                                        >
                                                            <CheckCircle2 className="h-4 w-4 shrink-0 text-emerald-500" />
                                                            <span>{carac}</span>
                                                        </div>
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                    )}

                                {/* Tables summary */}
                                <div className="flex items-center justify-between rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4">
                                    <div className="flex items-center gap-3">
                                        <LayoutGrid className="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                                        <div>
                                            <p className="text-xs font-extrabold tracking-wide text-foreground uppercase">
                                                Mesas Disponibles
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                Distribución para grupos y
                                                parejas
                                            </p>
                                        </div>
                                    </div>
                                    <span className="text-lg font-black text-amber-600 dark:text-amber-400">
                                        {activeAmbiente.mesas_count ||
                                            activeAmbiente.mesas?.length ||
                                            4}{' '}
                                        mesas
                                    </span>
                                </div>
                            </div>

                            {/* Reserve button */}
                            <button
                                onClick={() =>
                                    onSelectAmbienteReserva?.(
                                        activeAmbiente.nombre,
                                    )
                                }
                                className="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-amber-500 py-4 text-sm font-black text-zinc-950 shadow-lg shadow-amber-500/10 transition-all hover:scale-[1.01] hover:bg-amber-600"
                            >
                                <MapPin className="h-4 w-4" />
                                Reservar en {activeAmbiente.nombre}
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Gallery Modal Component */}
            <VisorGaleriaModal
                estaAbierto={modalGaleria.abierto}
                alCerrar={() =>
                    setModalGaleria((prev) => ({ ...prev, abierto: false }))
                }
                imagenes={modalGaleria.imagenes}
                indiceImagenActiva={modalGaleria.indice}
                alSeleccionarImagen={(idx) =>
                    setModalGaleria((prev) => ({ ...prev, indice: idx }))
                }
                titulo={modalGaleria.titulo}
            />
        </section>
    );
};
export default AmbientesRestaurante;
