import { User, Mail, Phone, Sparkles } from 'lucide-react';
import type { UseFormRegister, FieldErrors } from 'react-hook-form';
import {
    Field,
    FieldLabel,
    FieldError,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import type { CrearReservaFormValues } from '../schemas/crearReservaSchema';
import type { BeneficioClienteItem } from '../types';
import { BeneficiosClienteBadge } from './BeneficiosClienteBadge';

interface ReservaPasoHuespedProps {
    beneficiosCliente?: BeneficioClienteItem[];
    register: UseFormRegister<CrearReservaFormValues>;
    errors: FieldErrors<CrearReservaFormValues>;
}

export const ReservaPasoHuesped = ({
    beneficiosCliente = [],
    register,
    errors,
}: ReservaPasoHuespedProps) => {
    return (
        <div className="animate-in fade-in space-y-6 duration-200">
            <div className="space-y-5 rounded-3xl border border-border bg-card p-6 shadow-sm">
                <div>
                    <h2 className="text-lg font-black tracking-tight text-foreground">
                        Información del Huésped Principal
                    </h2>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        Enviaremos los detalles de confirmación y factura
                        electrónica a estos contactos.
                    </p>
                </div>

                {/* Beneficios de Cliente si está logueado */}
                {beneficiosCliente && beneficiosCliente.length > 0 && (
                    <div className="rounded-2xl border border-primary/20 bg-primary/5 p-4 dark:bg-rose-950/20">
                        <div className="flex items-center gap-2 text-xs font-black text-primary dark:text-rose-300">
                            <Sparkles className="size-4" />
                            <span>Beneficios aplicables para tu perfil:</span>
                        </div>
                        <div className="mt-2.5 flex flex-wrap gap-2">
                            {beneficiosCliente.map((b) => (
                                <BeneficiosClienteBadge
                                    key={b.id}
                                    beneficio={b}
                                />
                            ))}
                        </div>
                    </div>
                )}

                <div className="space-y-4">
                    <Field>
                        <FieldLabel className="text-xs font-bold">
                            Nombre Completo
                        </FieldLabel>
                        <div className="relative">
                            <User className="absolute top-3.5 left-3.5 size-4 text-muted-foreground" />
                            <Input
                                type="text"
                                placeholder="Ej. Carlos Mendoza"
                                {...register('nombre_cliente')}
                                className="h-11 rounded-2xl bg-background pl-10 text-xs font-bold"
                            />
                        </div>
                        {errors.nombre_cliente && (
                            <FieldError>
                                {errors.nombre_cliente.message}
                            </FieldError>
                        )}
                    </Field>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel className="text-xs font-bold">
                                Correo Electrónico
                            </FieldLabel>
                            <div className="relative">
                                <Mail className="absolute top-3.5 left-3.5 size-4 text-muted-foreground" />
                                <Input
                                    type="email"
                                    placeholder="carlos@correo.com"
                                    {...register('email_cliente')}
                                    className="h-11 rounded-2xl bg-background pl-10 text-xs font-bold"
                                />
                            </div>
                            {errors.email_cliente && (
                                <FieldError>
                                    {errors.email_cliente.message}
                                </FieldError>
                            )}
                        </Field>

                        <Field>
                            <FieldLabel className="text-xs font-bold">
                                Teléfono / WhatsApp
                            </FieldLabel>
                            <div className="relative">
                                <Phone className="absolute top-3.5 left-3.5 size-4 text-muted-foreground" />
                                <Input
                                    type="tel"
                                    placeholder="+505 8888 8888"
                                    {...register('telefono_cliente')}
                                    className="h-11 rounded-2xl bg-background pl-10 text-xs font-bold"
                                />
                            </div>
                            {errors.telefono_cliente && (
                                <FieldError>
                                    {errors.telefono_cliente.message}
                                </FieldError>
                            )}
                        </Field>
                    </div>

                    <Field>
                        <FieldLabel className="text-xs font-bold">
                            Peticiones Especiales (Opcional)
                        </FieldLabel>
                        <Input
                            type="text"
                            placeholder="Ej. Cama adicional, llegada tardía, piso alto..."
                            {...register('notas')}
                            className="h-11 rounded-2xl bg-background text-xs font-bold"
                        />
                    </Field>
                </div>
            </div>
        </div>
    );
};

export default ReservaPasoHuesped;
