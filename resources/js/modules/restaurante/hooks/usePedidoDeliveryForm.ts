import { zodResolver } from '@hookform/resolvers/zod';
import { useState } from 'react';
import { useForm, useWatch } from 'react-hook-form';
import type { StripePaymentData } from '@/modules/reservas/types';
import { usePropiedadesPagina } from '@/modules/shared/hooks/usePropiedadesPagina';
import { departamentosPorDefecto } from '../constants';
import { pedidoDeliverySchema } from '../schemas/pedidoDeliverySchema';
import type { FormPedidoDelivery } from '../schemas/pedidoDeliverySchema';
import restauranteService from '../services/restauranteService';
import type { RespuestaPedidoBackend } from '../services/restauranteService';
import type { DepartamentoDelivery, ItemCarritoRestaurante } from '../types';
import {
    abrirWhatsappSiExiste,
    mapearItemsParaPedido,
    redirigirAlPortalPedidos,
} from '../utils/pedidoUtils';

interface PropsUsePedidoDeliveryForm {
    items: ItemCarritoRestaurante[];
    costoDelivery: number;
    departamentosDisponibles?: DepartamentoDelivery[];
    alEnviarExitoso?: () => void;
}

export const usePedidoDeliveryForm = ({
    items,
    costoDelivery,
    departamentosDisponibles = [],
    alEnviarExitoso,
}: PropsUsePedidoDeliveryForm) => {
    const { auth } = usePropiedadesPagina();
    const usuario = auth?.user;

    const [pasoActual, setPasoActual] = useState<1 | 2 | 3>(1);
    const [pedidoCreado, setPedidoCreado] =
        useState<RespuestaPedidoBackend | null>(null);
    const [stripeData, setStripeData] = useState<StripePaymentData | null>(
        null,
    );
    const [errorEnvio, setErrorEnvio] = useState<string | null>(null);
    const [confirmandoStripe, setConfirmandoStripe] = useState(false);

    const departamentosActivos =
        departamentosDisponibles.length > 0
            ? departamentosDisponibles.filter((d) => d.activo)
            : departamentosPorDefecto;

    const form = useForm<FormPedidoDelivery>({
        resolver: zodResolver(pedidoDeliverySchema),
        defaultValues: {
            nombre: usuario?.persona?.nombre_completo || usuario?.name || '',
            email: usuario?.email || '',
            telefono: usuario?.persona?.telefono || '',
            departamento: departamentosActivos[0]?.nombre || 'Estelí',
            municipio: departamentosActivos[0]?.municipios[0] || 'Estelí',
            direccion: '',
            metodoPago: 'efectivo',
            montoPagaCon: '',
            notas: '',
        },
    });

    const { register, control, setValue, trigger, handleSubmit, formState } =
        form;
    const { errors, isSubmitting } = formState;

    const departamentoSeleccionado = useWatch({
        control,
        name: 'departamento',
    });
    const municipioSeleccionado = useWatch({ control, name: 'municipio' });
    const metodoPagoSeleccionado = useWatch({ control, name: 'metodoPago' });

    const departamentoObj =
        departamentosActivos.find(
            (d) =>
                d.nombre.toLowerCase() ===
                (departamentoSeleccionado || '').toLowerCase(),
        ) || departamentosActivos[0];

    const municipiosDisponibles = departamentoObj?.municipios || ['Estelí'];

    const costoEnvioCalculado =
        departamentoObj && typeof departamentoObj.costo_envio === 'number'
            ? departamentoObj.costo_envio
            : costoDelivery;

    const cambiarDepartamento = (nombre: string) => {
        const dep = departamentosActivos.find(
            (d) => d.nombre.toLowerCase() === nombre.toLowerCase(),
        );
        setValue('departamento', nombre, {
            shouldValidate: true,
            shouldDirty: true,
        });
        setValue('municipio', dep?.municipios[0] || 'Estelí', {
            shouldValidate: true,
        });
    };

    const irAlPaso = (nuevoPaso: 1 | 2 | 3) => {
        if (nuevoPaso < pasoActual) {
            setPasoActual(nuevoPaso);

            return;
        }

        if (nuevoPaso === 2) {
            if (items.length === 0) {
                setErrorEnvio(
                    'Agrega al menos un platillo al carrito antes de continuar.',
                );

                return;
            }

            setErrorEnvio(null);
            setPasoActual(2);

            return;
        }

        if (nuevoPaso === 3) {
            trigger([
                'nombre',
                'telefono',
                'departamento',
                'municipio',
                'direccion',
            ]).then((esValido) => {
                if (esValido) {
                    setErrorEnvio(null);
                    setPasoActual(3);
                }
            });
        }
    };

    const avanzarPaso = () => irAlPaso((pasoActual + 1) as 1 | 2 | 3);
    const retrocederPaso = () => irAlPaso((pasoActual - 1) as 1 | 2 | 3);

    const completarFlujoExitoso = (whatsappUrl?: string) => {
        alEnviarExitoso?.();
        abrirWhatsappSiExiste(whatsappUrl);
        redirigirAlPortalPedidos();
    };

    const onSubmit = async (data: FormPedidoDelivery) => {
        if (items.length === 0) {
            setErrorEnvio(
                'Tu carrito está vacío. Agrega platillos antes de confirmar.',
            );

            return;
        }

        setErrorEnvio(null);

        try {
            const datosPedido = await restauranteService.crearPedido({
                infoEntrega: data,
                items: mapearItemsParaPedido(items),
            });
            setPedidoCreado(datosPedido);

            if (
                data.metodoPago === 'stripe' &&
                datosPedido.stripe_pago !== null
            ) {
                setStripeData({
                    client_secret: datosPedido.stripe_pago.client_secret,
                    publishable_key: datosPedido.stripe_pago.publishable_key,
                    monto: datosPedido.stripe_pago.monto,
                    moneda: datosPedido.moneda,
                });
            } else {
                completarFlujoExitoso(datosPedido.whatsapp_url);
            }
        } catch (err: unknown) {
            setErrorEnvio(
                err instanceof Error
                    ? err.message
                    : 'Error de conexión con el servidor.',
            );
        }
    };

    const handleStripeSuccess = async (paymentIntentId: string) => {
        if (!pedidoCreado) {
            return;
        }

        setConfirmandoStripe(true);
        setErrorEnvio(null);

        try {
            const data = await restauranteService.confirmarPagoStripePedido({
                pedido_id: pedidoCreado.pedido_id,
                payment_intent_id: paymentIntentId,
            });

            setStripeData(null);
            setPedidoCreado({
                ...pedidoCreado,
                whatsapp_url: data.whatsapp_url || pedidoCreado.whatsapp_url,
            });
            completarFlujoExitoso(data.whatsapp_url);
        } catch {
            setErrorEnvio(
                'No se pudo confirmar el pago de Stripe en el servidor.',
            );
        } finally {
            setConfirmandoStripe(false);
        }
    };

    const reiniciarPedido = () => {
        setPedidoCreado(null);
        setStripeData(null);
        setErrorEnvio(null);
        setPasoActual(1);
        form.reset();
    };

    return {
        register,
        setValue,
        errors,
        isSubmitting,
        pasoActual,
        irAlPaso,
        avanzarPaso,
        retrocederPaso,
        cambiarDepartamento,
        departamentosActivos,
        municipiosDisponibles,
        departamentoSeleccionado,
        municipioSeleccionado,
        metodoPagoSeleccionado,
        costoEnvioCalculado,
        pedidoCreado,
        stripeData,
        confirmandoStripe,
        errorEnvio,
        onSubmit: handleSubmit(onSubmit),
        handleStripeSuccess,
        reiniciarPedido,
        setStripeData,
    };
};
