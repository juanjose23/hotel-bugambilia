import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    BedDouble,
    MapPin,
    Minus,
    Plus,
    ShieldCheck,
    Sparkles,
} from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { IndicadorPasosReserva } from '@/modules/reservations/components/IndicadorPasosReserva';
import { ListadoErroresFormulario } from '@/modules/reservations/components/ListadoErroresFormulario';
import { NavegacionReserva } from '@/modules/reservations/components/NavegacionReserva';
import { SeccionesAdicionalesReserva } from '@/modules/reservations/components/SeccionesAdicionalesReserva';
import { SolicitudCuentaEstancia } from '@/modules/reservations/components/SolicitudCuentaEstancia';
import { usePasosReserva } from '@/modules/reservations/hooks/usePasosReserva';
import { useAlmacenReserva } from '@/modules/reservations/stores/useAlmacenReserva';
import type { DatosBorradorHabitacion } from '@/modules/reservations/types/borradorReserva';
import type { PropiedadesReservarHabitacion } from '@/modules/rooms/types/reservaHabitacion';
import { useAutenticacion } from '@/modules/shared/hooks/useAutenticacion';
import { Badge } from '@/modules/shared/ui/insignia';
import { formatearNumero } from '@/modules/shared/utils/formato';
const HabitacionReservar = ({
    room,
    opcionesReserva,
}: PropiedadesReservarHabitacion) => {
    const auth = useAutenticacion();
    const authUser = auth?.user;
    const borradorGuardado = useAlmacenReserva((estado) => estado.borrador);
    const guardarBorrador = useAlmacenReserva(
        (estado) => estado.guardarBorrador,
    );
    const limpiarBorrador = useAlmacenReserva(
        (estado) => estado.limpiarBorrador,
    );
    const borradorHabitacion =
        borradorGuardado?.tipo === 'habitacion' &&
        borradorGuardado.datos.habitacion_id === String(room.id)
            ? borradorGuardado
            : null;
    const { pasoActual, avanzar, retroceder, irAlPaso } = usePasosReserva({
        totalPasos: 4,
        pasoInicial: borradorHabitacion?.pasoActual,
    });
    const [fechaCheckIn] = useState(() => {
        return new Date().toISOString().split('T')[0];
    });
    const [fechaCheckOut] = useState(() => {
        return new Date(Date.now() + 86400000).toISOString().split('T')[0];
    });
    const hoy = new Date().toISOString().split('T')[0];
    const datosIniciales: DatosBorradorHabitacion = {
        telefono_cliente: '',
        tipo_reserva: 'habitacion' as const,
        habitacion_id: String(room?.id || ''),
        fecha_check_in: fechaCheckIn,
        fecha_check_out: fechaCheckOut,
        adultos: room?.adultos || 2,
        ninos: room?.ninos || 0,
        notas: '',
        servicios_adicionales: [] as Array<{
            servicio_id: number;
            cantidad: number;
        }>,
        espacios_adicionales: [] as Array<{
            espacio_id: number;
            cantidad: number;
        }>,
        promocion_id: null as number | null,
        solicita_cuenta: false,
        limite_cuenta_solicitado: null as number | null,
        ...borradorHabitacion?.datos,
        nombre_cliente:
            borradorHabitacion?.datos.nombre_cliente || authUser?.name || '',
        email_cliente:
            borradorHabitacion?.datos.email_cliente || authUser?.email || '',
    };
    const { data, setData, post, processing, errors } =
        useForm<DatosBorradorHabitacion>(datosIniciales);
    const [promoAplicada] = useState<string | null>(() => {
        if (typeof window !== 'undefined') {
            const params = new URLSearchParams(window.location.search);

            return params.get('promo') || params.get('codigo_promocional');
        }

        return null;
    });

    useEffect(() => {
        guardarBorrador({
            tipo: 'habitacion',
            rutaRetorno: `/habitaciones/${room.slug}/reservar`,
            pasoActual,
            datos: data,
        });
    }, [data, guardarBorrador, pasoActual, room.slug]);
    const imagenPrincipal =
        room?.imagenes && room.imagenes.length > 0
            ? room.imagenes[0]
            : '/images/main-room.webp';
    const calcularNoches = () => {
        if (!data.fecha_check_in || !data.fecha_check_out) {
            return 1;
        }

        const d1 = new Date(data.fecha_check_in);
        const d2 = new Date(data.fecha_check_out);
        const diffMs = d2.getTime() - d1.getTime();
        const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

        return Math.max(1, diffDays);
    };
    const nochesCalculadas = calcularNoches();
    const subtotalEstimado = (room?.precio || 0) * nochesCalculadas;
    const steps = [
        { id: 1, titulo: 'Fechas & Titular' },
        { id: 2, titulo: 'Huéspedes' },
        { id: 3, titulo: 'Adicionales' },
        { id: 4, titulo: 'Confirmación' },
    ];
    const handleNextStep = (e: React.FormEvent) => {
        e.preventDefault();

        if (pasoActual === 1) {
            if (!data.nombre_cliente.trim() || !data.telefono_cliente.trim()) {
                alert('Por favor ingrese su nombre y teléfono para continuar.');

                return;
            }
        }

        if (pasoActual < 4) {
            avanzar();
        } else {
            post('/reservas', { onSuccess: limpiarBorrador });
        }
    };

    return (
        <>
            <Head title={`Reservar ${room.nombre} - Hotel Bugambilias`} />

            <div className="min-h-screen bg-background pt-4 pb-28 font-sans md:pt-8 lg:pb-16">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    {/* Botón de Retorno Móvil */}
                    <div className="mb-4 flex items-center justify-between">
                        <Link
                            href={`/habitaciones/${room.slug}`}
                            className="inline-flex items-center text-xs font-bold text-bugambilia-600 hover:underline dark:text-bugambilia-400"
                        >
                            <ArrowLeft className="mr-1.5 h-4 w-4" />
                            Volver a la Habitación {room.numero}
                        </Link>
                        <span className="text-[11px] font-extrabold tracking-widest text-muted-foreground uppercase">
                            Paso {pasoActual} de 4
                        </span>
                    </div>

                    {/* Encabezado Botánico Fino Hotel Bugambilias */}
                    <div className="mb-6 flex items-center gap-4 rounded-3xl border border-border bg-card p-4 shadow-xs md:p-6">
                        <img
                            src={imagenPrincipal}
                            alt={room.nombre}
                            className="h-20 w-20 shrink-0 rounded-2xl object-cover shadow-sm md:h-24 md:w-28"
                        />
                        <div className="min-w-0 flex-1">
                            <div className="mb-1 flex flex-wrap items-center gap-2">
                                <Badge className="bg-bugambilia-500/10 text-[10px] font-extrabold text-bugambilia-700 dark:text-bugambilia-300">
                                    {room.categoria}
                                </Badge>
                                <Badge className="bg-emerald-500/10 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                    Habitación {room.numero}
                                </Badge>
                            </div>
                            <h1 className="truncate text-lg font-black text-foreground md:text-2xl">
                                {room.nombre}
                            </h1>
                            <p className="mt-0.5 flex items-center gap-3 text-xs text-muted-foreground">
                                <span className="inline-flex items-center">
                                    <MapPin className="mr-1 h-3.5 w-3.5 shrink-0 text-bugambilia-600" />
                                    {room.ubicacion}
                                </span>
                                <span className="inline-flex items-center">
                                    <BedDouble className="mr-1 h-3.5 w-3.5 shrink-0 text-bugambilia-600" />
                                    {room.camas}
                                </span>
                            </p>
                        </div>
                    </div>
                    {promoAplicada && (
                        <div className="mb-6 flex items-center justify-between rounded-2xl border border-amber-500/40 bg-amber-500/10 p-4 font-sans text-xs font-bold text-amber-700 dark:text-amber-300">
                            <div className="flex items-center gap-2.5">
                                <Sparkles className="h-4 w-4 shrink-0 text-amber-500" />
                                <span>
                                    ¡Promoción <strong>{promoAplicada}</strong>{' '}
                                    aplicada correctamente a su reserva!
                                </span>
                            </div>
                            <span className="rounded-full bg-amber-500/20 px-3 py-1 text-[10px] font-black text-amber-600 uppercase dark:text-amber-400">
                                Descuento Activo
                            </span>
                        </div>
                    )}

                    <IndicadorPasosReserva
                        pasoActual={pasoActual}
                        pasos={steps}
                        alSeleccionarPaso={irAlPaso}
                    />

                    {/* Formulario de reserva */}
                    <form onSubmit={handleNextStep} className="space-y-6">
                        {/* PASO 1: Fechas y Datos del Titular */}
                        {pasoActual === 1 && (
                            <div className="animate-in fade-in-50 space-y-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
                                <div>
                                    <h2 className="text-lg font-black text-foreground md:text-xl">
                                        Fechas de Estancia &{' '}
                                        <span className="font-serif font-normal text-bugambilia-600 italic">
                                            Titular
                                        </span>
                                    </h2>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        Seleccione los días de su visita y los
                                        datos de quien coordina la reserva
                                    </p>
                                </div>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label className="mb-1 block text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                            Fecha de Check-In *
                                        </label>
                                        <input
                                            type="date"
                                            required
                                            min={hoy}
                                            value={data.fecha_check_in}
                                            onChange={(e) =>
                                                setData(
                                                    'fecha_check_in',
                                                    e.target.value,
                                                )
                                            }
                                            className="w-full rounded-2xl border border-border bg-background px-4 py-3.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                        />
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                            Fecha de Check-Out *
                                        </label>
                                        <input
                                            type="date"
                                            required
                                            min={data.fecha_check_in || hoy}
                                            value={data.fecha_check_out}
                                            onChange={(e) =>
                                                setData(
                                                    'fecha_check_out',
                                                    e.target.value,
                                                )
                                            }
                                            className="w-full rounded-2xl border border-border bg-background px-4 py-3.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                        />
                                    </div>
                                </div>

                                <div className="flex items-center justify-between rounded-2xl bg-muted/50 p-3.5 text-xs font-medium">
                                    <span className="text-muted-foreground">
                                        Duración de la Estancia:
                                    </span>
                                    <span className="rounded-xl bg-background px-3 py-1 font-extrabold text-foreground shadow-xs">
                                        {nochesCalculadas} noche(s)
                                    </span>
                                </div>

                                <div className="space-y-4 border-t border-border/40 pt-4">
                                    <div>
                                        <label className="mb-1 block text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                            Nombre Completo del Titular *
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
                                            className="w-full rounded-2xl border border-border bg-background px-4 py-3.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                            placeholder="Ej. Ana María Rodríguez"
                                        />
                                    </div>

                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label className="mb-1 block text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                Teléfono Móvil *
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
                                                className="w-full rounded-2xl border border-border bg-background px-4 py-3.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                                placeholder="+505 8888 8888"
                                            />
                                        </div>

                                        <div>
                                            <label className="mb-1 block text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                Correo Electrónico
                                            </label>
                                            <input
                                                type="email"
                                                value={data.email_cliente}
                                                onChange={(e) =>
                                                    setData(
                                                        'email_cliente',
                                                        e.target.value,
                                                    )
                                                }
                                                className="w-full rounded-2xl border border-border bg-background px-4 py-3.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                                placeholder="correo@ejemplo.com"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* PASO 2: Selección de Huéspedes */}
                        {pasoActual === 2 && (
                            <div className="animate-in fade-in-50 space-y-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
                                <div>
                                    <h2 className="text-lg font-black text-foreground md:text-xl">
                                        Huéspedes &{' '}
                                        <span className="font-serif font-normal text-bugambilia-600 italic">
                                            Acompañantes
                                        </span>
                                    </h2>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        Capacidad máxima sugerida:{' '}
                                        {room.capacidad} personas
                                    </p>
                                </div>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div className="flex items-center justify-between rounded-2xl border border-border bg-background p-4">
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
                                                    setData(
                                                        'adultos',
                                                        Math.max(
                                                            1,
                                                            data.adultos - 1,
                                                        ),
                                                    )
                                                }
                                                className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-muted text-foreground transition hover:bg-muted/80 active:scale-95"
                                            >
                                                <Minus className="h-4 w-4" />
                                            </button>
                                            <span className="w-6 text-center text-sm font-black text-foreground">
                                                {data.adultos}
                                            </span>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setData(
                                                        'adultos',
                                                        Math.min(
                                                            room.capacidad,
                                                            data.adultos + 1,
                                                        ),
                                                    )
                                                }
                                                className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-bugambilia-600 text-white transition hover:bg-bugambilia-700 active:scale-95"
                                            >
                                                <Plus className="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-between rounded-2xl border border-border bg-background p-4">
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
                                                    setData(
                                                        'ninos',
                                                        Math.max(
                                                            0,
                                                            data.ninos - 1,
                                                        ),
                                                    )
                                                }
                                                className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-muted text-foreground transition hover:bg-muted/80 active:scale-95"
                                            >
                                                <Minus className="h-4 w-4" />
                                            </button>
                                            <span className="w-6 text-center text-sm font-black text-foreground">
                                                {data.ninos}
                                            </span>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setData(
                                                        'ninos',
                                                        data.ninos + 1,
                                                    )
                                                }
                                                className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-bugambilia-600 text-white transition hover:bg-bugambilia-700 active:scale-95"
                                            >
                                                <Plus className="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* PASO 3: Complementos & Adicionales */}
                        {pasoActual === 3 && (
                            <div className="animate-in fade-in-50 space-y-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
                                <div>
                                    <h2 className="text-lg font-black text-foreground md:text-xl">
                                        Complementos &{' '}
                                        <span className="font-serif font-normal text-bugambilia-600 italic">
                                            Experiencias
                                        </span>
                                    </h2>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        Personalice su estancia con servicios y
                                        promociones exclusivas
                                    </p>
                                </div>

                                <SeccionesAdicionalesReserva
                                    opciones={opcionesReserva}
                                    serviciosSeleccionados={data.servicios_adicionales.map(
                                        (i) => i.servicio_id,
                                    )}
                                    espaciosSeleccionados={data.espacios_adicionales.map(
                                        (i) => i.espacio_id,
                                    )}
                                    promocionId={data.promocion_id}
                                    onServiciosChange={(ids) =>
                                        setData(
                                            'servicios_adicionales',
                                            ids.map((servicio_id) => ({
                                                servicio_id,
                                                cantidad: 1,
                                            })),
                                        )
                                    }
                                    onEspaciosChange={(ids) =>
                                        setData(
                                            'espacios_adicionales',
                                            ids.map((espacio_id) => ({
                                                espacio_id,
                                                cantidad: 1,
                                            })),
                                        )
                                    }
                                    onPromocionChange={(id) =>
                                        setData('promocion_id', id)
                                    }
                                />

                                <SolicitudCuentaEstancia
                                    solicitada={data.solicita_cuenta}
                                    limite={data.limite_cuenta_solicitado}
                                    alCambiarSolicitud={(solicitada) => {
                                        setData('solicita_cuenta', solicitada);

                                        if (!solicitada) {
                                            setData(
                                                'limite_cuenta_solicitado',
                                                null,
                                            );
                                        }
                                    }}
                                    alCambiarLimite={(limite) =>
                                        setData(
                                            'limite_cuenta_solicitado',
                                            limite,
                                        )
                                    }
                                />

                                <div>
                                    <label className="mb-1 block text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                        Indicaciones o Solicitudes Especiales
                                    </label>
                                    <textarea
                                        value={data.notas}
                                        onChange={(e) =>
                                            setData('notas', e.target.value)
                                        }
                                        className="w-full rounded-2xl border border-border bg-background px-4 py-3 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                        rows={3}
                                        placeholder="Ej. Almohada extra, horario aproximado de check-in..."
                                    />
                                </div>
                            </div>
                        )}

                        {/* PASO 4: Resumen & Confirmación Final */}
                        {pasoActual === 4 && (
                            <div className="animate-in fade-in-50 space-y-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
                                <div>
                                    <h2 className="text-lg font-black text-foreground md:text-xl">
                                        Resumen de la{' '}
                                        <span className="font-serif font-normal text-bugambilia-600 italic">
                                            Reserva
                                        </span>
                                    </h2>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        Por favor revise los detalles antes de
                                        enviar su solicitud
                                    </p>
                                </div>

                                <div className="space-y-3 rounded-2xl border border-border bg-background p-4 text-xs">
                                    <div className="flex items-center justify-between border-b border-border/40 pb-2">
                                        <span className="text-muted-foreground">
                                            Habitación:
                                        </span>
                                        <span className="font-black text-foreground">
                                            {room.nombre} ({room.numero})
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between border-b border-border/40 pb-2">
                                        <span className="text-muted-foreground">
                                            Titular:
                                        </span>
                                        <span className="font-bold text-foreground">
                                            {data.nombre_cliente} (
                                            {data.telefono_cliente})
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between border-b border-border/40 pb-2">
                                        <span className="text-muted-foreground">
                                            Fechas:
                                        </span>
                                        <span className="font-bold text-foreground">
                                            {data.fecha_check_in} a{' '}
                                            {data.fecha_check_out} (
                                            {nochesCalculadas} noche
                                            {nochesCalculadas > 1 ? 's' : ''})
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between border-b border-border/40 pb-2">
                                        <span className="text-muted-foreground">
                                            Huéspedes:
                                        </span>
                                        <span className="font-bold text-foreground">
                                            {data.adultos} adulto(s),{' '}
                                            {data.ninos} niño(s)
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between pt-2">
                                        <span className="text-sm font-bold text-foreground">
                                            Total Estimado:
                                        </span>
                                        <span className="text-xl font-black text-bugambilia-600 dark:text-bugambilia-400">
                                            {room.moneda}{' '}
                                            {formatearNumero(subtotalEstimado)}{' '}
                                            USD
                                        </span>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2 rounded-2xl bg-emerald-500/10 p-4 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                                    <ShieldCheck className="h-5 w-5 shrink-0 text-emerald-600" />
                                    <span>
                                        Garantía directa Hotel Bugambilias.
                                        Atención personalizada en su estancia.
                                    </span>
                                </div>
                            </div>
                        )}
                        <ListadoErroresFormulario errores={errors} />
                        <NavegacionReserva
                            pasoActual={pasoActual}
                            totalPasos={steps.length}
                            procesando={processing}
                            alRetroceder={retroceder}
                        />
                    </form>
                </div>
            </div>
        </>
    );
};
export default HabitacionReservar;
