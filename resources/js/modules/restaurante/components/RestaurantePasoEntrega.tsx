import {
    ArrowLeft,
    ArrowRight,
    Building2,
    Info,
    Mail,
    MapPin,
    Phone,
    Truck,
    User,
} from 'lucide-react';
import type {
    FieldErrors,
    UseFormRegister,
    UseFormSetValue,
} from 'react-hook-form';
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
import type { FormPedidoDelivery } from '../schemas/pedidoDeliverySchema';
import type { DepartamentoDelivery } from '../types';

const MensajeErrorCampo = ({ mensaje }: { mensaje?: string }) =>
    mensaje ? <FieldError className="text-[11px]">{mensaje}</FieldError> : null;

interface RestaurantePasoEntregaProps {
    register: UseFormRegister<FormPedidoDelivery>;
    setValue: UseFormSetValue<FormPedidoDelivery>;
    errors: FieldErrors<FormPedidoDelivery>;
    departamentos: DepartamentoDelivery[];
    municipios: string[];
    departamentoSeleccionado: string;
    municipioSeleccionado: string;
    costoEnvio: number;
    moneda?: string;
    alCambiarDepartamento: (nombre: string) => void;
    alAvanzar: () => void;
    alRetroceder: () => void;
}

export const RestaurantePasoEntrega = ({
    register,
    setValue,
    errors,
    departamentos,
    municipios,
    departamentoSeleccionado,
    municipioSeleccionado,
    costoEnvio,
    moneda = 'C$',
    alCambiarDepartamento,
    alAvanzar,
    alRetroceder,
}: RestaurantePasoEntregaProps) => {
    return (
        <div className="space-y-6">
            <div className="rounded-3xl border border-border/80 bg-card p-6 shadow-xs">
                <div className="border-b border-border/60 pb-4">
                    <div className="flex items-center gap-2">
                        <div className="flex size-7 items-center justify-center rounded-xl bg-primary/10 text-primary dark:text-rose-400">
                            <MapPin className="size-4" />
                        </div>
                        <h2 className="text-lg font-black text-foreground">
                            2. Datos de Contacto y Dirección de Entrega
                        </h2>
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                        Indica tu información para la entrega express,
                        confirmación y recibo de pago.
                    </p>
                </div>

                <div className="mt-6 space-y-5">
                    <div className="flex items-start gap-2.5 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-3.5 text-xs text-emerald-800 dark:text-emerald-300">
                        <Truck className="mt-0.5 size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                        <div>
                            <p className="font-bold">
                                Cobertura Activa de Delivery
                            </p>
                            <p className="mt-0.5 text-[11px] text-muted-foreground">
                                Entregas garantizadas en{' '}
                                {departamentoSeleccionado || 'Estelí'} y
                                municipios autorizados. Tarifa de envío:{' '}
                                <strong className="text-foreground">
                                    {moneda} {costoEnvio.toFixed(2)}
                                </strong>
                                .
                            </p>
                        </div>
                    </div>

                    <FieldGroup className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field className="sm:col-span-2">
                            <FieldLabel className="flex items-center gap-1.5 text-xs font-bold">
                                <User className="size-3.5 text-muted-foreground" />
                                <span>Nombre Completo *</span>
                            </FieldLabel>
                            <Input
                                placeholder="Ej: María Sánchez"
                                className="h-10 rounded-xl text-xs"
                                {...register('nombre')}
                            />
                            <MensajeErrorCampo
                                mensaje={errors.nombre?.message}
                            />
                        </Field>

                        <Field className="sm:col-span-1">
                            <FieldLabel className="flex items-center gap-1.5 text-xs font-bold">
                                <Mail className="size-3.5 text-muted-foreground" />
                                <span>Correo Electrónico *</span>
                            </FieldLabel>
                            <Input
                                type="email"
                                placeholder="Ej: maria.sanchez@gmail.com"
                                className="h-10 rounded-xl text-xs"
                                {...register('email')}
                            />
                            <MensajeErrorCampo
                                mensaje={errors.email?.message}
                            />
                        </Field>

                        <Field className="sm:col-span-1">
                            <FieldLabel className="flex items-center gap-1.5 text-xs font-bold">
                                <Phone className="size-3.5 text-muted-foreground" />
                                <span>Teléfono / WhatsApp *</span>
                            </FieldLabel>
                            <Input
                                placeholder="Ej: +505 8888 8888"
                                className="h-10 rounded-xl text-xs"
                                {...register('telefono')}
                            />
                            <MensajeErrorCampo
                                mensaje={errors.telefono?.message}
                            />
                        </Field>

                        <Field className="sm:col-span-1">
                            <FieldLabel className="flex items-center gap-1.5 text-xs font-bold">
                                <Building2 className="size-3.5 text-muted-foreground" />
                                <span>Departamento *</span>
                            </FieldLabel>
                            <Select
                                value={departamentoSeleccionado}
                                onValueChange={alCambiarDepartamento}
                            >
                                <SelectTrigger className="h-10 w-full rounded-xl text-xs">
                                    <SelectValue placeholder="Selecciona departamento" />
                                </SelectTrigger>
                                <SelectContent>
                                    {departamentos.map((dep) => (
                                        <SelectItem
                                            key={dep.codigo}
                                            value={dep.nombre}
                                        >
                                            {dep.nombre}{' '}
                                            {dep.activo
                                                ? ''
                                                : '(No disponible)'}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <MensajeErrorCampo
                                mensaje={errors.departamento?.message}
                            />
                        </Field>

                        <Field className="sm:col-span-1">
                            <FieldLabel className="flex items-center gap-1.5 text-xs font-bold">
                                <MapPin className="size-3.5 text-muted-foreground" />
                                <span>Municipio / Zona *</span>
                            </FieldLabel>
                            <Select
                                value={municipioSeleccionado}
                                onValueChange={(val) =>
                                    setValue('municipio', val, {
                                        shouldValidate: true,
                                    })
                                }
                            >
                                <SelectTrigger className="h-10 w-full rounded-xl text-xs">
                                    <SelectValue placeholder="Selecciona municipio" />
                                </SelectTrigger>
                                <SelectContent>
                                    {municipios.map((mun) => (
                                        <SelectItem key={mun} value={mun}>
                                            {mun}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <MensajeErrorCampo
                                mensaje={errors.municipio?.message}
                            />
                        </Field>

                        <Field className="sm:col-span-2">
                            <FieldLabel className="flex items-center gap-1.5 text-xs font-bold">
                                <MapPin className="size-3.5 text-muted-foreground" />
                                <span>Dirección Exacta de Entrega *</span>
                            </FieldLabel>
                            <Input
                                placeholder="Ej: Del Parque Central 2c al norte, 1c al oeste, casa esquinera color blanco #14"
                                className="h-10 rounded-xl text-xs"
                                {...register('direccion')}
                            />
                            <MensajeErrorCampo
                                mensaje={errors.direccion?.message}
                            />
                            <p className="mt-1 flex items-center gap-1 text-[10px] text-muted-foreground">
                                <Info className="size-3" />
                                <span>
                                    Sé lo más detallado posible con puntos de
                                    referencia para facilitar la entrega al
                                    repartidor.
                                </span>
                            </p>
                        </Field>

                        <Field className="sm:col-span-2">
                            <FieldLabel className="text-xs font-bold">
                                Instrucciones Especiales de Entrega (Opcional)
                            </FieldLabel>
                            <Textarea
                                placeholder="Ej: Tocar el timbre dos veces, dejar en recepción del edificio, etc."
                                className="min-h-[65px] rounded-xl text-xs"
                                {...register('notas')}
                            />
                        </Field>
                    </FieldGroup>
                </div>

                <div className="mt-8 flex items-center justify-between border-t border-border/60 pt-5">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={alRetroceder}
                        className="h-11 cursor-pointer gap-2 rounded-2xl px-5 text-xs font-bold"
                    >
                        <ArrowLeft className="size-4" />
                        <span>Atrás (Artículos)</span>
                    </Button>

                    <Button
                        type="button"
                        onClick={alAvanzar}
                        className="h-11 cursor-pointer gap-2 rounded-2xl px-6 text-xs font-bold shadow-md"
                    >
                        <span>Continuar a Método de Pago</span>
                        <ArrowRight className="size-4" />
                    </Button>
                </div>
            </div>
        </div>
    );
};

export default RestaurantePasoEntrega;
