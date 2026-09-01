import { Send, CheckCircle2, Loader2 } from 'lucide-react';
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

    const handleCerrar = () => {
        reset();
        alCerrar();
    };

    return (
        <Sheet open={abierto} onOpenChange={handleCerrar}>
            <SheetContent side="right" className="w-full font-sans sm:max-w-md">
                <SheetHeader>
                    <SheetTitle className="text-lg font-black text-foreground">
                        Consultar Servicio
                    </SheetTitle>
                    <SheetDescription className="text-xs text-muted-foreground">
                        Completa tus datos para solicitar información o
                        cotización personalizada para{' '}
                        <strong className="text-foreground">
                            {servicio.nombre}
                        </strong>
                        .
                    </SheetDescription>
                </SheetHeader>

                {isSubmitSuccessful ? (
                    <div className="flex flex-col items-center justify-center py-12 text-center">
                        <CheckCircle2 className="size-12 text-emerald-500" />
                        <h4 className="mt-3 text-base font-black text-foreground">
                            ¡Consulta Generada con Éxito!
                        </h4>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Se abrirá WhatsApp para enviar tu solicitud
                            directamente al concierge de Hotel Bugambilias.
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
                            {/* Nombre */}
                            <Field>
                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                    Nombre Completo
                                </FieldLabel>
                                <Input
                                    {...register('nombre')}
                                    placeholder="Ej. Juan Pérez"
                                    className="h-9 text-xs"
                                    aria-invalid={!!errors.nombre}
                                />
                                {errors.nombre?.message && (
                                    <FieldError>
                                        {errors.nombre.message}
                                    </FieldError>
                                )}
                            </Field>

                            {/* Correo Electrónico */}
                            <Field>
                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                    Correo Electrónico
                                </FieldLabel>
                                <Input
                                    type="email"
                                    {...register('email')}
                                    placeholder="ejemplo@correo.com"
                                    className="h-9 text-xs"
                                    aria-invalid={!!errors.email}
                                />
                                {errors.email?.message && (
                                    <FieldError>
                                        {errors.email.message}
                                    </FieldError>
                                )}
                            </Field>

                            {/* Teléfono */}
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

                            {/* Fecha y Personas */}
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
                                        N° Personas
                                    </FieldLabel>
                                    <Input
                                        type="number"
                                        min="1"
                                        {...register('personas')}
                                        className="h-9 text-xs"
                                        aria-invalid={!!errors.personas}
                                    />
                                    {errors.personas?.message && (
                                        <FieldError>
                                            {errors.personas.message}
                                        </FieldError>
                                    )}
                                </Field>
                            </div>

                            {/* Notas */}
                            <Field>
                                <FieldLabel className="text-[11px] font-black tracking-wider text-muted-foreground uppercase">
                                    Requerimientos Especiales (Opcional)
                                </FieldLabel>
                                <Textarea
                                    {...register('notas')}
                                    placeholder="Describe cualquier detalle adicional..."
                                    rows={3}
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
                                    : 'Enviar Consulta al Concierge'}
                            </span>
                        </Button>
                    </form>
                )}
            </SheetContent>
        </Sheet>
    );
};

export default ServicioConsultaSheet;
