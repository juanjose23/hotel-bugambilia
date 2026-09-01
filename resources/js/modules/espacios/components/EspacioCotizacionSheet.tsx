import { Send, CheckCircle2, Loader2 } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import { Checkbox } from '@/modules/shared/components/ui/checkbox';
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
import { useEspacioReservaForm } from '../hooks/useEspacioReservaForm';
import type { EspacioItem } from '../types';

interface PropsEspacioCotizacionSheet {
    abierto: boolean;
    alCerrar: () => void;
    espacio: EspacioItem;
}

export const EspacioCotizacionSheet = ({
    abierto,
    alCerrar,
    espacio,
}: PropsEspacioCotizacionSheet) => {
    const {
        register,
        manejarSubmit,
        watch,
        setValue,
        reset,
        errors,
        isSubmitting,
        isSubmitSuccessful,
    } = useEspacioReservaForm({ space: espacio });

    const handleCerrar = () => {
        reset();
        alCerrar();
    };

    const requiereCatering = watch('requiere_catering');

    return (
        <Sheet open={abierto} onOpenChange={handleCerrar}>
            <SheetContent
                side="right"
                className="w-full overflow-y-auto font-sans sm:max-w-md"
            >
                <SheetHeader>
                    <SheetTitle className="text-lg font-black text-foreground">
                        Cotizar Evento o Espacio
                    </SheetTitle>
                    <SheetDescription className="text-xs text-muted-foreground">
                        Planifica tu evento en{' '}
                        <strong className="text-foreground">
                            {espacio.nombre}
                        </strong>
                        .
                    </SheetDescription>
                </SheetHeader>

                {isSubmitSuccessful ? (
                    <div className="flex flex-col items-center justify-center py-12 text-center">
                        <CheckCircle2 className="size-12 text-emerald-500" />
                        <h4 className="mt-3 text-base font-black text-foreground">
                            ¡Solicitud Generada con Éxito!
                        </h4>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Tu solicitud se enviará a través de WhatsApp para
                            darte atención personalizada y cotización inmediata.
                        </p>
                        <Button
                            type="button"
                            onClick={handleCerrar}
                            className="mt-6 rounded-full bg-foreground px-6 text-xs font-bold text-background"
                        >
                            Listo
                        </Button>
                    </div>
                ) : (
                    <form
                        onSubmit={manejarSubmit}
                        className="mt-6 flex flex-col gap-4"
                    >
                        <FieldGroup>
                            {/* Tipo de Evento */}
                            <Field>
                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                    Tipo de Evento
                                </FieldLabel>
                                <Input
                                    {...register('tipo_evento')}
                                    placeholder="Ej. Conferencia, Boda, Taller"
                                    className="h-9 text-xs"
                                    aria-invalid={!!errors.tipo_evento}
                                />
                                {errors.tipo_evento?.message && (
                                    <FieldError>
                                        {errors.tipo_evento.message}
                                    </FieldError>
                                )}
                            </Field>

                            {/* Nombre */}
                            <Field>
                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                    Nombre o Empresa
                                </FieldLabel>
                                <Input
                                    {...register('nombre')}
                                    placeholder="Ej. Juan Pérez / Corporación XYZ"
                                    className="h-9 text-xs"
                                    aria-invalid={!!errors.nombre}
                                />
                                {errors.nombre?.message && (
                                    <FieldError>
                                        {errors.nombre.message}
                                    </FieldError>
                                )}
                            </Field>

                            {/* Correo y Teléfono */}
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <Field>
                                    <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                        Correo Electrónico
                                    </FieldLabel>
                                    <Input
                                        type="email"
                                        {...register('email')}
                                        placeholder="contacto@ejemplo.com"
                                        className="h-9 text-xs"
                                        aria-invalid={!!errors.email}
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
                                        aria-invalid={!!errors.telefono}
                                    />
                                    {errors.telefono?.message && (
                                        <FieldError>
                                            {errors.telefono.message}
                                        </FieldError>
                                    )}
                                </Field>
                            </div>

                            {/* Fecha y Asistentes */}
                            <div className="grid grid-cols-2 gap-3">
                                <Field>
                                    <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                        Fecha Estimada
                                    </FieldLabel>
                                    <Input
                                        type="date"
                                        {...register('fecha')}
                                        className="h-9 text-xs"
                                        aria-invalid={!!errors.fecha}
                                    />
                                    {errors.fecha?.message && (
                                        <FieldError>
                                            {errors.fecha.message}
                                        </FieldError>
                                    )}
                                </Field>

                                <Field>
                                    <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                        N° Asistentes
                                    </FieldLabel>
                                    <Input
                                        type="number"
                                        min="1"
                                        {...register('asistentes')}
                                        className="h-9 text-xs"
                                        aria-invalid={!!errors.asistentes}
                                    />
                                    {errors.asistentes?.message && (
                                        <FieldError>
                                            {errors.asistentes.message}
                                        </FieldError>
                                    )}
                                </Field>
                            </div>

                            {/* Horario */}
                            <div className="grid grid-cols-2 gap-3">
                                <Field>
                                    <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                        Hora Inicio
                                    </FieldLabel>
                                    <Input
                                        type="time"
                                        {...register('hora_inicio')}
                                        className="h-9 text-xs"
                                        aria-invalid={!!errors.hora_inicio}
                                    />
                                    {errors.hora_inicio?.message && (
                                        <FieldError>
                                            {errors.hora_inicio.message}
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
                                        aria-invalid={!!errors.hora_fin}
                                    />
                                    {errors.hora_fin?.message && (
                                        <FieldError>
                                            {errors.hora_fin.message}
                                        </FieldError>
                                    )}
                                </Field>
                            </div>

                            {/* Checkbox Catering */}
                            <Field
                                orientation="horizontal"
                                className="cursor-pointer"
                            >
                                <Checkbox
                                    id="catering-sheet"
                                    checked={requiereCatering}
                                    onCheckedChange={(checked) =>
                                        setValue(
                                            'requiere_catering',
                                            checked === true,
                                        )
                                    }
                                />
                                <FieldLabel
                                    htmlFor="catering-sheet"
                                    className="cursor-pointer text-xs font-bold text-foreground"
                                >
                                    Deseo incluir servicio de alimentos / coffee
                                    break
                                </FieldLabel>
                            </Field>

                            {/* Notas */}
                            <Field>
                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                    Notas Adicionales (Opcional)
                                </FieldLabel>
                                <Textarea
                                    {...register('notas')}
                                    placeholder="Montaje en U, proyector, micrófonos inalámbricos..."
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
                            className="mt-2 flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-bugambilia-600 py-3 text-xs font-black text-white shadow-md hover:bg-bugambilia-700 active:scale-95 disabled:opacity-50"
                        >
                            {isSubmitting ? (
                                <Loader2 className="size-3.5 animate-spin" />
                            ) : (
                                <Send className="size-3.5" />
                            )}
                            <span>
                                {isSubmitting
                                    ? 'Enviando...'
                                    : 'Enviar Solicitud de Evento'}
                            </span>
                        </Button>
                    </form>
                )}
            </SheetContent>
        </Sheet>
    );
};

export default EspacioCotizacionSheet;
