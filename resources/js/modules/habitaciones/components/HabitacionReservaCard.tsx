import { Link } from '@inertiajs/react';
import {
    CalendarDays,
    CalendarCheck,
    Users,
    MessageCircle,
    Loader2,
    AlertCircle,
    ArrowRight,
    ShieldCheck,
} from 'lucide-react';
import type {
    BeneficioClienteItem,
    ServicioAdicionalItem,
} from '@/modules/reservas/types';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/modules/shared/components/ui/select';
import { useHabitacionReservaForm } from '../hooks/useHabitacionReservaForm';
import type { HabitacionDetalleData } from '../types';

interface HabitacionReservaCardProps {
    room: HabitacionDetalleData;
    telefonoWhatsApp?: string;
    diasAgotados?: string[];
    serviciosDisponibles?: ServicioAdicionalItem[];
    beneficiosCliente?: BeneficioClienteItem[];
}

export const HabitacionReservaCard = ({
    room,
    telefonoWhatsApp,
    diasAgotados = [],
}: HabitacionReservaCardProps) => {
    const {
        register,
        handleSubmit,
        setValue,
        errors,
        isSubmitting,
        noches,
        precioNoche,
        totalEstimado,
        checkIn,
        checkOut,
        huespedes,
        tieneConflictoDisponibilidad,
    } = useHabitacionReservaForm({
        room,
        telefonoWhatsApp,
        diasAgotados,
    });

    const hoy = new Date().toISOString().split('T')[0];
    const moneda = room.moneda || '$';

    const formatearFechaDisplay = (str?: string) => {
        if (!str) {
            return 'Seleccionar';
        }

        try {
            const [, m, d] = str.split('-');
            const meses = [
                'ene',
                'feb',
                'mar',
                'abr',
                'may',
                'jun',
                'jul',
                'ago',
                'sep',
                'oct',
                'nov',
                'dic',
            ];
            const numMes = parseInt(m, 10) - 1;

            return `${parseInt(d, 10)} ${meses[numMes] || ''}`;
        } catch {
            return str;
        }
    };

    const urlContinuarReserva = `/habitaciones/${room.slug || room.id}/reservar?check_in=${checkIn}&check_out=${checkOut}&huespedes=${huespedes}`;

    return (
        <>
            {/* Desktop Sticky Card */}
            <div className="sticky top-24 rounded-3xl border border-border bg-card p-6 font-sans shadow-xl">
                {/* Header de Estancia */}
                <div className="flex items-center justify-between border-b border-border/70 pb-4">
                    <div>
                        <span className="text-[11px] font-black tracking-wider text-primary uppercase dark:text-rose-400">
                            Tu Estancia
                        </span>
                        <h3 className="mt-0.5 text-base font-black text-foreground">
                            {room.nombre}
                        </h3>
                    </div>
                    <div className="rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-black text-emerald-600 dark:text-emerald-400">
                        Disponible
                    </div>
                </div>

                {/* Precio Base por Noche */}
                <div className="mt-4 flex items-baseline justify-between">
                    <div className="flex items-baseline gap-1">
                        <span className="text-3xl font-black tracking-tight text-foreground">
                            {moneda}
                            {precioNoche.toLocaleString()}
                        </span>
                        <span className="text-xs font-bold text-muted-foreground">
                            / noche
                        </span>
                    </div>
                    <span className="text-[11px] font-medium text-muted-foreground">
                        Impuestos incluidos
                    </span>
                </div>

                {/* Formulario de Configuración de Fechas */}
                <form onSubmit={handleSubmit} className="mt-5 space-y-4">
                    {/* Selector de Rango de Fechas */}
                    <div className="grid grid-cols-2 gap-2">
                        {/* Llegada */}
                        <div className="rounded-2xl border border-border/80 bg-background p-2.5 transition-colors focus-within:border-primary/50">
                            <label className="flex items-center gap-1 text-[10px] font-black text-muted-foreground uppercase">
                                <CalendarDays className="size-3 text-primary dark:text-rose-400" />
                                <span>Entrada</span>
                            </label>
                            <Input
                                type="date"
                                min={hoy}
                                value={checkIn}
                                {...register('check_in')}
                                className="mt-0.5 h-7 border-none bg-transparent p-0 text-xs font-bold text-foreground focus-visible:ring-0"
                            />
                        </div>

                        {/* Salida */}
                        <div className="rounded-2xl border border-border/80 bg-background p-2.5 transition-colors focus-within:border-primary/50">
                            <label className="flex items-center gap-1 text-[10px] font-black text-muted-foreground uppercase">
                                <CalendarCheck className="size-3 text-primary dark:text-rose-400" />
                                <span>Salida</span>
                            </label>
                            <Input
                                type="date"
                                min={checkIn || hoy}
                                value={checkOut}
                                {...register('check_out')}
                                className="mt-0.5 h-7 border-none bg-transparent p-0 text-xs font-bold text-foreground focus-visible:ring-0"
                            />
                        </div>
                    </div>

                    {(errors.check_in || errors.check_out) && (
                        <p className="text-[11px] font-bold text-destructive">
                            {errors.check_in?.message ||
                                errors.check_out?.message}
                        </p>
                    )}

                    {tieneConflictoDisponibilidad && (
                        <div className="flex items-center gap-2 rounded-2xl border border-destructive/30 bg-destructive/10 p-3 text-xs font-bold text-destructive">
                            <AlertCircle className="size-4 shrink-0" />
                            <span>
                                Fechas agotadas para esta categoría. Prueba otro
                                rango.
                            </span>
                        </div>
                    )}

                    {/* Selector de Huéspedes */}
                    <div className="rounded-2xl border border-border/80 bg-background p-2.5 transition-colors focus-within:border-primary/50">
                        <label className="flex items-center gap-1 text-[10px] font-black text-muted-foreground uppercase">
                            <Users className="size-3 text-primary dark:text-rose-400" />
                            <span>Huéspedes</span>
                        </label>
                        <Select
                            value={huespedes}
                            onValueChange={(val) => setValue('huespedes', val)}
                        >
                            <SelectTrigger className="mt-0.5 h-7 border-none bg-transparent p-0 text-xs font-bold text-foreground shadow-none focus-visible:ring-0">
                                <SelectValue placeholder="Cantidad de huéspedes" />
                            </SelectTrigger>
                            <SelectContent>
                                {Array.from({
                                    length: room.capacidad || 4,
                                }).map((_, i) => (
                                    <SelectItem
                                        key={i + 1}
                                        value={String(i + 1)}
                                    >
                                        {i + 1}{' '}
                                        {i + 1 === 1 ? 'huésped' : 'huéspedes'}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {/* Resumen Económico de la Estancia */}
                    <div className="space-y-2 rounded-2xl border border-border/70 bg-muted/20 p-4 text-xs">
                        <div className="flex justify-between text-muted-foreground">
                            <span>
                                {moneda}
                                {precioNoche.toLocaleString()} × {noches}{' '}
                                {noches === 1 ? 'noche' : 'noches'}
                            </span>
                            <span className="font-bold text-foreground">
                                {moneda}
                                {totalEstimado.toLocaleString()}
                            </span>
                        </div>
                        <div className="flex justify-between text-muted-foreground">
                            <span>Acceso a piscina & WiFi</span>
                            <span className="font-bold text-emerald-600 dark:text-emerald-400">
                                Incluido
                            </span>
                        </div>
                        <div className="flex items-baseline justify-between border-t border-border/70 pt-2 text-sm font-black text-foreground">
                            <span>Total estimado</span>
                            <span className="text-lg text-primary dark:text-rose-400">
                                {moneda}
                                {totalEstimado.toLocaleString()}
                            </span>
                        </div>
                    </div>

                    {/* Botones de Acción */}
                    <div className="space-y-2 pt-1">
                        {/* Botón Principal: Continuar a la Reserva */}
                        <Link
                            href={urlContinuarReserva}
                            className={`inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-primary py-3.5 text-xs font-black text-primary-foreground shadow-lg transition-all hover:bg-primary/90 active:scale-95 ${
                                tieneConflictoDisponibilidad
                                    ? 'pointer-events-none opacity-50'
                                    : ''
                            }`}
                        >
                            <span>Continuar con la reserva</span>
                            <ArrowRight className="size-4" />
                        </Link>

                        {/* Botón Secundario: WhatsApp */}
                        <Button
                            type="submit"
                            variant="outline"
                            disabled={isSubmitting}
                            className="w-full cursor-pointer rounded-2xl border-border bg-card py-3 text-xs font-bold text-foreground shadow-xs transition-colors hover:bg-muted"
                        >
                            {isSubmitting ? (
                                <>
                                    <Loader2 className="mr-2 size-4 animate-spin" />
                                    <span>Conectando...</span>
                                </>
                            ) : (
                                <>
                                    <MessageCircle className="mr-2 size-4 text-emerald-600 dark:text-emerald-400" />
                                    <span>Consultar al Concierge</span>
                                </>
                            )}
                        </Button>
                    </div>

                    {/* Micro-garantías */}
                    <div className="flex items-center justify-center gap-1.5 text-[11px] font-bold text-muted-foreground">
                        <ShieldCheck className="size-3.5 text-emerald-600 dark:text-emerald-400" />
                        <span>
                            Sin cargos sorpresa • Confirmación inmediata
                        </span>
                    </div>
                </form>
            </div>

            {/* Mobile Fixed Bottom CTA Bar (< 1024px) */}
            <div className="fixed right-0 bottom-0 left-0 z-40 border-t border-border bg-card/95 px-4 py-3 shadow-2xl backdrop-blur-xl lg:hidden">
                <div className="container mx-auto flex items-center justify-between gap-3">
                    <div>
                        <div className="flex items-baseline gap-1">
                            <span className="text-base font-black text-foreground">
                                {moneda}
                                {totalEstimado.toLocaleString()}
                            </span>
                            <span className="text-[11px] font-medium text-muted-foreground">
                                · {noches} {noches === 1 ? 'noche' : 'noches'}
                            </span>
                        </div>
                        <span className="text-[10px] text-muted-foreground">
                            {checkIn
                                ? `${formatearFechaDisplay(checkIn)} → ${formatearFechaDisplay(checkOut)}`
                                : 'Fechas flexibles'}
                        </span>
                    </div>

                    <Link
                        href={urlContinuarReserva}
                        className={`inline-flex items-center justify-center gap-1.5 rounded-2xl bg-primary px-5 py-3 text-xs font-black text-primary-foreground shadow-md transition-all active:scale-95 ${
                            tieneConflictoDisponibilidad
                                ? 'pointer-events-none opacity-50'
                                : ''
                        }`}
                    >
                        <span>Reservar</span>
                        <ArrowRight className="size-3.5" />
                    </Link>
                </div>
            </div>
        </>
    );
};

export default HabitacionReservaCard;
