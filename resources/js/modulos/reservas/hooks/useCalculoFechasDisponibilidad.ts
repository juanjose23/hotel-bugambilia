import { useState } from 'react';
import type { DateRange } from 'react-day-picker';

interface OpcionesCalculoFechas {
    diasAgotadosHabitacion?: string[];
    ocupacionHabitacionPorDia?: Record<
        string,
        {
            ocupadas: number;
            total: number;
            disponibles: number;
            agotado: boolean;
        }
    >;
    totalHabitacionesCategoria?: number;
    fechaCheckIn: string;
    fechaCheckOut: string;
    onSelectFechas: (checkIn: string, checkOut: string) => void;
}

export function useCalculoFechasDisponibilidad({
    diasAgotadosHabitacion = [],
    ocupacionHabitacionPorDia = {},
    totalHabitacionesCategoria,
    fechaCheckIn,
    fechaCheckOut,
    onSelectFechas,
}: OpcionesCalculoFechas) {
    const [diasAgotadosExtras, setDiasAgotadosExtras] = useState<string[]>([]);
    const mesesCalendario =
        typeof window !== 'undefined' && window.innerWidth >= 1024 ? 2 : 1;

    const now = new Date();
    const hoyStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

    const diasAgotadosSet = new Set([
        ...diasAgotadosHabitacion,
        ...diasAgotadosExtras,
    ]);

    const agregarDiasAgotados = (fechas: string[]) => {
        setDiasAgotadosExtras((prev) =>
            Array.from(new Set([...prev, ...fechas])),
        );
    };

    const formatearFechaSegura = (fecha: Date): string => {
        if (!fecha || !(fecha instanceof Date) || isNaN(fecha.getTime())) {
            return '';
        }

        const y = fecha.getFullYear();
        const m = String(fecha.getMonth() + 1).padStart(2, '0');
        const d = String(fecha.getDate()).padStart(2, '0');

        return `${y}-${m}-${d}`;
    };

    const construirFechaLocal = (fechaStr: string): Date => {
        if (!fechaStr || typeof fechaStr !== 'string') {
            return new Date();
        }

        const partes = fechaStr.split('-').map(Number);

        if (partes.length < 3 || partes.some(isNaN)) {
            return new Date();
        }

        return new Date(partes[0], partes[1] - 1, partes[2], 12, 0, 0);
    };

    const calcularNoches = () => {
        if (!fechaCheckIn || !fechaCheckOut) {
            return 0;
        }

        const d1 = construirFechaLocal(fechaCheckIn);
        const d2 = construirFechaLocal(fechaCheckOut);
        const diffMs = d2.getTime() - d1.getTime();

        return Math.max(0, Math.ceil(diffMs / (1000 * 60 * 60 * 24)));
    };

    const fechaSeleccionada: DateRange | undefined =
        fechaCheckIn || fechaCheckOut
            ? {
                  from: fechaCheckIn
                      ? construirFechaLocal(fechaCheckIn)
                      : undefined,
                  to: fechaCheckOut
                      ? construirFechaLocal(fechaCheckOut)
                      : undefined,
              }
            : undefined;

    const fechaEstaAgotada = (fecha: Date): boolean => {
        if (!fecha || !(fecha instanceof Date) || isNaN(fecha.getTime())) {
            return false;
        }

        // Si la categoría completa no posee habitaciones activas / disponibles
        if (
            totalHabitacionesCategoria !== undefined &&
            totalHabitacionesCategoria <= 0
        ) {
            return true;
        }

        const fechaStr = formatearFechaSegura(fecha);

        if (!fechaStr) {
            return false;
        }

        if (diasAgotadosSet.has(fechaStr)) {
            return true;
        }

        const ocup = ocupacionHabitacionPorDia[fechaStr];

        return ocup ? ocup.disponibles <= 0 : false;
    };

    const obtenerPrimerFechaAgotadaDespuesDe = (checkIn: Date): Date | null => {
        if (
            !checkIn ||
            !(checkIn instanceof Date) ||
            isNaN(checkIn.getTime())
        ) {
            return null;
        }

        const cursor = new Date(
            checkIn.getFullYear(),
            checkIn.getMonth(),
            checkIn.getDate(),
            12,
            0,
            0,
        );

        for (let i = 0; i < 180; i++) {
            if (fechaEstaAgotada(cursor)) {
                return cursor;
            }

            cursor.setDate(cursor.getDate() + 1);
        }

        return null;
    };

    const esFechaDeshabilitada = (fecha: Date): boolean => {
        if (!fecha || !(fecha instanceof Date) || isNaN(fecha.getTime())) {
            return true;
        }

        const fechaStr = formatearFechaSegura(fecha);

        if (!fechaStr) {
            return true;
        }

        // Deshabilitar días pasados (anteriores a hoy en YYYY-MM-DD)
        if (fechaStr < hoyStr) {
            return true;
        }

        // Deshabilitar días sin disponibilidad de habitaciones
        if (fechaEstaAgotada(fecha)) {
            return true;
        }

        // Deshabilitar días anteriores al check-in cuando solo check-in está seleccionado
        if (fechaCheckIn && !fechaCheckOut) {
            if (fechaStr < fechaCheckIn) {
                return true;
            }

            const checkInDate = construirFechaLocal(fechaCheckIn);
            const primerAgotada =
                obtenerPrimerFechaAgotadaDespuesDe(checkInDate);

            if (primerAgotada) {
                const primerAgotadaStr = formatearFechaSegura(primerAgotada);

                if (fechaStr > primerAgotadaStr) {
                    return true;
                }
            }
        }

        return false;
    };

    const tieneAlgunaNocheAgotada = (inicio: Date, fin: Date): boolean => {
        const cursor = new Date(
            inicio.getFullYear(),
            inicio.getMonth(),
            inicio.getDate(),
            12,
            0,
            0,
        );
        const limite = new Date(
            fin.getFullYear(),
            fin.getMonth(),
            fin.getDate(),
            12,
            0,
            0,
        );

        while (cursor < limite) {
            if (fechaEstaAgotada(cursor)) {
                return true;
            }

            cursor.setDate(cursor.getDate() + 1);
        }

        return false;
    };

    const seleccionarRangoFechas = (rango?: DateRange) => {
        if (!rango || !rango.from) {
            onSelectFechas('', '');

            return;
        }

        const fromStr = formatearFechaSegura(rango.from);

        if (fechaCheckIn && fechaCheckOut) {
            if (esFechaDeshabilitada(rango.from)) {
                onSelectFechas('', '');

                return;
            }

            onSelectFechas(fromStr, '');

            return;
        }

        if (fechaCheckIn && !fechaCheckOut) {
            const checkInDate = construirFechaLocal(fechaCheckIn);
            const targetDate = rango.to ? rango.to : rango.from;
            const targetStr = formatearFechaSegura(targetDate);

            if (targetStr <= fechaCheckIn) {
                if (esFechaDeshabilitada(targetDate)) {
                    onSelectFechas('', '');

                    return;
                }

                onSelectFechas(targetStr, '');

                return;
            }

            if (tieneAlgunaNocheAgotada(checkInDate, targetDate)) {
                alert(
                    'El rango seleccionado contiene días sin disponibilidad. Por favor elija un rango continuo disponible.',
                );
                onSelectFechas(fechaCheckIn, '');

                return;
            }

            onSelectFechas(fechaCheckIn, targetStr);

            return;
        }

        if (esFechaDeshabilitada(rango.from)) {
            return;
        }

        onSelectFechas(fromStr, '');
    };

    const limpiarSeleccionFechas = () => {
        onSelectFechas('', '');
    };

    const disponibilidadEntrada = fechaCheckIn
        ? (ocupacionHabitacionPorDia[fechaCheckIn] ?? null)
        : null;

    return {
        mesesCalendario,
        fechaSeleccionada,
        esFechaDeshabilitada,
        fechaEstaAgotada,
        seleccionarRangoFechas,
        limpiarSeleccionFechas,
        agregarDiasAgotados,
        rangoTieneNochesAgotadas: (checkIn: string, checkOut: string) => {
            if (!checkIn || !checkOut) {
                return false;
            }

            return tieneAlgunaNocheAgotada(
                construirFechaLocal(checkIn),
                construirFechaLocal(checkOut),
            );
        },
        nochesCalculadas: calcularNoches(),
        disponibilidadEntrada,
    };
}
