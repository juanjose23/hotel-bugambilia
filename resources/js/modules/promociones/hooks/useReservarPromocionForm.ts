import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import { format, addDays } from 'date-fns';
import { useForm, useWatch } from 'react-hook-form';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';
import { reservarPromocionSchema } from '../schemas/reservarPromocionSchema';
import type { ReservarPromocionValues } from '../schemas/reservarPromocionSchema';
import type { PromocionItem, OpcionReservaPromocion } from '../types';

export function useReservarPromocionForm(
    promocion: PromocionItem,
    opciones: OpcionReservaPromocion[],
) {
    const { auth } = usePropiedadesPagina();
    const inicio = addDays(
        new Date(),
        Math.max(1, Math.ceil((promocion.anticipacion_horas ?? 0) / 24)),
    );
    const form = useForm<ReservarPromocionValues>({
        resolver: zodResolver(reservarPromocionSchema),
        defaultValues: {
            opcion: opciones[0]?.key ?? '',
            nombre_cliente: auth?.user?.name ?? '',
            telefono_cliente: auth?.user?.persona?.telefono ?? '',
            email_cliente: auth?.user?.email ?? '',
            fecha_check_in: format(inicio, 'yyyy-MM-dd'),
            fecha_check_out: format(
                addDays(inicio, promocion.noches_incluidas ?? 1),
                'yyyy-MM-dd',
            ),
            hora_reserva: '13:00',
            duracion_horas: 1,
            adultos: Math.min(2, promocion.personas_incluidas ?? 2),
            tipo_pago_reserva: 'sin_pago',
            notas: '',
        },
    });
    const seleccion = useWatch({ control: form.control, name: 'opcion' });
    const opcion = opciones.find((item) => item.key === seleccion);
    const onSubmit = form.handleSubmit(async (datos) => {
        form.clearErrors('root');

        if (
            opcion?.necesita_salida &&
            (!datos.fecha_check_out ||
                datos.fecha_check_out <= datos.fecha_check_in)
        ) {
            form.setError('fecha_check_out', {
                message: 'La salida debe ser posterior al ingreso',
            });

            return;
        }

        try {
            const response = await fetch(
                `/promociones/${promocion.id}/reservar`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN':
                            document.querySelector<HTMLMetaElement>(
                                'meta[name="csrf-token"]',
                            )?.content ?? '',
                    },
                    body: JSON.stringify({
                        ...datos,
                        fecha_check_out: opcion?.necesita_salida
                            ? datos.fecha_check_out
                            : null,
                    }),
                },
            );
            const resultado = (await response.json()) as {
                message?: string;
                errors?: Record<string, string[]>;
                requiere_pago_stripe?: boolean;
                url_pago?: string;
                url_detalle?: string;
            };

            if (!response.ok) {
                throw new Error(
                    Object.values(resultado.errors ?? {})[0]?.[0] ??
                        resultado.message ??
                        'No se pudo registrar la reserva',
                );
            }

            const destino = resultado.requiere_pago_stripe
                ? resultado.url_pago
                : resultado.url_detalle;

            if (!destino) {
                throw new Error(
                    'No se recibió el enlace de seguimiento de la reserva',
                );
            }

            router.visit(destino);
        } catch (error) {
            form.setError('root', {
                message:
                    error instanceof Error
                        ? error.message
                        : 'No se pudo completar la solicitud',
            });
        }
    });

    return { form, opcion, onSubmit };
}
