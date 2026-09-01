import { Button } from '@/modules/shared/components/ui/button';
import type { DiaCalendarioInfo } from '../hooks/useCalendarioRango';

interface CalendarioDiaBotonProps {
    diaInfo: DiaCalendarioInfo;
    checkIn: string;
    checkOut: string;
    hoverFecha: string | null;
    primerDiaAgotadoPosterior: string | null;
    onDiaClick: (fechaStr: string, esDeshabilitado: boolean) => void;
    onHoverChange: (fechaStr: string | null) => void;
}

export const CalendarioDiaBoton = ({
    diaInfo,
    checkIn,
    checkOut,
    hoverFecha,
    primerDiaAgotadoPosterior,
    onDiaClick,
    onHoverChange,
}: CalendarioDiaBotonProps) => {
    const { dia, fechaStr, esAgotado, esPasado } = diaInfo;

    const esBloqueadoPorRango = Boolean(
        checkIn &&
        !checkOut &&
        primerDiaAgotadoPosterior &&
        fechaStr > primerDiaAgotadoPosterior,
    );
    const esDeshabilitado = esPasado || esAgotado || esBloqueadoPorRango;
    const esCheckIn = checkIn === fechaStr;
    const esCheckOut = checkOut === fechaStr;
    const estaEnRango =
        checkIn && checkOut && fechaStr > checkIn && fechaStr < checkOut;
    const estaEnHover =
        checkIn &&
        !checkOut &&
        hoverFecha &&
        fechaStr > checkIn &&
        fechaStr <= hoverFecha &&
        !esDeshabilitado;

    let claseBoton =
        'relative flex h-9 w-full items-center justify-center p-0 text-xs font-bold transition-all';

    if (esDeshabilitado) {
        claseBoton +=
            ' cursor-not-allowed text-muted-foreground/35 bg-muted/15 line-through rounded-xl';
    } else if (esCheckIn || esCheckOut) {
        claseBoton +=
            ' bg-primary text-primary-foreground font-black shadow-md z-10 scale-105 rounded-xl hover:bg-primary hover:text-primary-foreground';
    } else if (estaEnRango) {
        claseBoton +=
            ' bg-primary/15 text-primary dark:bg-rose-950/40 dark:text-rose-200 rounded-none first:rounded-l-xl last:rounded-r-xl hover:bg-primary/20';
    } else if (estaEnHover) {
        claseBoton +=
            ' bg-primary/10 text-primary rounded-none first:rounded-l-xl last:rounded-r-xl';
    } else {
        claseBoton +=
            ' hover:bg-primary/10 hover:text-primary text-foreground rounded-xl';
    }

    return (
        <Button
            type="button"
            variant="ghost"
            disabled={esDeshabilitado}
            onClick={() => onDiaClick(fechaStr, esDeshabilitado)}
            onMouseEnter={() => onHoverChange(fechaStr)}
            onMouseLeave={() => onHoverChange(null)}
            className={claseBoton}
            title={
                esAgotado
                    ? 'Agotado en esta categoría'
                    : esPasado
                      ? 'Fecha pasada'
                      : fechaStr
            }
        >
            <span>{dia}</span>
            {esAgotado && (
                <span className="absolute -bottom-0.5 size-1 rounded-full bg-destructive" />
            )}
        </Button>
    );
};
