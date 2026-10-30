import {
    Send,
    CheckCircle2,
    Loader2,
    Clock,
    Sparkles,
    ShieldCheck,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/modules/shared/components/ui/sheet';
import { Textarea } from '@/modules/shared/components/ui/textarea';
import { useServicioConsultaForm } from '../hooks/useServicioConsultaForm';
import type { ServicioItem } from '../types';

interface PropsServicioConsultaSheet {
    abierto: boolean;
    alCerrar: () => void;
    servicio: ServicioItem;
}

const HORARIOS_SUGERIDOS_SERVICIO = [
    '09:00',
    '10:30',
    '12:00',
    '14:00',
    '15:30',
    '17:00',
];

export const ServicioConsultaSheet = ({
    abierto,
    alCerrar,
    servicio,
}: PropsServicioConsultaSheet) => {
    const {
        register,
        manejarSubmit,
        reset,
        errors,
        isSubmitting,
        isSubmitSuccessful,
    } = useServicioConsultaForm({ servicio });

    const [horaSeleccionada, setHoraSeleccionada] = useState('10:30');
    const hoy = new Date().toISOString().split('T')[0];
    const precio = Number(servicio.precio || 0);
    const esTransporte =
        servicio.categoria?.toLowerCase().includes('transporte') ||
        servicio.nombre?.toLowerCase().includes('traslado');

    const handleCerrar = () => {
        reset();
        alCerrar();
    };

    return (
        <Sheet open={abierto} onOpenChange={handleCerrar}>
            <SheetContent
                side="right"
                className="w-full overflow-y-auto font-sans sm:max-w-md"
            >
                <SheetHeader className="text-left">
                    <div className="inline-flex items-center gap-1.5 self-start rounded-full bg-primary/10 px-2.5 py-0.5 text-[10px] font-black text-primary uppercase dark:text-rose-400">
                        <Sparkles className="size-3" />
                        <span>
                            {servicio.categoria || 'Servicio del Hotel'}
                        </span>
                    </div>
                    <SheetTitle className="mt-1 text-lg font-black text-foreground">
                        Reservar {servicio.nombre}
                    </SheetTitle>
                    <SheetDescription className="text-xs text-muted-foreground">
                        {servicio.descripcion ||
                            'Selecciona la fecha y hora que mejor se adapte a tu itinerario.'}
                    </SheetDescription>
                </SheetHeader>

                {/* Tarjeta de Resumen Rápido del Servicio */}
                <div className="mt-4 rounded-2xl border border-border/70 bg-muted/20 p-3.5 text-xs">
                    <div className="flex items-center justify-between">
                        <span className="font-bold text-foreground">
                            Tarifa del servicio
                        </span>
                        <span className="text-sm font-black text-primary dark:text-rose-400">
                            {servicio.moneda || '$'}
                            {precio > 0
                                ? precio.toLocaleString()
                                : 'A consultar'}
                        </span>
                    </div>
                    <div className="mt-1 flex items-center gap-2 text-[11px] text-muted-foreground">
                        <Clock className="size-3 text-primary" />
                        <span>
                            {esTransporte
                                ? 'Horario coordinado a tu llegada'
                                : 'Duración aproximada: 60 minutos'}
                        </span>
                    </div>
                </div>

                {isSubmitSuccessful ? (
                    <div className="flex flex-col items-center justify-center py-12 text-center">
                        <div className="flex size-14 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 className="size-8" />
                        </div>
                        <h4 className="mt-3 text-base font-black text-foreground">
                            ¡Solicitud Generada con Éxito!
                        </h4>
                        <p className="mt-1 max-w-xs text-xs text-muted-foreground">
                            Tu solicitud se ha transmitido a nuestro concierge
                            en WhatsApp para reservar tu espacio sin demoras.
                        </p>
                        <Button
                            type="button"
                            onClick={handleCerrar}
                            className="mt-6 rounded-2xl bg-foreground px-6 text-xs font-bold text-background hover:bg-foreground/90"
                        >
                            Listo
                        </Button>
                    </div>
                ) : (
                    <form
                        onSubmit={manejarSubmit}
                        className="mt-5 flex flex-col gap-4"
                    >
                        <FieldGroup>
                            {/* Fecha y Personas */}
                            <div className="grid grid-cols-2 gap-3">
                                <Field>
                                    <FieldLabel className="text-[11px] font-black text-muted-foreground uppercase">
                                        Fecha Deseada *
                                    </FieldLabel>
                                    <Input
                                        type="date"
                                        min={hoy}
                                        {...register('fecha')}
                                        className="h-10 rounded-xl text-xs font-bold"
                                        aria-invalid={!!errors.fecha}
                                    />
                                    {errors.fecha?.message && (
                                        <FieldError className="text-[10px]">
                                            {errors.fecha.message}
                                        </FieldError>
                                    )}
                                </Field>

                                <Field>
                                    <FieldLabel className="text-[11px] font-black text-muted-foreground uppercase">
                                        Personas *
                                    </FieldLabel>
                                    <Input
                                        type="number"
                                        min="1"
                                        max="20"
                                        {...register('personas')}
                                        className="h-10 rounded-xl text-center text-xs font-bold"
                                        aria-invalid={!!errors.personas}
                                    />
                                    {errors.personas?.message && (
                                        <FieldError className="text-[10px]">
                                            {errors.personas.message}
                                        </FieldError>
                                    )}
                                </Field>
                            </div>

                            {/* Horarios Sugeridos */}
                            <div>
                                <label className="text-[11px] font-black text-muted-foreground uppercase">
                                    Horario Sugerido
                                </label>
                                <div className="mt-1.5 grid grid-cols-3 gap-1.5">
                                    {HORARIOS_SUGERIDOS_SERVICIO.map((hora) => (
                                        <button
                                            key={hora}
                                            type="button"
                                            onClick={() =>
                                                setHoraSeleccionada(hora)
                                            }
                                            className={`rounded-xl py-1.5 text-xs font-bold transition-all ${
                                                horaSeleccionada === hora
                                                    ? 'bg-primary text-primary-foreground shadow-xs'
                                                    : 'border border-border bg-card text-muted-foreground hover:text-foreground'
                                            }`}
                                        >
                                            {hora}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Nombre Completo */}
                            <Field>
                                <FieldLabel className="text-[11px] font-black text-muted-foreground uppercase">
                                    Nombre Completo *
                                </FieldLabel>
                                <Input
                                    {...register('nombre')}
                                    placeholder="Ej. Juan Pérez"
                                    className="h-10 rounded-xl text-xs font-medium"
                                    aria-invalid={!!errors.nombre}
                                />
                                {errors.nombre?.message && (
                                    <FieldError className="text-[10px]">
                                        {errors.nombre.message}
                                    </FieldError>
                                )}
                            </Field>

                            {/* Contacto: Email & Teléfono */}
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <Field>
                                    <FieldLabel className="text-[11px] font-black text-muted-foreground uppercase">
                                        Correo *
                                    </FieldLabel>
                                    <Input
                                        type="email"
                                        {...register('email')}
                                        placeholder="correo@ejemplo.com"
                                        className="h-10 rounded-xl text-xs"
                                        aria-invalid={!!errors.email}
                                    />
                                    {errors.email?.message && (
                                        <FieldError className="text-[10px]">
                                            {errors.email.message}
                                        </FieldError>
                                    )}
                                </Field>

                                <Field>
                                    <FieldLabel className="text-[11px] font-black text-muted-foreground uppercase">
                                        WhatsApp / Tel *
                                    </FieldLabel>
                                    <Input
                                        type="tel"
                                        {...register('telefono')}
                                        placeholder="+505 8888-8888"
                                        className="h-10 rounded-xl text-xs"
                                        aria-invalid={!!errors.telefono}
                                    />
                                    {errors.telefono?.message && (
                                        <FieldError className="text-[10px]">
                                            {errors.telefono.message}
                                        </FieldError>
                                    )}
                                </Field>
                            </div>

                            {/* Requerimientos especiales */}
                            <Field>
                                <FieldLabel className="text-[11px] font-black text-muted-foreground uppercase">
                                    {esTransporte
                                        ? 'Lugar de recogida y vuelo / hora estimada'
                                        : 'Notas o preferencias especiales'}
                                </FieldLabel>
                                <Textarea
                                    {...register('notas')}
                                    placeholder={
                                        esTransporte
                                            ? 'Indica tu número de vuelo, aerolínea y punto de recogida...'
                                            : 'Preferencias sobre masajista, aceites, alergias o detalles del servicio...'
                                    }
                                    rows={2}
                                    className="rounded-xl text-xs"
                                />
                            </Field>
                        </FieldGroup>

                        {/* Botón CTA Descriptivo */}
                        <div className="pt-2">
                            <Button
                                type="submit"
                                disabled={isSubmitting}
                                className="flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-primary text-xs font-black text-primary-foreground shadow-md hover:bg-primary/90 active:scale-95 disabled:opacity-50"
                            >
                                {isSubmitting ? (
                                    <>
                                        <Loader2 className="size-4 animate-spin" />
                                        <span>Procesando...</span>
                                    </>
                                ) : (
                                    <>
                                        <Send className="size-4" />
                                        <span>
                                            Reservar Servicio con Concierge
                                        </span>
                                    </>
                                )}
                            </Button>

                            <div className="mt-2.5 flex items-center justify-center gap-1.5 text-[10px] font-bold text-muted-foreground">
                                <ShieldCheck className="size-3.5 text-emerald-500" />
                                <span>
                                    Coordinación directa y confirmación en
                                    minutos
                                </span>
                            </div>
                        </div>
                    </form>
                )}
            </SheetContent>
        </Sheet>
    );
};

export default ServicioConsultaSheet;
