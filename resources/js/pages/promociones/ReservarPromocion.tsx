import { Head, Link } from '@inertiajs/react';
import { CalendarDays, Gift, Loader2 } from 'lucide-react';
import { useReservarPromocionForm } from '@/modules/promociones/hooks/useReservarPromocionForm';
import type {
    PromocionItem,
    OpcionReservaPromocion,
} from '@/modules/promociones/types';
import { GuiaPasoAPaso } from '@/modules/shared/components/GuiaPasoAPaso';
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
import { Textarea } from '@/modules/shared/components/ui/textarea';

export default function ReservarPromocion({
    promocion,
    opciones,
    tarjeta_disponible,
}: {
    promocion: PromocionItem;
    opciones: OpcionReservaPromocion[];
    tarjeta_disponible: boolean;
}) {
    const { form, opcion, onSubmit } = useReservarPromocionForm(
        promocion,
        opciones,
    );
    const {
        register,
        formState: { errors, isSubmitting },
    } = form;

    return (
        <div className="container mx-auto max-w-5xl space-y-6 px-4 py-8">
            <Head title={`Reservar ${promocion.nombre}`} />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <Link
                    href="/promociones"
                    className="text-sm text-muted-foreground"
                >
                    Volver a las promociones
                </Link>
                <GuiaPasoAPaso
                    pasos={[
                        {
                            elemento: '[data-guia="promocion-incluye"]',
                            titulo: 'Revisa lo incluido',
                            descripcion:
                                'El precio y los beneficios pertenecen a esta promoción. Las fechas, el número de personas y la anticipación se validan al reservar.',
                        },
                        {
                            elemento: '[data-guia="promocion-fechas"]',
                            titulo: 'Elige cuándo utilizarla',
                            descripcion:
                                'Selecciona el recurso y las fechas. Si el paquete incluye alojamiento, respeta el número de noches anunciado.',
                        },
                        {
                            elemento: '[data-guia="promocion-contacto"]',
                            titulo: 'Datos de contacto',
                            descripcion:
                                'Escribe los datos del titular. Recibirás un código y un enlace para consultar la reserva.',
                        },
                        {
                            elemento: '[data-guia="promocion-confirmar"]',
                            titulo: 'Confirmación y pago',
                            descripcion:
                                'Al confirmar se comprueba de nuevo la disponibilidad y la tarifa. Si eliges tarjeta, continuarás al pago; una reserva pendiente no equivale a un pago recibido.',
                        },
                    ]}
                />
            </div>
            <h1 className="flex items-center gap-3 text-2xl font-bold">
                <Gift className="size-6" />
                {promocion.nombre}
            </h1>
            <div className="grid gap-6 md:grid-cols-[1fr_1.2fr]">
                <aside
                    data-guia="promocion-incluye"
                    className="space-y-4 rounded-2xl border bg-card p-5"
                >
                    <p>{promocion.descripcion}</p>
                    {promocion.precio_original ? (
                        <p className="text-2xl font-bold">
                            {promocion.moneda}{' '}
                            {promocion.precio_final.toFixed(2)}{' '}
                            <span className="text-sm font-normal text-muted-foreground">
                                por paquete
                            </span>
                        </p>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            El descuento se aplica sobre la tarifa vigente del
                            recurso y las fechas seleccionadas.
                        </p>
                    )}
                    {promocion.noches_incluidas && (
                        <p>
                            <CalendarDays className="mr-2 inline size-4" />
                            {promocion.noches_incluidas} noches incluidas
                        </p>
                    )}
                    {promocion.personas_incluidas && (
                        <p>Hasta {promocion.personas_incluidas} personas</p>
                    )}
                    {!!promocion.anticipacion_horas && (
                        <p>
                            Reserva con {promocion.anticipacion_horas} horas de
                            anticipación
                        </p>
                    )}
                    <ul className="space-y-2 text-sm">
                        {promocion.items?.map((item) => (
                            <li key={item.id}>✓ {item.nombre ?? item.tipo}</li>
                        ))}
                    </ul>
                    {promocion.condiciones && (
                        <p className="border-t pt-3 text-sm text-muted-foreground">
                            {promocion.condiciones}
                        </p>
                    )}
                    <p className="text-xs text-muted-foreground">
                        Precio base antes de impuestos y cargos aplicables. El
                        total final se muestra en la confirmación. Sujeto a
                        disponibilidad.
                    </p>
                </aside>
                <form
                    onSubmit={onSubmit}
                    className="space-y-5 rounded-2xl border p-5"
                >
                    {opciones.length === 0 ? (
                        <p role="alert">
                            Esta oferta aún no tiene recursos disponibles para
                            reserva en línea. Contacta con recepción.
                        </p>
                    ) : (
                        <>
                            <section
                                data-guia="promocion-fechas"
                                className="space-y-4"
                            >
                                <Field>
                                    <FieldLabel>
                                        Recurso de la promoción
                                    </FieldLabel>
                                    <Select
                                        value={form.watch('opcion')}
                                        onValueChange={(value) =>
                                            form.setValue(
                                                'opcion',
                                                value ?? '',
                                                { shouldValidate: true },
                                            )
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {opciones.map((item) => (
                                                <SelectItem
                                                    key={item.key}
                                                    value={item.key}
                                                >
                                                    {item.nombre}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <FieldError>
                                        {errors.opcion?.message}
                                    </FieldError>
                                </Field>
                                <div className="grid grid-cols-2 gap-3">
                                    <Field>
                                        <FieldLabel htmlFor="promo-fecha">
                                            Fecha de llegada
                                        </FieldLabel>
                                        <Input
                                            id="promo-fecha"
                                            type="date"
                                            {...register('fecha_check_in')}
                                        />
                                        <FieldError>
                                            {errors.fecha_check_in?.message}
                                        </FieldError>
                                    </Field>
                                    {opcion?.necesita_salida ? (
                                        <Field>
                                            <FieldLabel htmlFor="promo-salida">
                                                Fecha de salida
                                            </FieldLabel>
                                            <Input
                                                id="promo-salida"
                                                type="date"
                                                {...register('fecha_check_out')}
                                            />
                                            <FieldError>
                                                {
                                                    errors.fecha_check_out
                                                        ?.message
                                                }
                                            </FieldError>
                                        </Field>
                                    ) : (
                                        <Field>
                                            <FieldLabel htmlFor="promo-hora">
                                                Hora
                                            </FieldLabel>
                                            <Input
                                                id="promo-hora"
                                                type="time"
                                                {...register('hora_reserva')}
                                            />
                                            <FieldError>
                                                {errors.hora_reserva?.message}
                                            </FieldError>
                                        </Field>
                                    )}
                                </div>
                                <Field>
                                    <FieldLabel htmlFor="promo-personas">
                                        Personas
                                    </FieldLabel>
                                    <Input
                                        id="promo-personas"
                                        type="number"
                                        min={1}
                                        max={promocion.personas_incluidas ?? 30}
                                        {...register('adultos', {
                                            valueAsNumber: true,
                                        })}
                                    />
                                    <FieldError>
                                        {errors.adultos?.message}
                                    </FieldError>
                                </Field>
                            </section>
                            <section
                                data-guia="promocion-contacto"
                                className="space-y-3"
                            >
                                {(
                                    [
                                        {
                                            name: 'nombre_cliente',
                                            label: 'Nombre completo',
                                            type: 'text',
                                        },
                                        {
                                            name: 'telefono_cliente',
                                            label: 'Teléfono',
                                            type: 'tel',
                                        },
                                        {
                                            name: 'email_cliente',
                                            label: 'Correo electrónico',
                                            type: 'email',
                                        },
                                    ] as const
                                ).map((input) => (
                                    <Field key={input.name}>
                                        <FieldLabel
                                            htmlFor={`promo-${input.name}`}
                                        >
                                            {input.label}
                                        </FieldLabel>
                                        <Input
                                            id={`promo-${input.name}`}
                                            type={input.type}
                                            {...register(input.name)}
                                        />
                                        <FieldError>
                                            {errors[input.name]?.message}
                                        </FieldError>
                                    </Field>
                                ))}
                                <Field>
                                    <FieldLabel htmlFor="promo-notas">
                                        Observaciones
                                    </FieldLabel>
                                    <Textarea
                                        id="promo-notas"
                                        {...register('notas')}
                                    />
                                </Field>
                            </section>
                            <section
                                data-guia="promocion-confirmar"
                                className="space-y-3"
                            >
                                <Field>
                                    <FieldLabel>Forma de confirmar</FieldLabel>
                                    <Select
                                        value={form.watch('tipo_pago_reserva')}
                                        onValueChange={(value) =>
                                            form.setValue(
                                                'tipo_pago_reserva',
                                                value as
                                                    | 'sin_pago'
                                                    | 'abono_50'
                                                    | 'pago_total',
                                            )
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="sin_pago">
                                                Reservar y coordinar pago con el
                                                hotel
                                            </SelectItem>
                                            {tarjeta_disponible && (
                                                <>
                                                    <SelectItem value="abono_50">
                                                        Abonar con tarjeta
                                                    </SelectItem>
                                                    <SelectItem value="pago_total">
                                                        Pagar el total con
                                                        tarjeta
                                                    </SelectItem>
                                                </>
                                            )}
                                        </SelectContent>
                                    </Select>
                                </Field>
                                {errors.root?.message && (
                                    <p
                                        role="alert"
                                        className="text-sm text-destructive"
                                    >
                                        {errors.root.message}
                                    </p>
                                )}
                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={isSubmitting}
                                >
                                    {isSubmitting && (
                                        <Loader2 className="mr-2 size-4 animate-spin" />
                                    )}
                                    Confirmar disponibilidad y reservar
                                </Button>
                            </section>
                        </>
                    )}
                </form>
            </div>
        </div>
    );
}
