import { ShieldCheck } from 'lucide-react';
import type { UseFormSetValue } from 'react-hook-form';
import {
    RadioGroup,
    RadioGroupItem,
} from '@/modules/shared/components/ui/radio-group';
import type { CrearReservaFormValues } from '../schemas/crearReservaSchema';
import type { PoliticaReserva } from '../types';

interface ReservaPasoPagoProps {
    politicas?: PoliticaReserva[];
    canalPago: string;
    tipoPago: string;
    totalNeto: number;
    porcentajeAnticipoPolitica?: number;
    moneda: string;
    esCorporativo: boolean;
    tieneBeneficioAnticipoReducido: boolean;
    setValue: UseFormSetValue<CrearReservaFormValues>;
}

export const ReservaPasoPago = ({
    politicas = [],
    canalPago,
    tipoPago,
    totalNeto,
    porcentajeAnticipoPolitica = 50,
    moneda,
    esCorporativo,
    tieneBeneficioAnticipoReducido,
    setValue,
}: ReservaPasoPagoProps) => {
    const montoAnticipo = Number(
        ((totalNeto * porcentajeAnticipoPolitica) / 100).toFixed(2),
    );
    const montoRestante = Math.max(
        0,
        Number((totalNeto - montoAnticipo).toFixed(2)),
    );

    const valorSeleccionado =
        canalPago === 'sin_pago'
            ? 'sin_pago'
            : tipoPago === 'abono_50'
              ? 'abono_50'
              : 'pago_completo';

    const handleCambioModalidad = (val: string) => {
        if (val === 'sin_pago') {
            setValue('canal_pago_reserva', 'sin_pago');
            setValue('tipo_pago_reserva', 'sin_pago');
        } else if (val === 'abono_50') {
            setValue('canal_pago_reserva', 'stripe');
            setValue('tipo_pago_reserva', 'abono_50');
        } else {
            setValue('canal_pago_reserva', 'stripe');
            setValue('tipo_pago_reserva', 'pago_completo');
        }
    };

    return (
        <div className="animate-in fade-in space-y-6 duration-200">
            <div className="space-y-6 rounded-3xl border border-border bg-card p-6 shadow-sm">
                <div>
                    <h2 className="text-lg font-black tracking-tight text-foreground">
                        Garantía de Reserva & Modalidad de Pago
                    </h2>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                        Elige si deseas liquidar tu estancia completa o asegurar
                        tu suite abonando el anticipo fijado por la política.
                    </p>
                </div>

                {/* Políticas de la Habitación desde BD */}
                {politicas && politicas.length > 0 && (
                    <div className="space-y-2 rounded-2xl border border-border bg-background p-4">
                        <div className="flex items-center gap-2 text-xs font-black text-foreground">
                            <ShieldCheck className="size-4 text-primary dark:text-rose-400" />
                            <span>
                                Políticas de Garantía & Cancelación de la Suite:
                            </span>
                        </div>
                        <div className="space-y-1.5">
                            {politicas.map(
                                (pol: PoliticaReserva, idx: number) => (
                                    <div
                                        key={pol.id || idx}
                                        className="text-xs text-muted-foreground"
                                    >
                                        <span className="font-bold text-foreground">
                                            {pol.titulo || pol.nombre}:
                                        </span>{' '}
                                        {pol.descripcion}
                                    </div>
                                ),
                            )}
                        </div>
                    </div>
                )}

                {/* Selector de Modalidad */}
                <RadioGroup
                    value={valorSeleccionado}
                    onValueChange={handleCambioModalidad}
                    className="space-y-3"
                >
                    <div className="text-xs font-bold text-foreground">
                        Selecciona la modalidad de pago para confirmar:
                    </div>

                    {/* Opción 1: Pago 100% en Línea */}
                    <label
                        htmlFor="radio-pago-completo"
                        className={`flex cursor-pointer items-center justify-between rounded-2xl border p-4 transition-all ${
                            valorSeleccionado === 'pago_completo'
                                ? 'border-primary bg-primary/5 shadow-sm dark:bg-rose-950/20'
                                : 'border-border bg-background hover:border-primary/40'
                        }`}
                    >
                        <div className="flex items-center gap-3">
                            <RadioGroupItem
                                id="radio-pago-completo"
                                value="pago_completo"
                            />
                            <div>
                                <div className="flex items-center gap-2">
                                    <div className="text-xs font-black text-foreground">
                                        Pagar 100% de la Estancia (Pago
                                        Completo)
                                    </div>
                                    <span className="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[9px] font-black text-emerald-700 dark:text-emerald-400">
                                        Liquidación Total
                                    </span>
                                </div>
                                <div className="text-[11px] text-muted-foreground">
                                    Confirmación inmediata y estancia 100%
                                    saldada para un check-in express.
                                </div>
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="text-xs font-black text-foreground">
                                {moneda}
                                {totalNeto.toFixed(2)}
                            </div>
                            <div className="text-[10px] text-muted-foreground">
                                hoy
                            </div>
                        </div>
                    </label>

                    {/* Opción 2: Anticipo fijado por la Política */}
                    <label
                        htmlFor="radio-abono-50"
                        className={`flex cursor-pointer items-center justify-between rounded-2xl border p-4 transition-all ${
                            valorSeleccionado === 'abono_50'
                                ? 'border-primary bg-primary/5 shadow-sm dark:bg-rose-950/20'
                                : 'border-border bg-background hover:border-primary/40'
                        }`}
                    >
                        <div className="flex items-center gap-3">
                            <RadioGroupItem
                                id="radio-abono-50"
                                value="abono_50"
                            />
                            <div>
                                <div className="flex items-center gap-2">
                                    <div className="text-xs font-black text-foreground">
                                        Pagar Anticipo de Política (
                                        {porcentajeAnticipoPolitica}% de
                                        Garantía)
                                    </div>
                                    <span className="rounded-full bg-primary/10 px-2 py-0.5 text-[9px] font-black text-primary dark:text-rose-400">
                                        Garantía de Reserva
                                    </span>
                                </div>
                                <div className="text-[11px] text-muted-foreground">
                                    Asegura tu suite pagando {moneda}
                                    {montoAnticipo.toFixed(2)} hoy y el resto (
                                    {moneda}
                                    {montoRestante.toFixed(2)}) al hacer
                                    check-in.
                                </div>
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="text-xs font-black text-foreground">
                                {moneda}
                                {montoAnticipo.toFixed(2)}
                            </div>
                            <div className="text-[10px] text-muted-foreground">
                                hoy
                            </div>
                        </div>
                    </label>

                    {/* Opción 3: Anticipo 0% para VIP / Corporativo */}
                    {tieneBeneficioAnticipoReducido && (
                        <label
                            htmlFor="radio-sin-pago"
                            className={`flex cursor-pointer items-center justify-between rounded-2xl border p-4 transition-all ${
                                valorSeleccionado === 'sin_pago'
                                    ? 'border-primary bg-primary/5 dark:bg-rose-950/20'
                                    : 'border-border bg-background hover:border-primary/40'
                            }`}
                        >
                            <div className="flex items-center gap-3">
                                <RadioGroupItem
                                    id="radio-sin-pago"
                                    value="sin_pago"
                                />
                                <div>
                                    <div className="flex items-center gap-2">
                                        <div className="text-xs font-black text-foreground">
                                            {esCorporativo
                                                ? 'Cargo a Cuenta Corporativa de Crédito'
                                                : 'Pagar al llegar al Hotel (Check-in)'}
                                        </div>
                                        <span className="rounded-full bg-amber-500/10 px-2 py-0.5 text-[9px] font-black text-amber-700 dark:text-amber-300">
                                            Beneficio Exclusivo
                                        </span>
                                    </div>
                                    <div className="text-[11px] text-muted-foreground">
                                        {esCorporativo
                                            ? 'Facturación a crédito autorizada.'
                                            : 'Beneficio de anticipo 0% concedido por tu cuenta VIP.'}
                                    </div>
                                </div>
                            </div>
                            <span className="text-xs font-bold text-muted-foreground">
                                {moneda}0.00 hoy
                            </span>
                        </label>
                    )}
                </RadioGroup>
            </div>
        </div>
    );
};

export default ReservaPasoPago;
