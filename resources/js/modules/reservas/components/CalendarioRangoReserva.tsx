import { ChevronLeft, ChevronRight, Ban, CheckCircle2 } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import { DIAS_SEMANA, useCalendarioRango } from '../hooks/useCalendarioRango';
import type {
    DiaCalendarioInfo,
    MesCalendarioInfo,
} from '../hooks/useCalendarioRango';

interface CalendarioDiaBotonProps {
    diaInfo: DiaCalendarioInfo;
    checkIn: string;
    checkOut: string;
    hoverFecha: string | null;
    primerDiaAgotadoPosterior: string | null;
    onDiaClick: (fechaStr: string, esDeshabilitado: boolean) => void;
    onHoverChange: (fechaStr: string | null) => void;
}

const CalendarioDiaBoton = ({
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

interface CalendarioMesGrillaProps {
    datosMes: MesCalendarioInfo;
    checkIn: string;
    checkOut: string;
    hoverFecha: string | null;
    primerDiaAgotadoPosterior: string | null;
    onDiaClick: (fechaStr: string, esDeshabilitado: boolean) => void;
    onHoverChange: (fechaStr: string | null) => void;
}

const CalendarioMesGrilla = ({
    datosMes,
    checkIn,
    checkOut,
    hoverFecha,
    primerDiaAgotadoPosterior,
    onDiaClick,
    onHoverChange,
}: CalendarioMesGrillaProps) => {
    return (
        <div className="flex-1 space-y-3">
            <div className="text-center text-sm font-black tracking-tight text-foreground">
                {datosMes.nombreMes} {datosMes.anio}
            </div>

            <div className="grid grid-cols-7 gap-1 text-center text-[10px] font-black text-muted-foreground">
                {DIAS_SEMANA.map((d) => (
                    <div key={d} className="py-1">
                        {d}
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-7 gap-1">
                {Array.from({ length: datosMes.diaInicioSemana }).map(
                    (_, i) => (
                        <div key={`empty-${i}`} className="h-9" />
                    ),
                )}

                {datosMes.dias.map((diaInfo) => (
                    <CalendarioDiaBoton
                        key={diaInfo.fechaStr}
                        diaInfo={diaInfo}
                        checkIn={checkIn}
                        checkOut={checkOut}
                        hoverFecha={hoverFecha}
                        primerDiaAgotadoPosterior={primerDiaAgotadoPosterior}
                        onDiaClick={onDiaClick}
                        onHoverChange={onHoverChange}
                    />
                ))}
            </div>
        </div>
    );
};

const CalendarioLeyenda = () => (
    <div className="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-border/60 pt-4 text-xs font-bold text-muted-foreground">
        <div className="flex items-center gap-2">
            <span className="size-2.5 rounded-full bg-primary" />
            <span>Fechas seleccionadas</span>
        </div>
        <div className="flex items-center gap-2">
            <span className="size-2.5 rounded-full bg-destructive" />
            <span>Agotado / No disponible</span>
        </div>
        <div className="flex items-center gap-2">
            <CheckCircle2 className="size-3.5 text-emerald-600 dark:text-emerald-400" />
            <span>Disponible para reserva</span>
        </div>
    </div>
);

interface CalendarioRangoReservaProps {
    checkIn: string;
    checkOut: string;
    diasAgotados?: string[];
    onSelectRango: (checkIn: string, checkOut: string) => void;
}

export const CalendarioRangoReserva = ({
    checkIn,
    checkOut,
    diasAgotados = [],
    onSelectRango,
}: CalendarioRangoReservaProps) => {
    const {
        mes1,
        mes2,
        puedeRetroceder,
        cambiarMes,
        handleDiaClick,
        hoverFecha,
        setHoverFecha,
        errorRango,
        primerDiaAgotadoPosterior,
    } = useCalendarioRango({
        checkIn,
        checkOut,
        diasAgotados,
        onSelectRango,
    });

    return (
        <div className="rounded-3xl border border-border bg-card p-4 font-sans shadow-sm sm:p-6">
            {/* Header con controles de navegación */}
            <div className="flex items-center justify-between border-b border-border/70 pb-4">
                <div className="text-xs font-black tracking-wider text-muted-foreground uppercase">
                    Calendario de Disponibilidad
                </div>
                <div className="flex items-center gap-1.5">
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        disabled={!puedeRetroceder}
                        onClick={() => cambiarMes(-1)}
                        className="size-8 rounded-xl border-border bg-background hover:bg-muted disabled:cursor-not-allowed disabled:opacity-30"
                    >
                        <ChevronLeft className="size-4" />
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        onClick={() => cambiarMes(1)}
                        className="size-8 rounded-xl border-border bg-background hover:bg-muted"
                    >
                        <ChevronRight className="size-4" />
                    </Button>
                </div>
            </div>

            {/* Grillas de 2 Meses */}
            <div className="mt-4 grid grid-cols-1 gap-8 md:grid-cols-2">
                <CalendarioMesGrilla
                    datosMes={mes1}
                    checkIn={checkIn}
                    checkOut={checkOut}
                    hoverFecha={hoverFecha}
                    primerDiaAgotadoPosterior={primerDiaAgotadoPosterior}
                    onDiaClick={handleDiaClick}
                    onHoverChange={setHoverFecha}
                />
                <div className="hidden border-l border-border/60 pl-8 md:block">
                    <CalendarioMesGrilla
                        datosMes={mes2}
                        checkIn={checkIn}
                        checkOut={checkOut}
                        hoverFecha={hoverFecha}
                        primerDiaAgotadoPosterior={primerDiaAgotadoPosterior}
                        onDiaClick={handleDiaClick}
                        onHoverChange={setHoverFecha}
                    />
                </div>
            </div>

            {/* Error de Rango */}
            {errorRango && (
                <div className="mt-4 flex items-center gap-2 rounded-2xl border border-destructive/30 bg-destructive/10 p-3 text-xs font-bold text-destructive">
                    <Ban className="size-4 shrink-0" />
                    <span>{errorRango}</span>
                </div>
            )}

            {/* Leyenda Visual */}
            <CalendarioLeyenda />
        </div>
    );
};

export default CalendarioRangoReserva;
