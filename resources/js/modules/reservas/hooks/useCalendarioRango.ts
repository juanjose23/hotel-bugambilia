import { useState, useMemo, useCallback } from 'react';

export interface DiaCalendarioInfo {
    dia: number;
    fechaStr: string;
    esAgotado: boolean;
    esPasado: boolean;
}

export interface MesCalendarioInfo {
    anio: number;
    mes: number;
    nombreMes: string;
    diaInicioSemana: number;
    dias: DiaCalendarioInfo[];
}

export const NOMBRES_MESES = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre',
];

export const DIAS_SEMANA = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'];

interface UseCalendarioRangoProps {
    checkIn: string;
    checkOut: string;
    diasAgotados?: string[];
    onSelectRango: (checkIn: string, checkOut: string) => void;
}

export const useCalendarioRango = ({
    checkIn,
    checkOut,
    diasAgotados = [],
    onSelectRango,
}: UseCalendarioRangoProps) => {
    const hoy = useMemo(() => {
        const d = new Date();
        d.setHours(0, 0, 0, 0);

        return d;
    }, []);
    const hoyStr = useMemo(() => hoy.toISOString().split('T')[0], [hoy]);

    const diasAgotadosSet = useMemo(
        () => new Set(diasAgotados),
        [diasAgotados],
    );

    const calcularMesInicial = useCallback(() => {
        if (checkIn) {
            const [y, m] = checkIn.split('-').map(Number);

            if (y && m) {
                return new Date(y, m - 1, 1);
            }
        }

        const anio = hoy.getFullYear();
        const mes = hoy.getMonth();

        const ultimoDiaMesActual = new Date(anio, mes + 1, 0).getDate();
        let tieneDiaDisponible = false;

        for (let d = 1; d <= ultimoDiaMesActual; d++) {
            const mStr = String(mes + 1).padStart(2, '0');
            const dStr = String(d).padStart(2, '0');
            const fStr = `${anio}-${mStr}-${dStr}`;

            if (fStr >= hoyStr && !diasAgotadosSet.has(fStr)) {
                tieneDiaDisponible = true;
                break;
            }
        }

        if (!tieneDiaDisponible) {
            return new Date(anio, mes + 1, 1);
        }

        return new Date(anio, mes, 1);
    }, [checkIn, hoy, hoyStr, diasAgotadosSet]);

    const [fechaVista, setFechaVista] = useState<Date>(calcularMesInicial);
    const [prevCheckIn, setPrevCheckIn] = useState<string | undefined>(checkIn);

    if (checkIn !== prevCheckIn) {
        setPrevCheckIn(checkIn);

        if (checkIn) {
            const [y, m] = checkIn.split('-').map(Number);

            if (y && m) {
                setFechaVista(new Date(y, m - 1, 1));
            }
        }
    }

    const [hoverFecha, setHoverFecha] = useState<string | null>(null);
    const [errorRango, setErrorRango] = useState<string | null>(null);

    const primerDiaAgotadoPosterior = useMemo(() => {
        if (!checkIn || checkOut) {
            return null;
        }

        const agotadosFuturos = diasAgotados.filter((f) => f >= checkIn);

        if (agotadosFuturos.length === 0) {
            return null;
        }

        return agotadosFuturos.sort()[0];
    }, [checkIn, checkOut, diasAgotados]);

    const puedeRetroceder = useMemo(() => {
        const mesAnterior = new Date(
            fechaVista.getFullYear(),
            fechaVista.getMonth(),
            1,
        );
        const inicioActual = new Date(hoy.getFullYear(), hoy.getMonth(), 1);

        return mesAnterior > inicioActual;
    }, [fechaVista, hoy]);

    const cambiarMes = (offset: number) => {
        setFechaVista(
            (prev) => new Date(prev.getFullYear(), prev.getMonth() + offset, 1),
        );
        setErrorRango(null);
    };

    const generarMes = useCallback(
        (anio: number, mes: number): MesCalendarioInfo => {
            const primerDia = new Date(anio, mes, 1);
            const ultimoDia = new Date(anio, mes + 1, 0);
            const totalDias = ultimoDia.getDate();

            let diaInicioSemana = primerDia.getDay() - 1;

            if (diaInicioSemana === -1) {
                diaInicioSemana = 6;
            }

            const dias: DiaCalendarioInfo[] = [];

            for (let d = 1; d <= totalDias; d++) {
                const mesStr = String(mes + 1).padStart(2, '0');
                const diaStr = String(d).padStart(2, '0');
                const fechaStr = `${anio}-${mesStr}-${diaStr}`;
                const esPasado = fechaStr < hoyStr;
                const esAgotado = diasAgotadosSet.has(fechaStr);

                dias.push({ dia: d, fechaStr, esAgotado, esPasado });
            }

            return {
                anio,
                mes,
                nombreMes: NOMBRES_MESES[mes],
                diaInicioSemana,
                dias,
            };
        },
        [diasAgotadosSet, hoyStr],
    );

    const mes1 = useMemo(
        () => generarMes(fechaVista.getFullYear(), fechaVista.getMonth()),
        [fechaVista, generarMes],
    );

    const mes2Fecha = useMemo(
        () => new Date(fechaVista.getFullYear(), fechaVista.getMonth() + 1, 1),
        [fechaVista],
    );

    const mes2 = useMemo(
        () => generarMes(mes2Fecha.getFullYear(), mes2Fecha.getMonth()),
        [mes2Fecha, generarMes],
    );

    const handleDiaClick = (fechaStr: string, esDeshabilitado: boolean) => {
        if (esDeshabilitado) {
            return;
        }

        setErrorRango(null);

        if (!checkIn || (checkIn && checkOut)) {
            onSelectRango(fechaStr, '');

            return;
        }

        if (checkIn && !checkOut) {
            if (fechaStr <= checkIn) {
                onSelectRango(fechaStr, '');

                return;
            }

            const dInicio = new Date(checkIn);
            const dFin = new Date(fechaStr);
            let hayBloqueo = false;

            for (
                let d = new Date(dInicio);
                d < dFin;
                d.setDate(d.getDate() + 1)
            ) {
                const fStr = d.toISOString().split('T')[0];

                if (diasAgotadosSet.has(fStr)) {
                    hayBloqueo = true;
                    break;
                }
            }

            if (hayBloqueo) {
                setErrorRango(
                    'El rango seleccionado incluye fechas sin disponibilidad en esta categoría.',
                );
                onSelectRango(fechaStr, '');

                return;
            }

            onSelectRango(checkIn, fechaStr);
        }
    };

    return {
        mes1,
        mes2,
        puedeRetroceder,
        cambiarMes,
        handleDiaClick,
        hoverFecha,
        setHoverFecha,
        errorRango,
        primerDiaAgotadoPosterior,
    };
};
