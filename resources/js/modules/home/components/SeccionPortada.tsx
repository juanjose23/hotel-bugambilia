import { router } from '@inertiajs/react';
import {
    Wifi,
    Car,
    UtensilsCrossed,
    Search,
    Calendar,
    Users,
    ShieldCheck,
    Minus,
    Plus,
    X,
    Award,
} from 'lucide-react';
import React, { useState, useEffect } from 'react';
import { PortadaHeroGeneral } from '@/modules/shared/components/PortadaHeroGeneral';

const FONDOS_HERO = [
    '/images/hero-main.webp',
    '/images/pool-front-view.webp',
    '/images/terrace.webp',
    '/images/group-room.webp',
];

interface SeccionPortadaProps {
    hotelInfo?: {
        nombre?: string;
        telefono?: string;
        email?: string;
        direccion?: string;
    };
}

const SeccionPortada = ({ hotelInfo }: SeccionPortadaProps) => {
    const hotelName = hotelInfo?.nombre || 'Hotel Bugambilias';
    const [currentBgIndex, setCurrentBgIndex] = useState(0);

    useEffect(() => {
        const interval = setInterval(() => {
            setCurrentBgIndex((prev) => (prev + 1) % FONDOS_HERO.length);
        }, 5500);

        return () => clearInterval(interval);
    }, []);

    const [fechaCheckIn, setFechaCheckIn] = useState(() => {
        return new Date().toISOString().split('T')[0];
    });
    const [fechaCheckOut, setFechaCheckOut] = useState(() => {
        return new Date(Date.now() + 86400000).toISOString().split('T')[0];
    });
    const [adultos, setAdultos] = useState(2);
    const [ninos, setNinos] = useState(0);
    const [activeModal, setActiveModal] = useState<
        'fechas' | 'huespedes' | null
    >(null);

    const handleSearchReserva = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/habitaciones', {
            check_in: fechaCheckIn,
            check_out: fechaCheckOut,
            adultos,
            ninos,
            huespedes: adultos + ninos,
        });
    };

    const formatearFechaVista = (isoStr: string) => {
        if (!isoStr) {
            return 'Seleccionar';
        }

        const parts = isoStr.split('-');

        if (parts.length < 3) {
            return isoStr;
        }

        const d = new Date(
            Number(parts[0]),
            Number(parts[1]) - 1,
            Number(parts[2]),
        );

        return d.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'short',
        });
    };

    return (
        <section className="relative font-sans">
            {/* Hero Principal con PortadaHeroGeneral Reutilizable */}
            <PortadaHeroGeneral
                imagenFondo={FONDOS_HERO[currentBgIndex]}
                carruselImagenes={FONDOS_HERO}
                indiceImagenActiva={currentBgIndex}
                alSeleccionarImagenCarrusel={(idx) => setCurrentBgIndex(idx)}
                alturaClass="h-[88vh] max-h-[900px] min-h-[640px]"
                badgeLabel={`${hotelName} • 5 Estrellas`}
                badgeIcon={Award}
                badgeStyle="border-amber-400/40 bg-amber-500/20 text-amber-300"
                titulo="Donde Estelí"
                tituloEnfasis="Florece con Elegancia"
                descripcion="Hospitalidad exclusiva de 5 estrellas con el encanto artesanal y la calidez auténtica de Nicaragua."
                acciones={
                    <div className="mx-auto mt-2 w-full max-w-4xl px-2 sm:px-4">
                        <form
                            onSubmit={handleSearchReserva}
                            className="hover:shadow-airbnb-hover flex flex-col items-center rounded-3xl border border-white/20 bg-card/95 p-2.5 shadow-2xl backdrop-blur-2xl transition-all duration-300 md:flex-row md:rounded-full dark:border-gray-800"
                        >
                            {/* Check-in / Check-out Selector Trigger */}
                            <div
                                onClick={() => setActiveModal('fechas')}
                                className="flex w-full cursor-pointer items-center gap-3.5 rounded-full px-6 py-3 text-left transition-all hover:bg-muted/60 md:w-1/3"
                            >
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-bugambilia-500/10">
                                    <Calendar className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                </div>
                                <div className="min-w-0">
                                    <span className="mb-0.5 block text-[10px] font-extrabold tracking-widest text-muted-foreground uppercase">
                                        Llegada / Salida
                                    </span>
                                    <span className="block truncate text-xs font-bold text-foreground">
                                        {formatearFechaVista(fechaCheckIn)} -{' '}
                                        {formatearFechaVista(fechaCheckOut)}
                                    </span>
                                </div>
                            </div>

                            <div className="hidden h-10 w-px bg-border/80 md:block" />

                            {/* Guests Selector Trigger */}
                            <div
                                onClick={() => setActiveModal('huespedes')}
                                className="flex w-full cursor-pointer items-center gap-3.5 rounded-full px-6 py-3 text-left transition-all hover:bg-muted/60 md:w-1/3"
                            >
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-bugambilia-500/10">
                                    <Users className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                                </div>
                                <div className="min-w-0">
                                    <span className="mb-0.5 block text-[10px] font-extrabold tracking-widest text-muted-foreground uppercase">
                                        Huéspedes
                                    </span>
                                    <span className="block truncate text-xs font-bold text-foreground">
                                        {adultos + ninos} Huésped
                                        {adultos + ninos > 1 ? 'es' : ''} (
                                        {adultos} ad / {ninos} niñ)
                                    </span>
                                </div>
                            </div>

                            <div className="hidden h-10 w-px bg-border/80 md:block" />

                            {/* Search CTA */}
                            <div className="flex w-full items-center justify-end p-1.5 md:w-1/3">
                                <button
                                    type="submit"
                                    className="shadow-airbnb flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-full bg-bugambilia-600 px-6 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase transition-all duration-300 hover:scale-105 hover:bg-bugambilia-700 active:scale-95"
                                >
                                    <Search className="h-4 w-4 stroke-[2.5]" />
                                    <span>Buscar Reserva</span>
                                </button>
                            </div>
                        </form>
                    </div>
                }
            />

            {/* MODAL OVERLAY 1: SELECCIÓN DE FECHAS */}
            {activeModal === 'fechas' && (
                <div
                    onClick={() => setActiveModal(null)}
                    className="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-md duration-200"
                >
                    <div
                        onClick={(e) => e.stopPropagation()}
                        className="w-full max-w-md space-y-4 rounded-3xl border border-border bg-card p-6 text-left font-sans shadow-2xl"
                    >
                        <div className="flex items-center justify-between border-b border-border/60 pb-3">
                            <h3 className="flex items-center gap-2 text-sm font-extrabold tracking-wider text-foreground uppercase">
                                <Calendar className="h-4 w-4 text-bugambilia-600" />
                                Seleccionar Fechas de Reserva
                            </h3>
                            <button
                                type="button"
                                onClick={() => setActiveModal(null)}
                                className="rounded-full p-1.5 text-muted-foreground transition hover:bg-muted"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-[10px] font-black text-muted-foreground uppercase">
                                    Fecha Check-In
                                </label>
                                <input
                                    type="date"
                                    min={fechaCheckIn}
                                    value={fechaCheckIn}
                                    onChange={(e) =>
                                        setFechaCheckIn(e.target.value)
                                    }
                                    className="w-full rounded-2xl border border-border bg-background px-3.5 py-2.5 text-xs font-semibold text-foreground focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="mb-1 block text-[10px] font-black text-muted-foreground uppercase">
                                    Fecha Check-Out
                                </label>
                                <input
                                    type="date"
                                    min={fechaCheckIn}
                                    value={fechaCheckOut}
                                    onChange={(e) =>
                                        setFechaCheckOut(e.target.value)
                                    }
                                    className="w-full rounded-2xl border border-border bg-background px-3.5 py-2.5 text-xs font-semibold text-foreground focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <div className="flex justify-end pt-2">
                            <button
                                type="button"
                                onClick={() => setActiveModal(null)}
                                className="cursor-pointer rounded-2xl bg-bugambilia-600 px-6 py-2.5 text-xs font-black text-white shadow-md hover:bg-bugambilia-700"
                            >
                                Aplicar Fechas
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL OVERLAY 2: SELECCIÓN DE HUÉSPEDES */}
            {activeModal === 'huespedes' && (
                <div
                    onClick={() => setActiveModal(null)}
                    className="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-md duration-200"
                >
                    <div
                        onClick={(e) => e.stopPropagation()}
                        className="w-full max-w-sm space-y-5 rounded-3xl border border-border bg-card p-6 text-left font-sans shadow-2xl"
                    >
                        <div className="flex items-center justify-between border-b border-border/60 pb-3">
                            <h3 className="flex items-center gap-2 text-sm font-extrabold tracking-wider text-foreground uppercase">
                                <Users className="h-4 w-4 text-bugambilia-600" />
                                Número de Huéspedes
                            </h3>
                            <button
                                type="button"
                                onClick={() => setActiveModal(null)}
                                className="rounded-full p-1.5 text-muted-foreground transition hover:bg-muted"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>

                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <span className="block text-xs font-bold text-foreground">
                                        Adultos
                                    </span>
                                    <span className="text-[10px] text-muted-foreground">
                                        Mayores de 12 años
                                    </span>
                                </div>
                                <div className="flex items-center gap-3">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setAdultos(Math.max(1, adultos - 1))
                                        }
                                        className="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-muted text-foreground transition hover:bg-muted/80"
                                    >
                                        <Minus className="h-4 w-4" />
                                    </button>
                                    <span className="w-5 text-center text-sm font-black text-foreground">
                                        {adultos}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => setAdultos(adultos + 1)}
                                        className="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-bugambilia-600 text-white transition hover:bg-bugambilia-700"
                                    >
                                        <Plus className="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <span className="block text-xs font-bold text-foreground">
                                        Niños
                                    </span>
                                    <span className="text-[10px] text-muted-foreground">
                                        Menores de 12 años
                                    </span>
                                </div>
                                <div className="flex items-center gap-3">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setNinos(Math.max(0, ninos - 1))
                                        }
                                        className="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-muted text-foreground transition hover:bg-muted/80"
                                    >
                                        <Minus className="h-4 w-4" />
                                    </button>
                                    <span className="w-5 text-center text-sm font-black text-foreground">
                                        {ninos}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => setNinos(ninos + 1)}
                                        className="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-bugambilia-600 text-white transition hover:bg-bugambilia-700"
                                    >
                                        <Plus className="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-end pt-2">
                            <button
                                type="button"
                                onClick={() => setActiveModal(null)}
                                className="cursor-pointer rounded-2xl bg-bugambilia-600 px-6 py-2.5 text-xs font-black text-white shadow-md hover:bg-bugambilia-700"
                            >
                                Listo
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Luxury Amenities Bar */}
            <div className="border-b border-border bg-card py-8 shadow-sm">
                <div className="container mx-auto px-4 sm:px-6">
                    <div className="grid grid-cols-2 gap-6 md:grid-cols-4 md:gap-8">
                        <div className="group flex items-center justify-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 transition-transform group-hover:scale-110">
                                <Wifi className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div className="text-left">
                                <span className="block text-xs font-black text-foreground">
                                    Fibra Óptica High-Speed
                                </span>
                                <span className="text-[10px] font-semibold text-muted-foreground">
                                    Internet de alta velocidad
                                </span>
                            </div>
                        </div>

                        <div className="group flex items-center justify-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 transition-transform group-hover:scale-110">
                                <Car className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div className="text-left">
                                <span className="block text-xs font-black text-foreground">
                                    Estac. Privado Gratuito
                                </span>
                                <span className="text-[10px] font-semibold text-muted-foreground">
                                    Vigilancia 24/7
                                </span>
                            </div>
                        </div>

                        <div className="group flex items-center justify-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 transition-transform group-hover:scale-110">
                                <UtensilsCrossed className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div className="text-left">
                                <span className="block text-xs font-black text-foreground">
                                    Restaurante Gourmet
                                </span>
                                <span className="text-[10px] font-semibold text-muted-foreground">
                                    Gastronomía de autor
                                </span>
                            </div>
                        </div>

                        <div className="group flex items-center justify-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-bugambilia-500/20 bg-bugambilia-500/10 transition-transform group-hover:scale-110">
                                <ShieldCheck className="h-5 w-5 text-bugambilia-600 dark:text-bugambilia-400" />
                            </div>
                            <div className="text-left">
                                <span className="block text-xs font-black text-foreground">
                                    Reserva Directa Garantizada
                                </span>
                                <span className="text-[10px] font-semibold text-muted-foreground">
                                    Mejor tarifa online
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default SeccionPortada;
