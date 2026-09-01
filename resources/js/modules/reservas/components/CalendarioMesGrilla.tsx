import { DIAS_SEMANA } from '../hooks/useCalendarioRango';
import type { MesCalendarioInfo } from '../hooks/useCalendarioRango';
import { CalendarioDiaBoton } from './CalendarioDiaBoton';

interface CalendarioMesGrillaProps {
    datosMes: MesCalendarioInfo;
    checkIn: string;
    checkOut: string;
    hoverFecha: string | null;
    primerDiaAgotadoPosterior: string | null;
    onDiaClick: (fechaStr: string, esDeshabilitado: boolean) => void;
    onHoverChange: (fechaStr: string | null) => void;
}

export const CalendarioMesGrilla = ({
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

            {/* Días de la Semana */}
            <div className="grid grid-cols-7 gap-1 text-center text-[10px] font-black text-muted-foreground">
                {DIAS_SEMANA.map((d) => (
                    <div key={d} className="py-1">
                        {d}
                    </div>
                ))}
            </div>

            {/* Grilla de Días */}
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
