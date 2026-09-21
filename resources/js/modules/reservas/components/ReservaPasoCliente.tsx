import {
    User,
    Mail,
    Phone,
    Sparkles,
    Gift,
    Tag,
    Clock,
    ArrowUpCircle,
    MessageSquare,
} from 'lucide-react';
import type { FieldErrors, UseFormRegister } from 'react-hook-form';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Field,
    FieldLabel,
    FieldError,
    FieldGroup,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import { Textarea } from '@/modules/shared/components/ui/textarea';
import type { BeneficioClienteItem } from '../types';

interface BeneficioBadgeProps {
    beneficio: BeneficioClienteItem;
    className?: string;
}

const renderIconoBeneficio = (tipo?: string) => {
    switch (tipo) {
        case 'descuento_reserva':
        case 'descuento_restaurante':
            return <Tag className="size-3.5 shrink-0" />;
        case 'anticipo_reducido':
            return <Sparkles className="size-3.5 shrink-0" />;
        case 'late_checkout':
            return <Clock className="size-3.5 shrink-0" />;
        case 'upgrade_habitacion':
            return <ArrowUpCircle className="size-3.5 shrink-0" />;
        case 'cortesia':
        default:
            return <Gift className="size-3.5 shrink-0" />;
    }
};

const BeneficiosClienteBadge = ({
    beneficio,
    className = '',
}: BeneficioBadgeProps) => {
    const textoBeneficio = () => {
        if (beneficio.nombre) {
            return beneficio.nombre;
        }

        if (beneficio.tipo === 'descuento_reserva') {
            return beneficio.es_porcentaje
                ? `${beneficio.valor}% Descuento VIP`
                : `$${beneficio.valor} Descuento VIP`;
        }

        if (beneficio.tipo === 'anticipo_reducido') {
            return 'Abono Flexible / Sin Pago Previo';
        }

        if (beneficio.tipo === 'late_checkout') {
            return 'Late Check-out de Cortesía';
        }

        if (beneficio.tipo === 'upgrade_habitacion') {
            return 'Upgrade de Suite Garantizado';
        }

        return 'Beneficio Exclusivo';
    };

    return (
        <div
            className={`inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-black text-amber-700 dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-300 ${className}`}
        >
            {renderIconoBeneficio(beneficio.tipo)}
            <span>{textoBeneficio()}</span>
        </div>
    );
};

export interface ReservaPasoClienteProps {
    register: UseFormRegister<any>;

    errors: FieldErrors<any>;
    beneficiosCliente?: BeneficioClienteItem[];
    ocasionesRapidas?: readonly string[];
    alAgregarNotaRapida?: (tag: string) => void;
    titulo?: string;
    subtitulo?: string;
    variante?: 'pagina' | 'modal';
    mostrarPeticionesEspeciales?: boolean;
    placeholderNotas?: string;
}

