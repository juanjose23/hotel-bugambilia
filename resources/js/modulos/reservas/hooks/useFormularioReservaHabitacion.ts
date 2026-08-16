import { useForm } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';
import { useAutenticacion } from '@/modulos/compartido/hooks/useAutenticacion';
import type { HabitacionReservable } from '@/modulos/habitaciones/interfaces/reservaHabitacion';
import { usePasosReserva } from '@/modulos/reservas/hooks/usePasosReserva';
import type { DatosBorradorHabitacion } from '@/modulos/reservas/interfaces/borradorReserva';
import { useAlmacenReserva } from '@/modulos/reservas/stores/useAlmacenReserva';

export function useFormularioReservaHabitacion(room: HabitacionReservable) {
    const auth = useAutenticacion();
    const authUser = auth?.user;
    const borradorGuardado = useAlmacenReserva((estado) => estado.borrador);
    const guardarBorrador = useAlmacenReserva(
        (estado) => estado.guardarBorrador,
    );
    const limpiarBorrador = useAlmacenReserva(
        (estado) => estado.limpiarBorrador,
    );
    const nombreUsuario =
        authUser?.persona?.nombre_completo || authUser?.name || '';
    const telefonoUsuario = authUser?.persona?.telefono || '';

    const borradorHabitacion =
        borradorGuardado?.tipo === 'habitacion' &&
        borradorGuardado.datos.habitacion_id === String(room.id)
            ? borradorGuardado
            : null;

    const pasos = usePasosReserva({
        totalPasos: 4,
        pasoInicial: borradorHabitacion?.pasoActual,
    });

    const datosIniciales = useMemo<DatosBorradorHabitacion>(() => {
        const borrador = borradorHabitacion?.datos;

        return {
            ...borrador,
            tipo_reserva: 'habitacion',
            habitacion_id: String(room.id || ''),
            fecha_check_in: borrador?.fecha_check_in || '',
            fecha_check_out: borrador?.fecha_check_out || '',
            adultos: borrador?.adultos || room.adultos || 2,
            ninos: borrador?.ninos ?? room.ninos ?? 0,
            nombre_cliente: borrador?.nombre_cliente || nombreUsuario,
            telefono_cliente: borrador?.telefono_cliente || telefonoUsuario,
            email_cliente: borrador?.email_cliente || authUser?.email || '',
            notas: borrador?.notas || '',
            servicios_adicionales: borrador?.servicios_adicionales || [],
            espacios_adicionales: borrador?.espacios_adicionales || [],
            promocion_id: borrador?.promocion_id ?? null,
            solicita_cuenta: borrador?.solicita_cuenta ?? false,
            limite_cuenta_solicitado:
                borrador?.limite_cuenta_solicitado ?? null,
            tipo_pago_reserva: borrador?.tipo_pago_reserva || 'abono_50',
            canal_pago_reserva: borrador?.canal_pago_reserva || 'stripe',
            origen_pago_reserva: 'publico',
            metodo_pago_reserva: borrador?.metodo_pago_reserva ?? null,
        };
    }, [
        authUser?.email,
        borradorHabitacion?.datos,
        nombreUsuario,
        room.adultos,
        room.id,
        room.ninos,
        telefonoUsuario,
    ]);

    const formulario = useForm<DatosBorradorHabitacion>(datosIniciales);

    const sincronizarBorradorSincrono = useCallback(
        (nuevosDatos: DatosBorradorHabitacion, paso: number) => {
            guardarBorrador({
                tipo: 'habitacion',
                rutaRetorno: `/habitaciones/${room.slug || room.id}/reservar`,
                pasoActual: paso,
                datos: nuevosDatos,
            });
        },
        [guardarBorrador, room.id, room.slug],
    );

    const avanzarYPuntos = useCallback(() => {
        const proximo = pasos.pasoActual + 1;
        pasos.avanzar();
        sincronizarBorradorSincrono(formulario.data, proximo);
    }, [formulario.data, pasos, sincronizarBorradorSincrono]);

    const retrocederYPuntos = useCallback(() => {
        const anterior = Math.max(1, pasos.pasoActual - 1);
        pasos.retroceder();
        sincronizarBorradorSincrono(formulario.data, anterior);
    }, [formulario.data, pasos, sincronizarBorradorSincrono]);

    const irAlPasoSincrono = useCallback(
        (paso: number) => {
            pasos.irAlPaso(paso);
            sincronizarBorradorSincrono(formulario.data, paso);
        },
        [formulario.data, pasos, sincronizarBorradorSincrono],
    );

    const actualizarDataSincrona = useCallback(
        (keyOrDataOrFn: any, value?: any) => {
            if (typeof keyOrDataOrFn === 'string') {
                formulario.setData((prev: DatosBorradorHabitacion) => {
                    const siguiente = { ...prev, [keyOrDataOrFn]: value };
                    sincronizarBorradorSincrono(siguiente, pasos.pasoActual);

                    return siguiente;
                });
            } else if (typeof keyOrDataOrFn === 'function') {
                formulario.setData((prev: DatosBorradorHabitacion) => {
                    const siguiente = keyOrDataOrFn(prev);
                    sincronizarBorradorSincrono(siguiente, pasos.pasoActual);

                    return siguiente;
                });
            } else {
                formulario.setData((prev: DatosBorradorHabitacion) => {
                    const siguiente = { ...prev, ...keyOrDataOrFn };
                    sincronizarBorradorSincrono(siguiente, pasos.pasoActual);

                    return siguiente;
                });
            }
        },
        [formulario, pasos.pasoActual, sincronizarBorradorSincrono],
    );

    return {
        ...formulario,
        ...pasos,
        setData: actualizarDataSincrona,
        avanzar: avanzarYPuntos,
        retroceder: retrocederYPuntos,
        irAlPaso: irAlPasoSincrono,
        guardarBorrador,
        limpiarBorrador,
    };
}
