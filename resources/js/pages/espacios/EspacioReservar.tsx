import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    Users,
    Send,
    CheckCircle2,
    Loader2,
    Calendar,
    Clock,
    Briefcase,
} from 'lucide-react';
import { useState } from 'react';
import { useEspacioReservaForm } from '@/modules/espacios/hooks/useEspacioReservaForm';
import type { EspacioReservarProps } from '@/modules/espacios/types';
import {
    BookingSummary,
    CompactBookingProgress,
} from '@/modules/shared/components/booking';
import { Button } from '@/modules/shared/components/ui/button';
import { Checkbox } from '@/modules/shared/components/ui/checkbox';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import { Textarea } from '@/modules/shared/components/ui/textarea';

const TIPOS_EVENTO = [
    'Conferencia',
    'Boda',
    'Reunión Corporativa',
    'Celebración Social',
    'Taller / Seminario',
    'Otro',
];

const PASOS_ESPACIO = [
    { num: 1, title: 'Evento & Horario', subtitle: 'Tipo, fecha y asistentes' },
    {
        num: 2,
        title: 'Montaje & Extras',
        subtitle: 'Catering y requerimientos',
    },
    { num: 3, title: 'Contacto & Envío', subtitle: 'Datos del organizador' },
];

export const EspacioReservar = ({ space }: EspacioReservarProps) => {
    const [pasoActual, setPasoActual] = useState<number>(1);

    const {
        register,
        manejarSubmit,
        watch,
        setValue,
        errors,
        isSubmitting,
        isSubmitSuccessful,
    } = useEspacioReservaForm({ space });

    const imagen =
        space.imagenes && space.imagenes.length > 0
            ? space.imagenes[0]
            : '/images/service-events.webp';

    const hoy = new Date().toISOString().split('T')[0];
    const requiereCatering = watch('requiere_catering');
    const tipoEventoSeleccionado = watch('tipo_evento') || 'Conferencia';
    const fechaSeleccionada = watch('fecha');
    const horaInicio = watch('hora_inicio');
    const horaFin = watch('hora_fin');
    const asistentes = watch('asistentes') || 10;

    const summaryRows = [
        {
            label: 'Tipo de Evento',
            value: tipoEventoSeleccionado,
            icon: <Briefcase className="size-3.5 text-primary" />,
        },
        {
            label: 'Fecha',
            value: fechaSeleccionada || 'Sin definir',
            icon: <Calendar className="size-3.5 text-primary" />,
        },
        {
            label: 'Horario',
            value:
                horaInicio && horaFin
                    ? `${horaInicio} — ${horaFin}`
                    : 'A convenir',
            icon: <Clock className="size-3.5 text-muted-foreground" />,
        },
        {
            label: 'Aforo proyectado',
            value: `${asistentes} asistentes`,
            subvalue: space.capacidad
                ? `Capacidad máxima: ${space.capacidad} personas`
                : undefined,
            icon: <Users className="size-3.5 text-muted-foreground" />,
        },
    ];

    const pricingData = {
        subtotal: space.precio_por_hora ? Number(space.precio_por_hora) * 4 : 0,
        subtotalLabel: 'Tarifa base estimada (4 horas)',
        servicesTotal: requiereCatering ? 2500 : 0,
        servicesLabel: requiereCatering ? 'Servicio de alimentos' : undefined,
        total:
            (space.precio_por_hora ? Number(space.precio_por_hora) * 4 : 0) +
            (requiereCatering ? 2500 : 0),
        currency: 'C$',
    };

    return (
        <div className="min-h-screen bg-background font-sans text-foreground">
            <Head>
                <title>{`Reservar ${space.nombre} — Hotel Bugambilias`}</title>
                <meta
                    name="description"
                    content={`Planifica tu evento en ${space.nombre}, Hotel Bugambilias. Montaje personalizado, catering y tecnología.`}
                />
            </Head>

            {/* Cabecera retorno */}
            <div className="border-b border-border/60 bg-card/40 py-3.5 backdrop-blur-md">
                <div className="container mx-auto flex items-center justify-between px-4 sm:px-6">
                    <Link
                        href={`/espacios/${space.slug || space.id}`}
                        className="inline-flex items-center gap-1.5 text-xs font-bold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        <span>Volver a detalles del espacio</span>
                    </Link>

                    <div className="flex items-center gap-2">
                        <span className="rounded-full bg-primary/10 px-3 py-1 text-xs font-black text-primary uppercase dark:text-rose-400">
                            {space.tipo_label || space.tipo}
                        </span>
                    </div>
                </div>
            </div>

            <div className="container mx-auto px-4 py-6 pb-28 sm:px-6 lg:max-w-6xl lg:pb-12">
                {/* Stepper Compacto */}
                <div className="mb-8">
                    <CompactBookingProgress
                        steps={PASOS_ESPACIO}
                        currentStep={pasoActual}
                        onStepClick={(p) => setPasoActual(p)}
                    />
                </div>

                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    {/* Columna Izquierda: Formulario en Pasos (8 cols) */}
                    <div className="lg:col-span-7 xl:col-span-8">
                        <div className="rounded-3xl border border-border bg-card p-6 shadow-xs sm:p-8">
                            {isSubmitSuccessful ? (
                                <div className="flex flex-col items-center justify-center py-10 text-center">
                                    <div className="flex size-16 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        <CheckCircle2 className="size-10" />
                                    </div>
                                    <h3 className="mt-4 text-xl font-black text-foreground">
                                        ¡Solicitud de Evento Enviada!
                                    </h3>
                                    <p className="mt-2 max-w-md text-xs leading-relaxed text-muted-foreground">
                                        Hemos recibido tu solicitud para{' '}
                                        <strong className="text-foreground">
                                            {space.nombre}
                                        </strong>
                                        . Un coordinador de eventos de Hotel
                                        Bugambilias se comunicará contigo vía
                                        WhatsApp para entregarte la cotización
                                        formal y planos de montaje.
                                    </p>
                                    <Link
                                        href="/espacios"
                                        className="mt-6 inline-flex items-center gap-2 rounded-2xl bg-foreground px-6 py-3 text-xs font-bold text-background shadow-md hover:bg-foreground/90"
                                    >
                                        Explorar más espacios
                                    </Link>
                                </div>
                            ) : (
                                <form
                                    onSubmit={manejarSubmit}
                                    className="space-y-6"
                                >
                                    {/* Paso 1: Tipo de Evento & Fechas */}
                                    {pasoActual === 1 && (
                                        <div className="animate-in fade-in space-y-5 duration-200">
                                            <div>
                                                <h3 className="text-base font-black text-foreground sm:text-lg">
                                                    Paso 1: ¿Qué tipo de evento
                                                    planeas?
                                                </h3>
                                                <p className="mt-0.5 text-xs text-muted-foreground">
                                                    Personalizaremos la
                                                    propuesta según el propósito
                                                    de tu reunión.
                                                </p>
                                            </div>

                                            {/* Chips de Selección de Tipo de Evento */}
                                            <div className="space-y-2">
                                                <label className="text-xs font-bold text-foreground">
                                                    Tipo de Celebración o
                                                    Encuentro *
                                                </label>
                                                <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                                    {TIPOS_EVENTO.map(
                                                        (tipo) => {
                                                            const esActivo =
                                                                tipoEventoSeleccionado ===
                                                                tipo;

                                                            return (
                                                                <button
                                                                    key={tipo}
                                                                    type="button"
                                                                    onClick={() =>
                                                                        setValue(
                                                                            'tipo_evento',
                                                                            tipo,
                                                                            {
                                                                                shouldValidate: true,
                                                                            },
                                                                        )
                                                                    }
                                                                    className={`rounded-2xl border p-3 text-left text-xs font-bold transition-all ${
                                                                        esActivo
                                                                            ? 'border-primary bg-primary/10 text-primary dark:border-rose-400 dark:bg-rose-950/40 dark:text-rose-200'
                                                                            : 'border-border/80 bg-background text-muted-foreground hover:border-primary/40 hover:text-foreground'
                                                                    }`}
                                                                >
                                                                    <span>
                                                                        {tipo}
                                                                    </span>
                                                                </button>
                                                            );
                                                        },
                                                    )}
                                                </div>
                                            </div>

                                            {/* Fecha & Aforo */}
                                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <Field>
                                                    <FieldLabel className="text-xs font-bold text-foreground">
                                                        Fecha del Evento *
                                                    </FieldLabel>
                                                    <Input
                                                        type="date"
                                                        min={hoy}
                                                        {...register('fecha')}
                                                        className="h-11 rounded-2xl text-xs font-bold"
                                                        aria-invalid={
                                                            !!errors.fecha
                                                        }
                                                    />
                                                    {errors.fecha?.message && (
                                                        <FieldError className="text-[10px]">
                                                            {
                                                                errors.fecha
                                                                    .message
                                                            }
                                                        </FieldError>
                                                    )}
                                                </Field>

                                                <Field>
                                                    <FieldLabel className="text-xs font-bold text-foreground">
                                                        Cantidad Estimada de
                                                        Asistentes *
                                                    </FieldLabel>
                                                    <Input
                                                        type="number"
                                                        min="1"
                                                        max={
                                                            space.capacidad ||
                                                            500
                                                        }
                                                        {...register(
                                                            'asistentes',
                                                            {
                                                                valueAsNumber: true,
                                                            },
                                                        )}
                                                        className="h-11 rounded-2xl text-xs font-bold"
                                                        aria-invalid={
                                                            !!errors.asistentes
                                                        }
                                                    />
                                                    {errors.asistentes
                                                        ?.message && (
                                                        <FieldError className="text-[10px]">
                                                            {
                                                                errors
                                                                    .asistentes
                                                                    .message
                                                            }
                                                        </FieldError>
                                                    )}
                                                </Field>
                                            </div>

                                            {/* Horarios */}
                                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <Field>
                                                    <FieldLabel className="text-xs font-bold text-foreground">
                                                        Hora de Inicio *
                                                    </FieldLabel>
                                                    <Input
                                                        type="time"
                                                        {...register(
                                                            'hora_inicio',
                                                        )}
                                                        className="h-11 rounded-2xl text-xs font-bold"
                                                        aria-invalid={
                                                            !!errors.hora_inicio
                                                        }
                                                    />
                                                </Field>

                                                <Field>
                                                    <FieldLabel className="text-xs font-bold text-foreground">
                                                        Hora de Finalización *
                                                    </FieldLabel>
                                                    <Input
                                                        type="time"
                                                        {...register(
                                                            'hora_fin',
                                                        )}
                                                        className="h-11 rounded-2xl text-xs font-bold"
                                                        aria-invalid={
                                                            !!errors.hora_fin
                                                        }
                                                    />
                                                </Field>
                                            </div>

                                            <div className="flex justify-end border-t border-border pt-4">
                                                <Button
                                                    type="button"
                                                    onClick={() =>
                                                        setPasoActual(2)
                                                    }
                                                    className="h-11 rounded-2xl bg-primary px-8 text-xs font-black text-primary-foreground shadow-md hover:bg-primary/90"
                                                >
                                                    <span>
                                                        Continuar a montaje &
                                                        extras
                                                    </span>
                                                    <ArrowRight className="ml-2 size-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    )}

                                    {/* Paso 2: Configuración de Extras & Catering */}
                                    {pasoActual === 2 && (
                                        <div className="animate-in fade-in space-y-5 duration-200">
                                            <div>
                                                <h3 className="text-base font-black text-foreground sm:text-lg">
                                                    Paso 2: Montaje & Servicios
                                                    Complementarios
                                                </h3>
                                                <p className="mt-0.5 text-xs text-muted-foreground">
                                                    Añade gastronomía, sonido o
                                                    especificaciones para tus
                                                    asistentes.
                                                </p>
                                            </div>

                                            {/* Catering Checkbox */}
                                            <div className="rounded-2xl border border-border/80 bg-background/80 p-4">
                                                <Field
                                                    orientation="horizontal"
                                                    className="cursor-pointer"
                                                >
                                                    <Checkbox
                                                        id="catering"
                                                        checked={
                                                            requiereCatering
                                                        }
                                                        onCheckedChange={(
                                                            checked,
                                                        ) =>
                                                            setValue(
                                                                'requiere_catering',
                                                                checked ===
                                                                    true,
                                                            )
                                                        }
                                                    />
                                                    <div>
                                                        <FieldLabel
                                                            htmlFor="catering"
                                                            className="cursor-pointer text-xs font-black text-foreground"
                                                        >
                                                            Deseo incluir
                                                            servicio de
                                                            alimentos y bebidas
                                                            (Catering)
                                                        </FieldLabel>
                                                        <p className="text-[11px] text-muted-foreground">
                                                            Coffee break, buffet
                                                            gourmet o bocadillos
                                                            preparados por el
                                                            restaurante.
                                                        </p>
                                                    </div>
                                                </Field>
                                            </div>

                                            {/* Notas y Montaje */}
                                            <Field>
                                                <FieldLabel className="text-xs font-bold text-foreground">
                                                    Instrucciones de Montaje o
                                                    Equipamiento Especial
                                                </FieldLabel>
                                                <Textarea
                                                    {...register('notas')}
                                                    placeholder="Ej: Distribución en U, proyector HDMI, micrófonos inalámbricos, podio..."
                                                    rows={4}
                                                    className="rounded-2xl text-xs leading-relaxed"
                                                />
                                            </Field>

                                            <div className="flex items-center justify-between border-t border-border pt-4">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    onClick={() =>
                                                        setPasoActual(1)
                                                    }
                                                    className="h-11 rounded-2xl px-5 text-xs font-bold"
                                                >
                                                    <ArrowLeft className="mr-2 size-4" />
                                                    <span>Paso Anterior</span>
                                                </Button>

                                                <Button
                                                    type="button"
                                                    onClick={() =>
                                                        setPasoActual(3)
                                                    }
                                                    className="h-11 rounded-2xl bg-primary px-8 text-xs font-black text-primary-foreground shadow-md hover:bg-primary/90"
                                                >
                                                    <span>
                                                        Continuar a datos del
                                                        organizador
                                                    </span>
                                                    <ArrowRight className="ml-2 size-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    )}

                                    {/* Paso 3: Contacto & Solicitud */}
                                    {pasoActual === 3 && (
                                        <div className="animate-in fade-in space-y-5 duration-200">
                                            <div>
                                                <h3 className="text-base font-black text-foreground sm:text-lg">
                                                    Paso 3: Datos de Contacto
                                                    del Organizador
                                                </h3>
                                                <p className="mt-0.5 text-xs text-muted-foreground">
                                                    A estos medios te enviaremos
                                                    la cotización detallada y
                                                    contrato de reserva.
                                                </p>
                                            </div>

                                            <FieldGroup>
                                                <Field>
                                                    <FieldLabel className="text-xs font-bold text-foreground">
                                                        Nombre Completo o Razón
                                                        Social *
                                                    </FieldLabel>
                                                    <Input
                                                        {...register('nombre')}
                                                        placeholder="Tu nombre o empresa"
                                                        className="h-11 rounded-2xl text-xs font-medium"
                                                        aria-invalid={
                                                            !!errors.nombre
                                                        }
                                                    />
                                                    {errors.nombre?.message && (
                                                        <FieldError className="text-[10px]">
                                                            {
                                                                errors.nombre
                                                                    .message
                                                            }
                                                        </FieldError>
                                                    )}
                                                </Field>

                                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                    <Field>
                                                        <FieldLabel className="text-xs font-bold text-foreground">
                                                            Correo Electrónico *
                                                        </FieldLabel>
                                                        <Input
                                                            type="email"
                                                            {...register(
                                                                'email',
                                                            )}
                                                            placeholder="correo@ejemplo.com"
                                                            className="h-11 rounded-2xl text-xs"
                                                            aria-invalid={
                                                                !!errors.email
                                                            }
                                                        />
                                                        {errors.email
                                                            ?.message && (
                                                            <FieldError className="text-[10px]">
                                                                {
                                                                    errors.email
                                                                        .message
                                                                }
                                                            </FieldError>
                                                        )}
                                                    </Field>

                                                    <Field>
                                                        <FieldLabel className="text-xs font-bold text-foreground">
                                                            WhatsApp / Teléfono
                                                            *
                                                        </FieldLabel>
                                                        <Input
                                                            type="tel"
                                                            {...register(
                                                                'telefono',
                                                            )}
                                                            placeholder="+505 8888-8888"
                                                            className="h-11 rounded-2xl text-xs"
                                                            aria-invalid={
                                                                !!errors.telefono
                                                            }
                                                        />
                                                        {errors.telefono
                                                            ?.message && (
                                                            <FieldError className="text-[10px]">
                                                                {
                                                                    errors
                                                                        .telefono
                                                                        .message
                                                                }
                                                            </FieldError>
                                                        )}
                                                    </Field>
                                                </div>
                                            </FieldGroup>

                                            <div className="flex items-center justify-between border-t border-border pt-4">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    onClick={() =>
                                                        setPasoActual(2)
                                                    }
                                                    className="h-11 rounded-2xl px-5 text-xs font-bold"
                                                >
                                                    <ArrowLeft className="mr-2 size-4" />
                                                    <span>Paso Anterior</span>
                                                </Button>

                                                <Button
                                                    type="submit"
                                                    disabled={isSubmitting}
                                                    className="h-12 rounded-2xl bg-primary px-8 text-xs font-black text-primary-foreground shadow-lg hover:bg-primary/90 active:scale-95"
                                                >
                                                    {isSubmitting ? (
                                                        <>
                                                            <Loader2 className="mr-2 size-4 animate-spin" />
                                                            <span>
                                                                Enviando
                                                                solicitud...
                                                            </span>
                                                        </>
                                                    ) : (
                                                        <>
                                                            <Send className="mr-2 size-4" />
                                                            <span>
                                                                Solicitar
                                                                Cotización de
                                                                Evento
                                                            </span>
                                                        </>
                                                    )}
                                                </Button>
                                            </div>
                                        </div>
                                    )}
                                </form>
                            )}
                        </div>
                    </div>

                    {/* Columna Derecha: Sticky BookingSummary (4 cols) */}
                    <div className="lg:col-span-5 xl:col-span-4">
                        <BookingSummary
                            title="Tu Evento"
                            subtitle="Cotización y reserva de espacio"
                            resourceName={space.nombre}
                            resourceCategory={
                                space.tipo_label ||
                                space.tipo ||
                                'Salón de Eventos'
                            }
                            resourceImage={imagen}
                            rows={summaryRows}
                            pricing={pricingData}
                            guarantees={[
                                'Atención ejecutiva personalizada',
                                'Flexibilidad en montaje y cancelaciones',
                                'Respaldo con generador eléctrico propio',
                            ]}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};

export default EspacioReservar;
