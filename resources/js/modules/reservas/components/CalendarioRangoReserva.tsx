import { ChevronLeft, ChevronRight, Ban } from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import { useCalendarioRango } from '../hooks/useCalendarioRango';
import { CalendarioLeyenda } from './CalendarioLeyenda';
import { CalendarioMesGrilla } from './CalendarioMesGrilla';

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

            {/* Grillas de 2 Meses (Lado a lado en Desktop, 1 Mes en Mobile) */}
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
