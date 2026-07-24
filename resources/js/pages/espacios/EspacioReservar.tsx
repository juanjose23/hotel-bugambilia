import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    Clock,
    MapPin,
    Minus,
    Plus,
    ShieldCheck,
} from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { IndicadorPasosReserva } from '@/modules/reservations/components/IndicadorPasosReserva';
import { ListadoErroresFormulario } from '@/modules/reservations/components/ListadoErroresFormulario';
import { NavegacionReserva } from '@/modules/reservations/components/NavegacionReserva';
import { SeccionesAdicionalesReserva } from '@/modules/reservations/components/SeccionesAdicionalesReserva';
import { SolicitudCuentaEstancia } from '@/modules/reservations/components/SolicitudCuentaEstancia';
import { usePasosReserva } from '@/modules/reservations/hooks/usePasosReserva';
import { useAlmacenReserva } from '@/modules/reservations/stores/useAlmacenReserva';
import type { DatosBorradorEspacio } from '@/modules/reservations/types/borradorReserva';
import { useAutenticacion } from '@/modules/shared/hooks/useAutenticacion';
import { Badge } from '@/modules/shared/ui/insignia';
import { formatearNumero } from '@/modules/shared/utils/formato';
import type { PropiedadesReservarEspacio } from '@/modules/spaces/types/reservaEspacio';
const EspacioReservar = ({
    space,
    opcionesReserva,
}: PropiedadesReservarEspacio) => {
    const auth = useAutenticacion();
    const authUser = auth?.user;
    const borradorGuardado = useAlmacenReserva((estado) => estado.borrador);
    const guardarBorrador = useAlmacenReserva(
        (estado) => estado.guardarBorrador,
    );
    const limpiarBorrador = useAlmacenReserva(
        (estado) => estado.limpiarBorrador,
    );
    const borradorEspacio =
        borradorGuardado?.tipo === 'espacio' &&
        borradorGuardado.datos.espacio_id === String(space.id)
            ? borradorGuardado
            : null;
    const { pasoActual, avanzar, retroceder, irAlPaso } = usePasosReserva({
        totalPasos: 4,
        pasoInicial: borradorEspacio?.pasoActual,
    });
    const [horaInicio, setHoraInicio] = useState(
        borradorEspacio?.horaInicio || '12:00',
    );
    const [horaFin, setHoraFin] = useState(borradorEspacio?.horaFin || '14:00');
    const datosIniciales: DatosBorradorEspacio = {
        telefono_cliente: '',
        tipo_reserva: 'restaurante' as const,
        espacio_id: String(space?.id || ''),
        fecha_check_in: new Date().toISOString().split('T')[0],
        hora_reserva: '12:00',
        hora_fin: '14:00',
        adultos: 2,
        ninos: 0,
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
        ...borradorEspacio?.datos,
        nombre_cliente:
            borradorEspacio?.datos.nombre_cliente || authUser?.name || '',
        email_cliente:
            borradorEspacio?.datos.email_cliente || authUser?.email || '',
    };
    const { data, setData, post, processing, transform, errors } =
        useForm<DatosBorradorEspacio>(datosIniciales);
    useEffect(() => {
        guardarBorrador({
            tipo: 'espacio',
            rutaRetorno: `/espacios/${space.slug || space.id}/reservar`,
            pasoActual,
            horaInicio,
            horaFin,
            datos: data,
        });
    }, [
        data,
        guardarBorrador,
        horaFin,
        horaInicio,
        pasoActual,
        space.id,
        space.slug,
    ]);
    const imagenPrincipal =
        space?.imagenes && space.imagenes.length > 0
            ? space.imagenes[0]
            : '/images/terrace.webp';
    const calcularHorasEstimadas = () => {
        if (!horaInicio || !horaFin) {
            return 1;
        }

        const [h1, m1] = horaInicio.split(':').map(Number);
        const [h2, m2] = horaFin.split(':').map(Number);
        const mins1 = (h1 || 0) * 60 + (m1 || 0);
        const mins2 = (h2 || 0) * 60 + (m2 || 0);
        const diffMins =
            mins2 > mins1 ? mins2 - mins1 : 24 * 60 - mins1 + mins2;

        return Math.max(1, Math.ceil(diffMins / 60));
    };
    const horasEstimadas = calcularHorasEstimadas();
    const precioPorHora = space?.precio_por_hora || 0;
    const precioBase = space?.precio_base || 0;
    const estimacionBase =
        precioPorHora > 0 ? precioPorHora * horasEstimadas : precioBase;
    const steps = [
        { id: 1, titulo: 'Día y Horario' },
        { id: 2, titulo: 'Asistentes' },
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
            const inicioFmt = horaInicio ? horaInicio.slice(0, 5) : '12:00';
            const finFmt = horaFin ? horaFin.slice(0, 5) : '14:00';
            transform((valores) => ({
                ...valores,
                hora_reserva: inicioFmt,
                hora_fin: finFmt,
            }));
            post('/reservas', { onSuccess: limpiarBorrador });
        }
    };

    return (
        <>
            <Head title={`Reservar ${space.nombre} - Hotel Bugambilias`} />

            <div className="min-h-screen bg-background pt-4 pb-28 font-sans md:pt-8 lg:pb-16">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    {/* Botón de Retorno Móvil */}
                    <div className="mb-4 flex items-center justify-between">
                        <Link
                            href={`/espacios/${space.slug || space.id}`}
                            className="inline-flex items-center text-xs font-bold text-bugambilia-600 hover:underline dark:text-bugambilia-400"
                        >
                            <ArrowLeft className="mr-1.5 h-4 w-4" />
                            Volver al Detalle del Ambiente
                        </Link>
                        <span className="text-[11px] font-extrabold tracking-widest text-muted-foreground uppercase">
                            Paso {pasoActual} de 4
                        </span>
                    </div>

                    {/* Encabezado Botánico Fino Hotel Bugambilias */}
                    <div className="mb-6 flex items-center gap-4 rounded-3xl border border-border bg-card p-4 shadow-xs md:p-6">
                        <img
                            src={imagenPrincipal}
                            alt={space.nombre}
                            className="h-20 w-20 shrink-0 rounded-2xl object-cover shadow-sm md:h-24 md:w-28"
                        />
                        <div className="min-w-0 flex-1">
                            <div className="mb-1 flex flex-wrap items-center gap-2">
                                <Badge className="bg-bugambilia-500/10 text-[10px] font-extrabold text-bugambilia-700 dark:text-bugambilia-300">
                                    {space.tipo_label}
                                </Badge>
                                {space.es_oferta && (
                                    <Badge className="bg-rose-500/10 text-[10px] font-black text-rose-600 dark:text-rose-400">
                                        ¡Oferta!
                                    </Badge>
                                )}
                            </div>
                            <h1 className="truncate text-lg font-black text-foreground md:text-2xl">
                                {space.nombre}
                            </h1>
                            <p className="mt-0.5 flex items-center text-xs text-muted-foreground">
                                <MapPin className="mr-1 h-3.5 w-3.5 shrink-0 text-bugambilia-600" />
                                <span className="truncate">
                                    {space.ubicacion}
                                </span>
                            </p>
                        </div>
                    </div>
                    <IndicadorPasosReserva
                        pasoActual={pasoActual}
                        pasos={steps}
                        alSeleccionarPaso={irAlPaso}
                    />

                    {/* Formulario de reserva */}
                    <form onSubmit={handleNextStep} className="space-y-6">
                        {/* PASO 1: Día y Horario + Datos */}
                        {pasoActual === 1 && (
                            <div className="animate-in fade-in-50 space-y-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
                                <div>
                                    <h2 className="text-lg font-black text-foreground md:text-xl">
                                        Día, Horario &{' '}
                                        <span className="font-serif font-normal text-bugambilia-600 italic">
                                            Titular
                                        </span>
                                    </h2>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        Especifique el día del evento y los
                                        datos de quien coordina la reserva
                                    </p>
                                </div>

                                <div className="space-y-4">
                                    <div>
                                        <label className="mb-1 block text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                            Fecha de Reserva *
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
                                            className="w-full rounded-2xl border border-border bg-background px-4 py-3.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="mb-1 block flex items-center gap-1 text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                <Clock className="h-3.5 w-3.5 text-bugambilia-600" />
                                                Hora Inicio *
                                            </label>
                                            <input
                                                type="time"
                                                required
                                                value={horaInicio}
                                                onChange={(e) =>
                                                    setHoraInicio(
                                                        e.target.value,
                                                    )
                                                }
                                                className="w-full rounded-2xl border border-border bg-background px-4 py-3.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                            />
                                        </div>

                                        <div>
                                            <label className="mb-1 block flex items-center gap-1 text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                <Clock className="h-3.5 w-3.5 text-bugambilia-600" />
                                                Hora Fin *
                                            </label>
                                            <input
                                                type="time"
                                                required
                                                value={horaFin}
                                                onChange={(e) =>
                                                    setHoraFin(e.target.value)
                                                }
                                                className="w-full rounded-2xl border border-border bg-background px-4 py-3.5 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                            />
                                        </div>
                                    </div>

                                    {precioPorHora > 0 && (
                                        <div className="flex items-center justify-between rounded-2xl bg-muted/50 p-3.5 text-xs font-medium">
                                            <span className="text-muted-foreground">
                                                Duración Calculada:
                                            </span>
                                            <span className="rounded-xl bg-background px-3 py-1 font-extrabold text-foreground shadow-xs">
                                                {horasEstimadas} hora(s)
                                            </span>
                                        </div>
                                    )}
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
                                            placeholder="Ej. Carlos Mendoza"
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

                        {/* PASO 2: Asistentes */}
                        {pasoActual === 2 && (
                            <div className="animate-in fade-in-50 space-y-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
                                <div>
                                    <h2 className="text-lg font-black text-foreground md:text-xl">
                                        Asistentes &{' '}
                                        <span className="font-serif font-normal text-bugambilia-600 italic">
                                            Capacidad
                                        </span>
                                    </h2>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        Capacidad máxima recomendada:{' '}
                                        {space.capacidad} personas
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
                                                            space.capacidad,
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
                                            Servicios Adicionales
                                        </span>
                                    </h2>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        Añada servicios de montaje, banquetes o
                                        mobiliario extra
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
                                        Notas o Requerimientos de Montaje
                                    </label>
                                    <textarea
                                        value={data.notas}
                                        onChange={(e) =>
                                            setData('notas', e.target.value)
                                        }
                                        className="w-full rounded-2xl border border-border bg-background px-4 py-3 text-xs font-semibold text-foreground transition-all focus:ring-2 focus:ring-bugambilia-500 focus:outline-none"
                                        rows={3}
                                        placeholder="Ej. Decoración especial, distribución de mesas en U..."
                                    />
                                </div>
                            </div>
                        )}

                        {/* PASO 4: Resumen & Confirmación Final */}
                        {pasoActual === 4 && (
                            <div className="animate-in fade-in-50 space-y-6 rounded-3xl border border-border bg-card p-5 shadow-sm duration-300 md:p-8">
                                <div>
                                    <h2 className="text-lg font-black text-foreground md:text-xl">
                                        Resumen del{' '}
                                        <span className="font-serif font-normal text-bugambilia-600 italic">
                                            Ambiente
                                        </span>
                                    </h2>
                                    <p className="mt-0.5 text-xs text-muted-foreground">
                                        Revise la estimación de su evento antes
                                        de confirmar
                                    </p>
                                </div>

                                <div className="space-y-3 rounded-2xl border border-border bg-background p-4 text-xs">
                                    <div className="flex items-center justify-between border-b border-border/40 pb-2">
                                        <span className="text-muted-foreground">
                                            Ambiente:
                                        </span>
                                        <span className="font-black text-foreground">
                                            {space.nombre}
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
                                            Día y Horario:
                                        </span>
                                        <span className="font-bold text-foreground">
                                            {data.fecha_check_in} ({horaInicio}{' '}
                                            a {horaFin})
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between border-b border-border/40 pb-2">
                                        <span className="text-muted-foreground">
                                            Asistentes:
                                        </span>
                                        <span className="font-bold text-foreground">
                                            {data.adultos} adulto(s),{' '}
                                            {data.ninos} niño(s)
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between pt-2">
                                        <span className="text-sm font-bold text-foreground">
                                            Subtotal Estimado:
                                        </span>
                                        <span className="text-xl font-black text-bugambilia-600 dark:text-bugambilia-400">
                                            {space.moneda}{' '}
                                            {formatearNumero(estimacionBase)}
                                        </span>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2 rounded-2xl bg-emerald-500/10 p-4 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                                    <ShieldCheck className="h-5 w-5 shrink-0 text-emerald-600" />
                                    <span>
                                        Reserva garantizada directamente con
                                        Hotel Bugambilias.
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
export default EspacioReservar;
