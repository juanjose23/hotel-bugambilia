import { Head } from '@inertiajs/react';
import type { SyntheticEvent } from 'react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';
import type { PropiedadesReservarEspacio } from '@/modulos/espacios/interfaces/reservaEspacio';
import { PasosReservaEspacio } from '@/modulos/reservas/componentes/PasosReservaEspacio';
import { PlantillaProcesoReserva } from '@/modulos/reservas/componentes/PlantillaProcesoReserva';
import { useFormularioReservaEspacio } from '@/modulos/reservas/hooks/useFormularioReservaEspacio';

const PASOS_RESERVA = [
    { id: 1, titulo: 'Fecha & Horario' },
    { id: 2, titulo: 'Huéspedes' },
    { id: 3, titulo: 'Adicionales' },
    { id: 4, titulo: 'Confirmación' },
];

export const PaginaEspacioReservar = ({
    space,
    opcionesReserva,
}: PropiedadesReservarEspacio) => {
    const {
        data,
        setData,
        transform,
        post,
        processing,
        errors,
        pasoActual,
        avanzar,
        retroceder,
        irAlPaso,
        limpiarBorrador,
    } = useFormularioReservaEspacio(space);

    const [promoAplicada] = useState<string | null>(() => {
        if (typeof window === 'undefined') {
            return null;
        }

        const params = new URLSearchParams(window.location.search);

        return params.get('promo') || params.get('codigo_promocional');
    });

    const imagenPrincipal = useMemo(
        () =>
            space.imagenes && space.imagenes.length > 0
                ? space.imagenes[0]
                : '/images/main-room.webp',
        [space.imagenes],
    );

    const subtotalEstimado = space.precio_base || space.precio || 0;

    const validarPasoActual = (): boolean => {
        if (pasoActual !== 1) {
            return true;
        }

        if (!data.fecha_check_in) {
            toast.error('Seleccione la fecha para su reserva.');

            return false;
        }

        if (!data.hora_reserva || !data.hora_fin) {
            toast.error('Especifique la hora de inicio y fin de su reserva.');

            return false;
        }

        if (
            !(data.nombre_cliente ?? '').trim() ||
            !(data.telefono_cliente ?? '').trim()
        ) {
            toast.error('Ingrese su nombre y teléfono para continuar.');

            return false;
        }

        return true;
    };

    const handleNextStep = (e: SyntheticEvent) => {
        e.preventDefault();

        if (!validarPasoActual()) {
            return;
        }

        if (pasoActual < 4) {
            avanzar();

            return;
        }

        transform((valores) => ({
            ...valores,
            tipo_reserva: 'restaurante',
            espacio_id: String(space.id || ''),
            tipo_pago_reserva: valores.tipo_pago_reserva || 'abono_50',
            canal_pago_reserva: valores.canal_pago_reserva || 'stripe',
            origen_pago_reserva: 'publico',
            metodo_pago_reserva:
                valores.canal_pago_reserva === 'transferencia' ? 4 : null,
        }));

        post('/reservas', { onSuccess: limpiarBorrador });
    };

    return (
        <>
            <Head title={`Reservar ${space.nombre} - Hotel Bugambilias`} />
            <PlantillaProcesoReserva
                nombreRecurso={space.nombre}
                categoriaRecurso={space.tipo_label || space.tipo}
                ubicacionRecurso={space.ubicacion || 'Hotel Bugambilias Estelí'}
                imagenPrincipal={imagenPrincipal}
                slugRecurso={space.slug || String(space.id)}
                rutaRetorno="/espacios"
                promoAplicada={promoAplicada}
                pasoActual={pasoActual}
                pasos={PASOS_RESERVA}
                errores={errors as Record<string, string>}
                procesando={processing}
                onRetroceder={retroceder}
                onIrAlPaso={irAlPaso}
                onSubmit={handleNextStep}
            >
                <PasosReservaEspacio
                    pasoActual={pasoActual}
                    space={space}
                    data={data}
                    setData={setData}
                    opcionesReserva={opcionesReserva}
                    subtotalEstimado={subtotalEstimado}
                />
            </PlantillaProcesoReserva>
        </>
    );
};

export default PaginaEspacioReservar;
