import { Mail, MessageSquare, Phone, User } from 'lucide-react';
import type { FieldErrors, UseFormRegister } from 'react-hook-form';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import { Textarea } from '@/modules/shared/components/ui/textarea';
import type { FormReservaMesa } from '../../schemas/reservaMesaSchema';
import { RestauranteOcasionesRapidas } from './RestauranteOcasionesRapidas';

interface RestauranteCamposContactoReservaProps {
    register: UseFormRegister<FormReservaMesa>;
    errors: FieldErrors<FormReservaMesa>;
    alAgregarNotaRapida: (tag: string) => void;
    variante?: 'pagina' | 'modal';
}

export const RestauranteCamposContactoReserva = ({
    register,
    errors,
    alAgregarNotaRapida,
    variante = 'pagina',
}: RestauranteCamposContactoReservaProps) => {
    const esModal = variante === 'modal';
    const etiquetaBase = esModal ? 'text-[11px]' : 'text-xs';
    const tamanoInput = esModal ? 'h-10' : 'h-11';

    return (
        <FieldGroup className="space-y-3">
            <Field>
                <FieldLabel
                    className={`${etiquetaBase} flex items-center gap-1.5 font-bold`}
                >
                    {esModal && <User className="size-3.5 text-primary" />}
                    Nombre Completo *
                </FieldLabel>
                <Input
                    placeholder={
                        esModal ? 'Ej: Carlos Mendoza' : 'Ej. Juan Pérez'
                    }
                    className={`${tamanoInput} rounded-xl text-xs`}
                    {...register('nombre_cliente')}
                />
                {errors.nombre_cliente && (
                    <FieldError className="text-[10px]">
                        {errors.nombre_cliente.message}
                    </FieldError>
                )}
            </Field>

            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <Field>
                    <FieldLabel
                        className={`${etiquetaBase} flex items-center gap-1.5 font-bold`}
                    >
                        {esModal && <Phone className="size-3.5 text-primary" />}
                        Teléfono / WhatsApp *
                    </FieldLabel>
                    <Input
                        placeholder={
                            esModal
                                ? 'Ej: +505 8888 8888'
                                : 'Ej. +505 8888 8888'
                        }
                        className={`${tamanoInput} rounded-xl text-xs`}
                        {...register('telefono_cliente')}
                    />
                    {errors.telefono_cliente && (
                        <FieldError className="text-[10px]">
                            {errors.telefono_cliente.message}
                        </FieldError>
                    )}
                </Field>

                <Field>
                    <FieldLabel
                        className={`${etiquetaBase} flex items-center gap-1.5 font-bold`}
                    >
                        {esModal && <Mail className="size-3.5 text-primary" />}
                        Correo Electrónico (Opcional)
                    </FieldLabel>
                    <Input
                        type="email"
                        placeholder={
                            esModal
                                ? 'correo@ejemplo.com'
                                : 'ejemplo@correo.com'
                        }
                        className={`${tamanoInput} rounded-xl text-xs`}
                        {...register('email_cliente')}
                    />
                    {errors.email_cliente && (
                        <FieldError className="text-[10px]">
                            {errors.email_cliente.message}
                        </FieldError>
                    )}
                </Field>
            </div>

            <Field>
                <FieldLabel
                    className={`${etiquetaBase} flex items-center gap-1.5 font-bold`}
                >
                    {esModal && (
                        <MessageSquare className="size-3.5 text-primary" />
                    )}
                    {esModal
                        ? 'Petición Especial u Ocasión (Opcional)'
                        : 'Notas u Ocasión Especial'}
                </FieldLabel>
                <RestauranteOcasionesRapidas
                    alAgregar={alAgregarNotaRapida}
                    variante={variante}
                />
                <Textarea
                    rows={3}
                    placeholder={
                        esModal
                            ? 'Ej: Aniversario, mesa con buena vista, preferencia cerca a la terraza...'
                            : 'Indícanos si celebras algún evento especial o tienes alguna preferencia...'
                    }
                    className={`${esModal ? 'min-h-[50px]' : ''} rounded-xl text-xs`}
                    {...register('notas')}
                />
            </Field>
        </FieldGroup>
    );
};

export default RestauranteCamposContactoReserva;