export const ReservaPasoCliente = ({
    register,
    errors,
    beneficiosCliente = [],
    ocasionesRapidas = [],
    alAgregarNotaRapida,
    titulo = 'Información del Huésped / Titular',
    subtitulo = 'Enviaremos los detalles de confirmación y seguimiento a estos contactos.',
    variante = 'pagina',
    mostrarPeticionesEspeciales = true,
    placeholderNotas = 'Indícanos si tienes alguna preferencia especial, ocasión o requerimiento...',
}: ReservaPasoClienteProps) => {
    const esModal = variante === 'modal';
    const etiquetaClase = esModal
        ? 'text-[11px] font-bold'
        : 'text-xs font-bold';
    const inputClase = esModal
        ? 'h-10 rounded-xl text-xs font-bold'
        : 'h-11 rounded-2xl bg-background pl-10 text-xs font-bold';

    return (
        <div className="animate-in fade-in space-y-6 duration-200">
            <div
                className={`space-y-5 rounded-3xl border border-border bg-card shadow-sm ${
                    esModal ? 'p-4' : 'p-6'
                }`}
            >
                <div>
                    <h2
                        className={`font-black tracking-tight text-foreground ${
                            esModal ? 'text-base' : 'text-lg'
                        }`}
                    >
                        {titulo}
                    </h2>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        {subtitulo}
                    </p>
                </div>

                {/* Beneficios VIP si existen */}
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

                <FieldGroup className="space-y-4">
                    {/* Nombre Completo */}
                    <Field>
                        <FieldLabel className={etiquetaClase}>
                            Nombre Completo *
                        </FieldLabel>
                        <div className="relative">
                            {!esModal && (
                                <User className="absolute top-3.5 left-3.5 size-4 text-muted-foreground" />
                            )}
                            <Input
                                type="text"
                                placeholder="Ej. Carlos Mendoza"
                                {...register('nombre_cliente')}
                                className={inputClase}
                            />
                        </div>
                        {errors.nombre_cliente && (
                            <FieldError className="text-[10px]">
                                {String(
                                    errors.nombre_cliente.message ||
                                        'Nombre requerido',
                                )}
                            </FieldError>
                        )}
                    </Field>

                    {/* Correo y Teléfono en grid */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel className={etiquetaClase}>
                                Teléfono / WhatsApp *
                            </FieldLabel>
                            <div className="relative">
                                {!esModal && (
                                    <Phone className="absolute top-3.5 left-3.5 size-4 text-muted-foreground" />
                                )}
                                <Input
                                    type="tel"
                                    placeholder="+505 8888 8888"
                                    {...register('telefono_cliente')}
                                    className={inputClase}
                                />
                            </div>
                            {errors.telefono_cliente && (
                                <FieldError className="text-[10px]">
                                    {String(
                                        errors.telefono_cliente.message ||
                                            'Teléfono requerido',
                                    )}
                                </FieldError>
                            )}
                        </Field>

                        <Field>
                            <FieldLabel className={etiquetaClase}>
                                Correo Electrónico
                            </FieldLabel>
                            <div className="relative">
                                {!esModal && (
                                    <Mail className="absolute top-3.5 left-3.5 size-4 text-muted-foreground" />
                                )}
                                <Input
                                    type="email"
                                    placeholder="carlos@correo.com"
                                    {...register('email_cliente')}
                                    className={inputClase}
                                />
                            </div>
                            {errors.email_cliente && (
                                <FieldError className="text-[10px]">
                                    {String(
                                        errors.email_cliente.message ||
                                            'Correo inválido',
                                    )}
                                </FieldError>
                            )}
                        </Field>
                    </div>

                    {/* Peticiones / Ocasiones Especiales */}
                    {mostrarPeticionesEspeciales && (
                        <Field>
                            <FieldLabel
                                className={`${etiquetaClase} flex items-center gap-1.5`}
                            >
                                <MessageSquare className="size-3.5 text-primary" />
                                <span>
                                    Peticiones u Ocasión Especial (Opcional)
                                </span>
                            </FieldLabel>

                            {/* Tags rápidos de ocasión */}
                            {ocasionesRapidas.length > 0 &&
                                alAgregarNotaRapida && (
                                    <div className="mb-2 flex flex-wrap gap-1.5 pt-1">
                                        {ocasionesRapidas.map((tag) => (
                                            <Button
                                                key={tag}
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() =>
                                                    alAgregarNotaRapida(tag)
                                                }
                                                className="h-auto cursor-pointer rounded-lg border border-border/50 bg-muted/60 px-2.5 py-1 text-[11px] font-semibold text-muted-foreground hover:bg-muted hover:text-foreground"
                                            >
                                                + {tag}
                                            </Button>
                                        ))}
                                    </div>
                                )}

                            {esModal || ocasionesRapidas.length > 0 ? (
                                <Textarea
                                    rows={3}
                                    placeholder={placeholderNotas}
                                    {...register('notas')}
                                    className="rounded-xl text-xs"
                                />
                            ) : (
                                <Input
                                    type="text"
                                    placeholder={placeholderNotas}
                                    {...register('notas')}
                                    className="h-11 rounded-2xl bg-background text-xs font-bold"
                                />
                            )}
                            {errors.notas && (
                                <FieldError className="text-[10px]">
                                    {String(errors.notas.message || '')}
                                </FieldError>
                            )}
                        </Field>
                    )}
                </FieldGroup>
            </div>
        </div>
    );
};

export default ReservaPasoCliente;
