interface UseReservaDisponibilidadProps {
    checkIn: string;
    checkOut: string;
    diasAgotados?: string[];
}

export const useReservaDisponibilidad = ({
    checkIn,
    checkOut,
    diasAgotados = [],
}: UseReservaDisponibilidadProps) => {
    if (!checkIn || !checkOut || diasAgotados.length === 0) {
        return { tieneConflictoFechas: false };
    }

    const d1 = new Date(checkIn);
    const d2 = new Date(checkOut);
    let tieneConflictoFechas = false;

    for (let d = new Date(d1); d < d2; d.setDate(d.getDate() + 1)) {
        const fStr = d.toISOString().split('T')[0];

        if (diasAgotados.includes(fStr)) {
            tieneConflictoFechas = true;
            break;
        }
    }

    return {
        tieneConflictoFechas,
    };
};

export default useReservaDisponibilidad;
