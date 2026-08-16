import { useCallback, useState } from 'react';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import type { TabPortal } from '../interfaces/portalInterfaces';

interface RespuestaCancelacion {
    message?: string;
    reembolso?: {
        pendiente_administracion: boolean;
        intentos_stripe?: number;
    };
}

const obtenerTokenCsrf = (): string =>
    document
        .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

export const usePortalMisReservas = (
    reservasIniciales: ReservaClienteDomain[] = [],
) => {
    const [reservas, setReservas] =
        useState<ReservaClienteDomain[]>(reservasIniciales);

    const [searchTerm, setSearchTerm] = useState<string>('');
    const [portalTab, setPortalTab] = useState<TabPortal>('overview');

    const [reservaACancelar, setReservaACancelar] =
        useState<ReservaClienteDomain | null>(null);
    const [motivoCancelacion, setMotivoCancelacion] = useState<string>('');
    const [cancelando, setCancelando] = useState<boolean>(false);
    const [errorCancelacion, setErrorCancelacion] = useState<string | null>(
        null,
    );
    const [mensajeCancelacion, setMensajeCancelacion] = useState<string | null>(
        null,
    );
    const [reembolsoPendiente, setReembolsoPendiente] =
        useState<boolean>(false);

    const [reservaDetalle, setReservaDetalle] =
        useState<ReservaClienteDomain | null>(null);

    const reservasFiltradas = reservas.filter((reserva) => {
        if (!searchTerm.trim()) {
            return true;
        }

        const busqueda = searchTerm.toLowerCase();

        return (
            reserva.codigo_reserva.toLowerCase().includes(busqueda) ||
            reserva.nombre_cliente.toLowerCase().includes(busqueda) ||
            (reserva.detalles &&
                reserva.detalles.toLowerCase().includes(busqueda))
        );
    });

    const reservasActivas = reservasFiltradas.filter(
        (r) => r.estado === 1 || r.estado === 2,
    );
    const reservasPasadas = reservasFiltradas.filter(
        (r) => r.estado !== 1 && r.estado !== 2,
    );

    const cerrarCancelacion = () => {
        setReservaACancelar(null);
        setMotivoCancelacion('');
        setErrorCancelacion(null);
        setMensajeCancelacion(null);
        setReembolsoPendiente(false);
    };

    const cancelarReserva = useCallback(
        async (reserva: ReservaClienteDomain): Promise<boolean> => {
            setCancelando(true);
            setErrorCancelacion(null);
            setMensajeCancelacion(null);
            setReembolsoPendiente(false);

            const controlador = new AbortController();
            const timeout = window.setTimeout(() => controlador.abort(), 15000);

            try {
                const url = `/api/reservas/${encodeURIComponent(reserva.codigo_reserva)}/cancelar`;

                const respuesta = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': obtenerTokenCsrf(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        motivo: motivoCancelacion,
                    }),
                    signal: controlador.signal,
                });

                window.clearTimeout(timeout);

                if (!respuesta.ok) {
                    const errorJson = await respuesta
                        .json()
                        .catch(() => ({ message: 'Error de comunicación.' }));

                    throw new Error(
                        errorJson.message ||
                            `Error ${respuesta.status}: No se pudo procesar la cancelación.`,
                    );
                }

                const data: RespuestaCancelacion = await respuesta.json();

                setReservas((prev) =>
                    prev.map((r) =>
                        r.id === reserva.id ? { ...r, estado: 3 } : r,
                    ),
                );

                setMensajeCancelacion(
                    data.message ||
                        'La reservación fue cancelada correctamente.',
                );
                setReembolsoPendiente(
                    Boolean(data.reembolso?.pendiente_administracion),
                );

                return true;
            } catch (err: unknown) {
                window.clearTimeout(timeout);

                if (err instanceof Error) {
                    if (err.name === 'AbortError') {
                        setErrorCancelacion(
                            'Tiempo de espera agotado al conectar con el servidor.',
                        );
                    } else {
                        setErrorCancelacion(err.message);
                    }
                } else {
                    setErrorCancelacion(
                        'Ocurrió un error inesperado al cancelar.',
                    );
                }

                return false;
            } finally {
                setCancelando(false);
            }
        },
        [motivoCancelacion],
    );

    return {
        searchTerm,
        setSearchTerm,
        portalTab,
        setPortalTab,
        reservasFiltradas,
        reservasActivas,
        reservasPasadas,
        reservaACancelar,
        setReservaACancelar,
        motivoCancelacion,
        setMotivoCancelacion,
        cancelando,
        errorCancelacion,
        mensajeCancelacion,
        reembolsoPendiente,
        cerrarCancelacion,
        cancelarReserva,
        reservaDetalle,
        setReservaDetalle,
    };
};

export default usePortalMisReservas;
