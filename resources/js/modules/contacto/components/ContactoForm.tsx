import { Loader2, Send, CheckCircle2, MessageCircle } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/modules/shared/components/ui/select';
import { Textarea } from '@/modules/shared/components/ui/textarea';
import { useContactoForm } from '../hooks/useContactoForm';

const MOTIVOS_CONTACTO = [
    { valor: 'Consulta General', etiqueta: 'Consulta General / Información' },
    { valor: 'Reserva Habitaciones', etiqueta: 'Reserva de Habitaciones' },
    {
        valor: 'Eventos y Espacios',
        etiqueta: 'Cotización de Eventos o Salones',
    },
    { valor: 'Restaurante y Banquetes', etiqueta: 'Restaurante y Banquetes' },
    {
        valor: 'Convenio Corporativo',
        etiqueta: 'Tarifas Corporativas / Empresas',
    },
];

export const ContactoForm = () => {
    const {
        register,
        handleSubmit,
        setValue,
        watch,
        errors,
        isSubmitting,
        isSubmitSuccessful,
    } = useContactoForm();

    const asuntoSeleccionado = watch('asunto');
    const urlWhatsAppSession =
        typeof window !== 'undefined'
            ? window.sessionStorage.getItem('ultimo_mensaje_contacto')
            : null;

    if (isSubmitSuccessful) {
        return (
            <div className="flex flex-col items-center justify-center rounded-3xl border border-emerald-500/30 bg-emerald-500/5 p-8 text-center sm:p-12">
                <div className="flex size-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                    <CheckCircle2 className="size-8" />
                </div>
                <h3 className="mt-4 text-lg font-black text-foreground sm:text-xl">
                    ¡Gracias por comunicarte con nosotros!
                </h3>
                <p className="mt-2 max-w-md text-xs leading-relaxed text-muted-foreground sm:text-sm">
                    Hemos recibido tu mensaje. Nuestro equipo de recepción en
                    Estelí te responderá al correo o teléfono proporcionado a la
                    brevedad.
                </p>

                {urlWhatsAppSession && (
                    <div className="mt-6">
                        <a
                            href={urlWhatsAppSession}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-6 py-2.5 text-xs font-bold text-white shadow-md hover:bg-emerald-700 active:scale-95"
                        >
                            <MessageCircle className="size-4" />
                            <span>Enviar copia por WhatsApp</span>
                        </a>
                    </div>
                )}
            </div>
        );
    }

    return (
        <div className="rounded-3xl border border-border bg-card p-6 shadow-sm sm:p-8">
            <div>
                <h2 className="text-lg font-black text-foreground sm:text-xl">
                    Envíanos un Mensaje Directo
                </h2>
                <p className="mt-1 text-xs text-muted-foreground">
                    Completa el formulario y te responderemos en menos de 24
                    horas.
                </p>
            </div>

            <form onSubmit={handleSubmit} className="mt-6 space-y-4">
                <FieldGroup className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {/* Nombre Completo */}
                    <Field>
                        <FieldLabel htmlFor="nombre_completo">
                            Nombre Completo *
                        </FieldLabel>
                        <Input
                            id="nombre_completo"
                            placeholder="Ej. María Gutiérrez"
                            {...register('nombre_completo')}
                            aria-invalid={Boolean(errors.nombre_completo)}
                            className="h-10 text-xs sm:text-sm"
                        />
                        {errors.nombre_completo && (
                            <FieldError>
                                {errors.nombre_completo.message}
                            </FieldError>
                        )}
                    </Field>

                    {/* Correo Electrónico */}
                    <Field>
                        <FieldLabel htmlFor="email">
                            Correo Electrónico *
                        </FieldLabel>
                        <Input
                            id="email"
                            type="email"
                            placeholder="maria@ejemplo.com"
                            {...register('email')}
                            aria-invalid={Boolean(errors.email)}
                            className="h-10 text-xs sm:text-sm"
                        />
                        {errors.email && (
                            <FieldError>{errors.email.message}</FieldError>
                        )}
                    </Field>
                </FieldGroup>

                <FieldGroup className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {/* Teléfono */}
                    <Field>
                        <FieldLabel htmlFor="telefono">
                            Teléfono / WhatsApp *
                        </FieldLabel>
                        <Input
                            id="telefono"
                            type="tel"
                            placeholder="+505 8888 8888"
                            {...register('telefono')}
                            aria-invalid={Boolean(errors.telefono)}
                            className="h-10 text-xs sm:text-sm"
                        />
                        {errors.telefono && (
                            <FieldError>{errors.telefono.message}</FieldError>
                        )}
                    </Field>

                    {/* Motivo / Asunto */}
                    <Field>
                        <FieldLabel htmlFor="asunto">Motivo *</FieldLabel>
                        <Select
                            value={asuntoSeleccionado}
                            onValueChange={(val) =>
                                setValue('asunto', val, {
                                    shouldValidate: true,
                                })
                            }
                        >
                            <SelectTrigger
                                id="asunto"
                                className="h-10 text-xs sm:text-sm"
                            >
                                <SelectValue placeholder="Selecciona un motivo" />
                            </SelectTrigger>
                            <SelectContent>
                                {MOTIVOS_CONTACTO.map((m) => (
                                    <SelectItem key={m.valor} value={m.valor}>
                                        {m.etiqueta}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.asunto && (
                            <FieldError>{errors.asunto.message}</FieldError>
                        )}
                    </Field>
                </FieldGroup>

                {/* Mensaje */}
                <Field>
                    <FieldLabel htmlFor="mensaje">
                        Mensaje o Consulta *
                    </FieldLabel>
                    <Textarea
                        id="mensaje"
                        rows={4}
                        placeholder="Escribe aquí los detalles de tu consulta, fechas deseadas o requerimientos especiales..."
                        {...register('mensaje')}
                        aria-invalid={Boolean(errors.mensaje)}
                        className="text-xs sm:text-sm"
                    />
                    {errors.mensaje && (
                        <FieldError>{errors.mensaje.message}</FieldError>
                    )}
                </Field>

                {/* Botón de Envío */}
                <div className="pt-2">
                    <Button
                        type="submit"
                        disabled={isSubmitting}
                        className="w-full cursor-pointer rounded-full bg-primary py-2.5 text-xs font-bold text-primary-foreground shadow-md transition-all hover:bg-primary/90 active:scale-95 sm:w-auto sm:px-8"
                    >
                        {isSubmitting ? (
                            <>
                                <Loader2 className="size-4 animate-spin" />
                                <span>Enviando mensaje...</span>
                            </>
                        ) : (
                            <>
                                <Send className="size-4" />
                                <span>Enviar Mensaje</span>
                            </>
                        )}
                    </Button>
                </div>
            </form>
        </div>
    );
};

export default ContactoForm;
