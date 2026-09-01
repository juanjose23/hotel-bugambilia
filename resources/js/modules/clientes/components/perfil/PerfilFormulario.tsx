import { Loader2, Save } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Field,
    FieldLabel,
    FieldError,
    FieldGroup,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import { usePerfilClienteForm } from '../../hooks/usePerfilClienteForm';
import type { ClienteProfile } from '../../types';

interface PerfilFormularioProps {
    cliente: ClienteProfile;
}

export const PerfilFormulario = ({ cliente }: PerfilFormularioProps) => {
    const { register, handleSubmit, isSubmitting, errors } =
        usePerfilClienteForm({ cliente });

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <FieldGroup className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <Field className="space-y-1.5 sm:col-span-2">
                    <FieldLabel htmlFor="nombre">Nombre Completo</FieldLabel>
                    <Input
                        id="nombre"
                        {...register('nombre')}
                        placeholder="Nombre y Apellidos"
                        aria-invalid={!!errors.nombre}
                    />
                    {errors.nombre && (
                        <FieldError>{errors.nombre.message}</FieldError>
                    )}
                </Field>

                <Field className="space-y-1.5">
                    <FieldLabel htmlFor="email">Correo Electrónico</FieldLabel>
                    <Input
                        id="email"
                        type="email"
                        {...register('email')}
                        placeholder="correo@ejemplo.com"
                        aria-invalid={!!errors.email}
                    />
                    {errors.email && (
                        <FieldError>{errors.email.message}</FieldError>
                    )}
                </Field>

                <Field className="space-y-1.5">
                    <FieldLabel htmlFor="telefono">
                        Teléfono / WhatsApp
                    </FieldLabel>
                    <Input
                        id="telefono"
                        {...register('telefono')}
                        placeholder="+505 8888 8888"
                        aria-invalid={!!errors.telefono}
                    />
                    {errors.telefono && (
                        <FieldError>{errors.telefono.message}</FieldError>
                    )}
                </Field>

                <Field className="space-y-1.5 sm:col-span-2">
                    <FieldLabel htmlFor="identificacion">
                        Cédula o Pasaporte
                    </FieldLabel>
                    <Input
                        id="identificacion"
                        {...register('identificacion')}
                        placeholder="Número de identificación oficial"
                        aria-invalid={!!errors.identificacion}
                    />
                    {errors.identificacion && (
                        <FieldError>{errors.identificacion.message}</FieldError>
                    )}
                </Field>
            </FieldGroup>

            <div className="flex justify-end border-t border-border/50 pt-4">
                <Button
                    type="submit"
                    disabled={isSubmitting}
                    className="gap-2 rounded-2xl px-8 font-bold shadow-md shadow-primary/20"
                >
                    {isSubmitting ? (
                        <Loader2 className="size-4 animate-spin" />
                    ) : (
                        <Save className="size-4" />
                    )}
                    <span>Guardar Cambios</span>
                </Button>
            </div>
        </form>
    );
};
