import { zodResolver } from '@hookform/resolvers/zod';
import { usePage } from '@inertiajs/react';
import { useState, useMemo, useCallback } from 'react';
import { useForm, useWatch } from 'react-hook-form';
import type { RoomItem } from '@/modules/shared/types';
import { crearReservaSchema } from '../schemas/crearReservaSchema';
import type { CrearReservaFormValues } from '../schemas/crearReservaSchema';
import { reservasService } from '../services/reservasService';
import type {
    BeneficioClienteItem,
    ServicioAdicionalItem,
    StripePaymentData,
    ReservaCreadaResponse,
    PoliticaReserva,
} from '../types';
import { useDiasAgotados } from './useDiasAgotados';
import { useReservaCalculos } from './useReservaCalculos';
import { useReservaDisponibilidad } from './useReservaDisponibilidad';

interface UseCrearReservaFormProps {
    room: RoomItem & {
        precio?: number | string;
        precio_desde?: number | string;
        moneda?: string;
        categoria_id?: number;
        politicas?: PoliticaReserva[];
        slug?: string;
    };
    serviciosDisponibles?: ServicioAdicionalItem[];
    beneficiosCliente?: BeneficioClienteItem[];
    diasAgotados?: string[];
    initialCheckIn?: string;
    initialCheckOut?: string;
    initialHuespedes?: string | number;
    onSuccess?: (reserva: ReservaCreadaResponse) => void;
}

