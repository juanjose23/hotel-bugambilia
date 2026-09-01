import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    MapPin,
    Users,
    Send,
    CheckCircle2,
    Loader2,
} from 'lucide-react';
import { useEspacioReservaForm } from '@/modules/espacios/hooks/useEspacioReservaForm';
import type { EspacioReservarProps } from '@/modules/espacios/types';
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

export const EspacioReservar = ({ space }: EspacioReservarProps) => {
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

    const requiereCatering = watch('requiere_catering');

    return (
        <div className="min-h-screen bg-background font-sans">
            <Head>
                <title>{`Reservar ${space.nombre} — Hotel Bugambilias`}</title>
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
                </div>
            </div>

            <div className="container mx-auto px-4 py-8 sm:px-6 lg:max-w-4xl">
                <div className="grid grid-cols-1 gap-8 md:grid-cols-12">
                    {/* Tarjeta Resumen del Espacio (5 cols) */}
                    <div className="md:col-span-5">
                        <div className="overflow-hidden rounded-3xl border border-border bg-card shadow-xs">
                            <div className="relative aspect-4/3 w-full overflow-hidden bg-muted">
                                <img
                                    src={imagen}
                                    alt={space.nombre}
                                    className="h-full w-full object-cover"
                                />
                            </div>
                            <div className="p-5">
                                <span className="text-[10px] font-black tracking-wider text-bugambilia-600 uppercase dark:text-bugambilia-400">
                                    {space.tipo_label || space.tipo}
                                </span>
                                <h2 className="mt-1 text-lg font-black text-foreground">
                                    {space.nombre}
                                </h2>
                                <div className="mt-2 flex flex-col gap-1 text-xs text-muted-foreground">
                                    <div className="flex items-center gap-1.5">
                                        <MapPin className="size-3.5 text-bugambilia-500" />
                                        <span>
                                            {space.ubicacion ||
                                                'Hotel Bugambilias Estelí'}
                                        </span>
                                    </div>
                                    {space.capacidad && (
                                        <div className="flex items-center gap-1.5">
                                            <Users className="size-3.5 text-bugambilia-500" />
                                            <span>
                                                Capacidad hasta{' '}
                                                {space.capacidad} personas
                                            </span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Formulario de Reserva / Cotización (7 cols) */}
                    <div className="md:col-span-7">
                        <div className="rounded-3xl border border-border bg-card p-6 shadow-xs sm:p-8">
                            {isSubmitSuccessful ? (
                                <div className="flex flex-col items-center justify-center py-10 text-center">
                                    <CheckCircle2 className="size-14 text-emerald-500" />
                                    <h3 className="mt-3 text-lg font-black text-foreground">
                                        ¡Reserva Enviada!
                                    </h3>
                                    <p className="mt-1 max-w-sm text-xs text-muted-foreground">
                                        Hemos recibido tu solicitud y se ha
                                        transferido a WhatsApp para confirmar
                                        fecha y opciones de montaje con el
                                        equipo de eventos.
                                    </p>
                                    <Link
                                        href="/espacios"
                                        className="mt-6 inline-flex items-center gap-2 rounded-full bg-foreground px-6 py-2.5 text-xs font-bold text-background"
                                    >
                                        Explorar más espacios
                                    </Link>
                                </div>
                            ) : (
                                <form
                                    onSubmit={manejarSubmit}
                                    className="flex flex-col gap-4"
                                >
                                    <div>
                                        <h3 className="text-base font-black text-foreground sm:text-lg">
                                            Completa tu solicitud de reserva
                                        </h3>
                                        <p className="text-xs text-muted-foreground">
                                            Nos comunicaremos de inmediato para
                                            confirmar disponibilidad.
                                        </p>
                                    </div>

                                    <FieldGroup>
                                        {/* Nombre */}
                                        <Field>
                                            <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                Nombre o Empresa
                                            </FieldLabel>
                                            <Input
                                                {...register('nombre')}
                                                placeholder="Tu nombre completo"
                                                className="h-9 text-xs"
                                                aria-invalid={!!errors.nombre}
                                            />
                                            {errors.nombre?.message && (
                                                <FieldError>
                                                    {errors.nombre.message}
                                                </FieldError>
                                            )}
                                        </Field>

                                        {/* Contacto */}
                                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <Field>
                                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                    Correo Electrónico
                                                </FieldLabel>
                                                <Input
                                                    type="email"
                                                    {...register('email')}
                                                    placeholder="correo@ejemplo.com"
                                                    className="h-9 text-xs"
                                                    aria-invalid={
                                                        !!errors.email
                                                    }
                                                />
                                                {errors.email?.message && (
                                                    <FieldError>
                                                        {errors.email.message}
                                                    </FieldError>
                                                )}
                                            </Field>

                                            <Field>
                                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                    Teléfono / WhatsApp
                                                </FieldLabel>
                                                <Input
                                                    type="tel"
                                                    {...register('telefono')}
                                                    placeholder="+505 8888-8888"
                                                    className="h-9 text-xs"
                                                    aria-invalid={
                                                        !!errors.telefono
                                                    }
                                                />
                                                {errors.telefono?.message && (
                                                    <FieldError>
                                                        {
                                                            errors.telefono
                                                                .message
                                                        }
                                                    </FieldError>
                                                )}
                                            </Field>
                                        </div>

                                        {/* Tipo de Evento y Fecha */}
                                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <Field>
                                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                    Tipo de Evento
                                                </FieldLabel>
                                                <Input
                                                    type="text"
                                                    {...register('tipo_evento')}
                                                    placeholder="Ej. Conferencia, Boda, Taller"
                                                    className="h-9 text-xs"
                                                    aria-invalid={
                                                        !!errors.tipo_evento
                                                    }
                                                />
                                                {errors.tipo_evento
                                                    ?.message && (
                                                    <FieldError>
                                                        {
                                                            errors.tipo_evento
                                                                .message
                                                        }
                                                    </FieldError>
                                                )}
                                            </Field>

                                            <Field>
                                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                    Fecha
                                                </FieldLabel>
                                                <Input
                                                    type="date"
                                                    {...register('fecha')}
                                                    className="h-9 text-xs"
                                                    aria-invalid={
                                                        !!errors.fecha
                                                    }
                                                />
                                                {errors.fecha?.message && (
                                                    <FieldError>
                                                        {errors.fecha.message}
                                                    </FieldError>
                                                )}
                                            </Field>
                                        </div>

                                        {/* Horarios & Asistentes */}
                                        <div className="grid grid-cols-3 gap-3">
                                            <Field>
                                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                    Hora Inicio
                                                </FieldLabel>
                                                <Input
                                                    type="time"
                                                    {...register('hora_inicio')}
                                                    className="h-9 text-xs"
                                                    aria-invalid={
                                                        !!errors.hora_inicio
                                                    }
                                                />
                                                {errors.hora_inicio
                                                    ?.message && (
                                                    <FieldError>
                                                        {
                                                            errors.hora_inicio
                                                                .message
                                                        }
                                                    </FieldError>
                                                )}
                                            </Field>

                                            <Field>
                                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                    Hora Fin
                                                </FieldLabel>
                                                <Input
                                                    type="time"
                                                    {...register('hora_fin')}
                                                    className="h-9 text-xs"
                                                    aria-invalid={
                                                        !!errors.hora_fin
                                                    }
                                                />
                                                {errors.hora_fin?.message && (
                                                    <FieldError>
                                                        {
                                                            errors.hora_fin
                                                                .message
                                                        }
                                                    </FieldError>
                                                )}
                                            </Field>

                                            <Field>
                                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                    Asistentes
                                                </FieldLabel>
                                                <Input
                                                    type="number"
                                                    min="1"
                                                    {...register('asistentes')}
                                                    className="h-9 text-xs"
                                                    aria-invalid={
                                                        !!errors.asistentes
                                                    }
                                                />
                                                {errors.asistentes?.message && (
                                                    <FieldError>
                                                        {
                                                            errors.asistentes
                                                                .message
                                                        }
                                                    </FieldError>
                                                )}
                                            </Field>
                                        </div>

                                        {/* Catering Checkbox */}
                                        <Field
                                            orientation="horizontal"
                                            className="cursor-pointer"
                                        >
                                            <Checkbox
                                                id="catering"
                                                checked={requiereCatering}
                                                onCheckedChange={(checked) =>
                                                    setValue(
                                                        'requiere_catering',
                                                        checked === true,
                                                    )
                                                }
                                            />
                                            <FieldLabel
                                                htmlFor="catering"
                                                className="cursor-pointer text-xs font-bold text-foreground"
                                            >
                                                Deseo incluir servicio de
                                                alimentos y bebidas
                                            </FieldLabel>
                                        </Field>

                                        {/* Notas */}
                                        <Field>
                                            <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                                Notas o requerimientos
                                                especiales
                                            </FieldLabel>
                                            <Textarea
                                                {...register('notas')}
                                                placeholder="Montaje de mesas, proyectores, sonido..."
                                                rows={2}
                                                className="text-xs"
                                            />
                                            {errors.notas?.message && (
                                                <FieldError>
                                                    {errors.notas.message}
                                                </FieldError>
                                            )}
                                        </Field>
                                    </FieldGroup>

                                    <Button
                                        type="submit"
                                        disabled={isSubmitting}
                                        className="mt-3 flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-bugambilia-600 py-3 text-xs font-black text-white shadow-md hover:bg-bugambilia-700 active:scale-95 disabled:opacity-50"
                                    >
                                        {isSubmitting ? (
                                            <Loader2 className="size-3.5 animate-spin" />
                                        ) : (
                                            <Send className="size-3.5" />
                                        )}
                                        <span>
                                            {isSubmitting
                                                ? 'Enviando...'
                                                : 'Confirmar Solicitud de Reserva'}
                                        </span>
                                    </Button>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default EspacioReservar;
