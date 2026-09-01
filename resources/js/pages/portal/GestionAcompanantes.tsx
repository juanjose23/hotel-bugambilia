import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Plus, Trash2, Loader2, Save } from 'lucide-react';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import { useAcompanantesForm } from '@/modules/clientes/hooks/useAcompanantesForm';
import type { PortalReservaDetalleCompleto } from '@/modules/clientes/types';
import { Button, buttonVariants } from '@/modules/shared/components/ui/button';
import { Field, FieldLabel } from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/modules/shared/components/ui/select';

interface GestionAcompanantesProps {
    reserva: PortalReservaDetalleCompleto;
}

export const GestionAcompanantes = ({ reserva }: GestionAcompanantesProps) => {
    const {
        fields,
        append,
        remove,
        register,
        setValue,
        handleSubmit,
        isSubmitting,
    } = useAcompanantesForm({
        reservaId: reserva.id,
        acompanantesIniciales: reserva.acompanantes,
    });

    return (
        <PortalLayout>
            <Head>
                <title>{`Acompañantes — Reserva #${reserva.codigo_reserva}`}</title>
                <meta
                    name="description"
                    content="Registra a tus acompañantes para agilizar el check-in en Hotel Bugambilias."
                />
            </Head>

            <div className="mx-auto max-w-4xl space-y-8 p-5 sm:p-8 lg:p-10">
                {/* Header */}
                <div className="flex flex-wrap items-center justify-between gap-4 border-b border-border/60 pb-6">
                    <div className="flex items-center gap-3">
                        <Link
                            href={`/portal/reservas/${reserva.id}`}
                            className={buttonVariants({
                                variant: 'ghost',
                                size: 'icon',
                                className: 'rounded-xl',
                            })}
                        >
                            <ArrowLeft className="size-5" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="font-mono text-xs font-bold text-primary">
                                    Reserva #{reserva.codigo_reserva}
                                </span>
                                <span>·</span>
                                <span className="text-xs text-muted-foreground">
                                    {reserva.recurso.nombre}
                                </span>
                            </div>
                            <h1 className="mt-0.5 text-xl font-black text-foreground sm:text-2xl">
                                Registro de Acompañantes
                            </h1>
                        </div>
                    </div>
                </div>

                <div className="space-y-6 rounded-3xl border border-border/70 bg-card p-6 shadow-xs sm:p-8">
                    <div className="border-b border-border/40 pb-4">
                        <p className="text-xs leading-relaxed text-muted-foreground">
                            Ingresa los nombres y datos de las personas que se
                            hospedarán contigo en la suite para preparar sus
                            llaves y agilizar el check-in a tu llegada.
                        </p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-4">
                            {fields.map((field, index) => (
                                <div
                                    key={field.id}
                                    className="relative flex flex-col gap-4 rounded-2xl border border-border/60 bg-secondary/30 p-4 sm:flex-row sm:items-center sm:p-5"
                                >
                                    <div className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">
                                        {index + 1}
                                    </div>

                                    <div className="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-3">
                                        <Field className="space-y-1 sm:col-span-1">
                                            <FieldLabel className="text-xs font-semibold">
                                                Nombre Completo
                                            </FieldLabel>
                                            <Input
                                                {...register(
                                                    `acompanantes.${index}.nombre` as const,
                                                )}
                                                placeholder="Nombre del huésped"
                                                className="text-xs"
                                            />
                                        </Field>

                                        <Field className="space-y-1 sm:col-span-1">
                                            <FieldLabel className="text-xs font-semibold">
                                                Identificación / Cédula
                                            </FieldLabel>
                                            <Input
                                                {...register(
                                                    `acompanantes.${index}.identificacion` as const,
                                                )}
                                                placeholder="Opcional"
                                                className="text-xs"
                                            />
                                        </Field>

                                        <Field className="space-y-1 sm:col-span-1">
                                            <FieldLabel className="text-xs font-semibold">
                                                Tipo de Huésped
                                            </FieldLabel>
                                            <Select
                                                defaultValue={
                                                    field.tipo || 'adulto'
                                                }
                                                onValueChange={(
                                                    val:
                                                        | 'adulto'
                                                        | 'nino'
                                                        | 'bebe',
                                                ) =>
                                                    setValue(
                                                        `acompanantes.${index}.tipo` as const,
                                                        val,
                                                    )
                                                }
                                            >
                                                <SelectTrigger className="text-xs">
                                                    <SelectValue placeholder="Seleccionar" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="adulto">
                                                        Adulto
                                                    </SelectItem>
                                                    <SelectItem value="nino">
                                                        Niño
                                                    </SelectItem>
                                                    <SelectItem value="bebe">
                                                        Bebé
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </Field>
                                    </div>

                                    {fields.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => remove(index)}
                                            className="size-8 shrink-0 text-destructive hover:bg-destructive/10"
                                            title="Eliminar acompañante"
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    )}
                                </div>
                            ))}
                        </div>

                        <div className="flex flex-wrap items-center justify-between gap-4 border-t border-border/40 pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() =>
                                    append({
                                        nombre: '',
                                        identificacion: '',
                                        tipo: 'adulto',
                                    })
                                }
                                className="gap-1.5 rounded-xl text-xs font-bold"
                            >
                                <Plus className="size-3.5" />
                                <span>Agregar Otro Acompañante</span>
                            </Button>

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
                                <span>Guardar Acompañantes</span>
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </PortalLayout>
    );
};

GestionAcompanantes.layout = (page: React.ReactNode) => page;

export default GestionAcompanantes;