export const useCrearReservaForm = ({
    room,
    serviciosDisponibles = [],
    beneficiosCliente = [],
    diasAgotados: diasAgotadosIniciales = [],
    initialCheckIn = '',
    initialCheckOut = '',
    initialHuespedes = 2,
    onSuccess,
}: UseCrearReservaFormProps) => {
    const { props } = usePage();
    const authUser = (
        props as {
            auth?: {
                user?: {
                    name?: string;
                    email?: string;
                    persona?: {
                        telefono?: string;
                        cliente?: {
                            id?: number;
                            tipoCliente?: { codigo?: string; nombre?: string };
                        };
                    };
                };
            };
        }
    )?.auth?.user;

    // 1. Estados de Stepper y Pasarela de Pago
    const [pasoActual, setPasoActual] = useState<1 | 2 | 3 | 4>(1);
    const [stripeData, setStripeData] = useState<StripePaymentData | null>(
        null,
    );
    const [reservaCreada, setReservaCreada] =
        useState<ReservaCreadaResponse | null>(null);
    const [reservaConfirmada, setReservaConfirmada] =
        useState<ReservaCreadaResponse | null>(null);
    const [errorServidor, setErrorServidor] = useState<string | null>(null);

    // 2. Formulario React Hook Form
    const form = useForm<CrearReservaFormValues>({
        resolver: zodResolver(crearReservaSchema),
        defaultValues: {
            nombre_cliente: authUser?.name || '',
            email_cliente: authUser?.email || '',
            telefono_cliente: authUser?.persona?.telefono || '',
            tipo_reserva: 'habitacion',
            habitacion_id: room.id,
            fecha_check_in: initialCheckIn,
            fecha_check_out: initialCheckOut,
            adultos:
                Number(initialHuespedes) > 0 ? Number(initialHuespedes) : 2,
            ninos: 0,
            canal_pago_reserva: 'stripe',
            tipo_pago_reserva: 'pago_completo',
            notas: '',
            acompanantes: [],
            servicios_adicionales: [],
            beneficio_id: beneficiosCliente[0]?.id || null,
        },
    });

    const {
        register,
        handleSubmit,
        setValue,
        control,
        trigger,
        formState: { errors, isSubmitting },
    } = form;

    // 3. Suscripción a campos reactivos
    const checkIn = useWatch({ control, name: 'fecha_check_in' });
    const checkOut = useWatch({ control, name: 'fecha_check_out' });
    const adultos = useWatch({ control, name: 'adultos' });
    const ninos = useWatch({ control, name: 'ninos' });
    const canalPago = useWatch({ control, name: 'canal_pago_reserva' });
    const tipoPago = useWatch({ control, name: 'tipo_pago_reserva' });
    const beneficioId = useWatch({ control, name: 'beneficio_id' });
    const rawServicios = useWatch({ control, name: 'servicios_adicionales' });
    const serviciosSeleccionados = useMemo(
        () => rawServicios ?? [],
        [rawServicios],
    );

    // 4. Lógica de Dominio Aislada
    const {
        diasAgotados,
        cargando: cargandoDiasAgotados,
        recargar: recargarDiasAgotados,
    } = useDiasAgotados({
        slug: room.slug,
        diasIniciales: diasAgotadosIniciales,
        adultos,
        ninos,
    });

    const { tieneConflictoFechas } = useReservaDisponibilidad({
        checkIn,
        checkOut,
        diasAgotados,
    });

    const {
        noches,
        precioNoche,
        subtotalHabitacion,
        subtotalServicios,
        beneficioAplicado,
        montoDescuento,
        totalNeto,
        porcentajeAnticipoPolitica,
        montoACobrarAhora,
    } = useReservaCalculos({
        checkIn,
        checkOut,
        room,
        serviciosSeleccionados,
        serviciosDisponibles,
        beneficiosCliente,
        beneficioId,
        canalPago,
        tipoPago,
    });

    // 5. Mutaciones de Servicios Adicionales
    const toggleServicio = useCallback(
        (servicioId: number) => {
            const existe = serviciosSeleccionados.find(
                (s) => s.servicio_id === servicioId,
            );
            const updated = existe
                ? serviciosSeleccionados.filter(
                      (s) => s.servicio_id !== servicioId,
                  )
                : [
                      ...serviciosSeleccionados,
                      { servicio_id: servicioId, cantidad: 1 },
                  ];

            setValue('servicios_adicionales', updated);
        },
        [serviciosSeleccionados, setValue],
    );

    const cambiarCantidadServicio = useCallback(
        (servicioId: number, cantidad: number) => {
            if (cantidad < 1) {
                return;
            }

            const updated = serviciosSeleccionados.map((s) =>
                s.servicio_id === servicioId ? { ...s, cantidad } : s,
            );
            setValue('servicios_adicionales', updated);
        },
        [serviciosSeleccionados, setValue],
    );

    // 6. Transición de Pasos con Validación
    const irAlPaso = async (nuevoPaso: 1 | 2 | 3 | 4) => {
        setErrorServidor(null);

        if (nuevoPaso > pasoActual) {
            if (pasoActual === 1) {
                const valido = await trigger([
                    'fecha_check_in',
                    'fecha_check_out',
                    'adultos',
                ]);

                if (!valido) {
                    return;
                }

                if (tieneConflictoFechas) {
                    setErrorServidor(
                        'No hay habitaciones disponibles en esta categoría para las fechas seleccionadas.',
                    );

                    return;
                }
            }

            if (pasoActual === 2) {
                const valido = await trigger([
                    'nombre_cliente',
                    'email_cliente',
                    'telefono_cliente',
                ]);

                if (!valido) {
                    return;
                }
            }
        }

        setPasoActual(nuevoPaso);
    };

    // 7. Handlers de Envío y Pasarela Stripe
    const onSubmit = async (data: CrearReservaFormValues) => {
        setErrorServidor(null);

        if (tieneConflictoFechas) {
            setErrorServidor(
                'No hay habitaciones disponibles en esta categoría para las fechas seleccionadas.',
            );

            return;
        }

        try {
            const result = await reservasService.crearReserva(data);

            if (result.requiere_pago_stripe && result.stripe_pago) {
                setStripeData(result.stripe_pago);
                setReservaCreada(result.reserva);
            } else {
                setReservaCreada(result.reserva);
                setReservaConfirmada(result.reserva);
                onSuccess?.(result.reserva);
            }
        } catch (err: unknown) {
            setErrorServidor(
                err instanceof Error
                    ? err.message
                    : 'No se pudo completar la reserva.',
            );
        }
    };

    const handleStripeSuccess = async (paymentIntentId: string) => {
        if (!reservaCreada) {
            return;
        }

        try {
            await reservasService.confirmarPagoStripe({
                reserva_id: reservaCreada.id,
                codigo_reserva: reservaCreada.codigo_reserva,
                payment_intent_id: paymentIntentId,
            });

            setStripeData(null);
            setReservaConfirmada(reservaCreada);
            onSuccess?.(reservaCreada);
        } catch (err: unknown) {
            setErrorServidor(
                err instanceof Error
                    ? err.message
                    : 'Error al registrar la confirmación del pago.',
            );
        }
    };

    const cancelarStripe = () => {
        setStripeData(null);
        setReservaCreada(null);
    };

    // 8. Información de Cliente y Membresías
    const esCorporativo =
        authUser?.persona?.cliente?.tipoCliente?.codigo === 'CLI_CORPORATIVO';
    const esVip = authUser?.persona?.cliente?.tipoCliente?.codigo === 'CLI_VIP';
    const tieneBeneficioAnticipoReducido =
        esCorporativo ||
        esVip ||
        beneficiosCliente.some((b) => b.tipo === 'anticipo_reducido');

    return {
        form,
        register,
        handleSubmit: handleSubmit(onSubmit),
        setValue,
        errors,
        isSubmitting,
        pasoActual,
        irAlPaso,
        checkIn,
        checkOut,
        adultos,
        ninos,
        canalPago,
        tipoPago,
        noches,
        precioNoche,
        subtotalHabitacion,
        subtotalServicios,
        montoDescuento,
        totalNeto,
        montoACobrarAhora,
        beneficioAplicado,
        serviciosSeleccionados,
        toggleServicio,
        cambiarCantidadServicio,
        stripeData,
        reservaCreada,
        reservaConfirmada,
        porcentajeAnticipoPolitica,
        cancelarStripe,
        errorServidor,
        handleStripeSuccess,
        authUser,
        esCorporativo,
        esVip,
        tieneBeneficioAnticipoReducido,
        tieneConflictoFechas,
        diasAgotados,
        cargandoDiasAgotados,
        recargarDiasAgotados,
    };
};

export default useCrearReservaForm;
