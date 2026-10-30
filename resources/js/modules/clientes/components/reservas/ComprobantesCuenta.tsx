import { Loader2, Paperclip } from 'lucide-react';
import { Controller, useWatch } from 'react-hook-form';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Field,
    FieldLabel,
    FieldError,
} from '@/modules/shared/components/ui/field';
import { Input } from '@/modules/shared/components/ui/input';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/modules/shared/components/ui/select';
import { useComprobanteForm } from '../../hooks/useComprobanteForm';

export interface CuentaComprobantes {
    id: number;
    numero: string;
    saldo: number;
    moneda_id: number;
    moneda: string;
}
export interface BancoComprobante {
    id: number;
    nombre: string;
    banco: string | null;
    identificacion: string | null;
    moneda_id: number;
}
export interface ComprobanteCliente {
    id: number;
    estado: string;
    importe: string;
    referencia: string;
    decision: string | null;
    fecha: string;
    evidencia_url: string;
    aclaracion: boolean;
    fondo_id: number;
}
export function ComprobantesCuenta({
    cuenta,
    bancos,
    comprobantes,
}: {
    cuenta: CuentaComprobantes;
    bancos: BancoComprobante[];
    comprobantes: ComprobanteCliente[];
}) {
    const { form, enviar } = useComprobanteForm(cuenta.id);
    const { errors, isSubmitting, isSubmitSuccessful } = form.formState;
    const aclaracion = useWatch({
        control: form.control,
        name: 'aclaracion_id',
    });

    return (
        <div className="mx-auto max-w-3xl space-y-6 p-4 sm:p-8">
            <div>
                <h1 className="text-2xl font-bold">
                    Reportar transferencia o depósito
                </h1>
                <p className="mt-2 text-sm text-muted-foreground">
                    Cuenta {cuenta.numero} · Pendiente {cuenta.saldo.toFixed(2)}{' '}
                    {cuenta.moneda}
                </p>
            </div>
            <div className="rounded-xl border bg-muted/40 p-4 text-sm">
                Tu comprobante queda pendiente de revisión. Solo se aplica
                dinero después de verificar el movimiento bancario. La reserva
                conserva su proceso de confirmación y disponibilidad.
            </div>
            {isSubmitSuccessful && (
                <p role="status" className="rounded-xl border p-4">
                    Comprobante enviado. Consulta su estado abajo.
                </p>
            )}
            {cuenta.saldo > 0 && (
                <form
                    onSubmit={enviar}
                    className="space-y-4 rounded-xl border p-5"
                >
                    {aclaracion !== null && (
                        <p className="text-sm font-semibold">
                            Adjunta una nueva evidencia para la aclaración
                            solicitada. Conserva los datos bancarios originales.
                        </p>
                    )}
                    <Field>
                        <FieldLabel>Banco al que transferiste</FieldLabel>
                        <Controller
                            control={form.control}
                            name="banco_id"
                            render={({ field }) => (
                                <Select
                                    value={field.value}
                                    onValueChange={field.onChange}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona el banco" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {bancos
                                            .filter(
                                                (b) =>
                                                    b.moneda_id ===
                                                    cuenta.moneda_id,
                                            )
                                            .map((b) => (
                                                <SelectItem
                                                    key={b.id}
                                                    value={String(b.id)}
                                                >
                                                    {b.nombre} · {b.banco} ·{' '}
                                                    {b.identificacion}
                                                </SelectItem>
                                            ))}
                                    </SelectContent>
                                </Select>
                            )}
                        />
                        <FieldError>{errors.banco_id?.message}</FieldError>
                    </Field>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel htmlFor="importe">
                                Importe en {cuenta.moneda}
                            </FieldLabel>
                            <Input
                                id="importe"
                                inputMode="decimal"
                                {...form.register('importe')}
                            />
                            <FieldError>{errors.importe?.message}</FieldError>
                        </Field>
                        <Field>
                            <FieldLabel htmlFor="fecha">
                                Fecha del movimiento
                            </FieldLabel>
                            <Input
                                id="fecha"
                                type="date"
                                {...form.register('fecha')}
                            />
                            <FieldError>{errors.fecha?.message}</FieldError>
                        </Field>
                    </div>
                    <Field>
                        <FieldLabel htmlFor="referencia">
                            Referencia bancaria
                        </FieldLabel>
                        <Input
                            id="referencia"
                            {...form.register('referencia')}
                        />
                        <FieldError>{errors.referencia?.message}</FieldError>
                    </Field>
                    <Field>
                        <FieldLabel htmlFor="archivo">
                            Imagen o PDF · hasta 5 MB
                        </FieldLabel>
                        <Input
                            id="archivo"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,application/pdf"
                            onChange={(e) => {
                                const archivo = e.target.files?.[0];

                                if (archivo) {
                                    form.setValue('archivo', archivo, {
                                        shouldValidate: true,
                                    });
                                }
                            }}
                        />
                        <FieldError>{errors.archivo?.message}</FieldError>
                    </Field>
                    <Button type="submit" disabled={isSubmitting}>
                        {isSubmitting ? (
                            <Loader2 className="size-4 animate-spin" />
                        ) : (
                            <Paperclip className="size-4" />
                        )}
                        Enviar a revisión
                    </Button>
                </form>
            )}
            <section className="space-y-3">
                <h2 className="text-lg font-semibold">Mis comprobantes</h2>
                {comprobantes.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Todavía no has enviado comprobantes.
                    </p>
                ) : (
                    comprobantes.map((c) => (
                        <article
                            key={c.id}
                            className="space-y-2 rounded-xl border p-4"
                        >
                            <div className="flex flex-wrap justify-between gap-2">
                                <strong className="break-all">
                                    {c.referencia}
                                </strong>
                                <span className="rounded-full bg-muted px-3 py-1 text-xs font-semibold">
                                    {(
                                        {
                                            pendiente: 'Pendiente de revisión',
                                            aprobado: 'Pago aplicado',
                                            rechazado: 'Rechazado',
                                            aclaracion: 'Aclaración solicitada',
                                        } as Record<string, string>
                                    )[c.estado] ?? c.estado}
                                </span>
                            </div>
                            <p className="text-sm">
                                {c.fecha} · {c.importe} {cuenta.moneda}
                            </p>
                            {c.decision && (
                                <p className="text-sm">{c.decision}</p>
                            )}
                            <a
                                className="text-sm underline"
                                href={c.evidencia_url}
                                target="_blank"
                                rel="noreferrer"
                            >
                                Ver mi evidencia
                            </a>
                            {c.aclaracion && (
                                <Button
                                    variant="outline"
                                    onClick={() => {
                                        form.setValue('aclaracion_id', c.id);
                                        form.setValue(
                                            'referencia',
                                            c.referencia,
                                        );
                                        form.setValue('fecha', c.fecha);
                                        form.setValue('importe', c.importe);
                                        form.setValue(
                                            'banco_id',
                                            String(c.fondo_id),
                                        );
                                        form.setValue(
                                            'clave',
                                            crypto.randomUUID(),
                                        );
                                        window.scrollTo({
                                            top: 0,
                                            behavior: 'smooth',
                                        });
                                    }}
                                >
                                    Adjuntar aclaración
                                </Button>
                            )}
                        </article>
                    ))
                )}
            </section>
        </div>
    );
}
