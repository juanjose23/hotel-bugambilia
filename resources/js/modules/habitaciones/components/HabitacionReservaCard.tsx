import { Link } from '@inertiajs/react';
import {
    CalendarDays,
    CalendarCheck,
    Users,
    MessageCircle,
    Lock,
    Loader2,
    AlertCircle,
} from 'lucide-react';
import { useRef } from 'react';
import type {
    BeneficioClienteItem,
    ServicioAdicionalItem,
} from '@/modules/reservas/types';
import { Button } from '@/modules/shared/components/ui/button';
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

    const checkInInputRef = useRef<HTMLInputElement | null>(null);
    const checkOutInputRef = useRef<HTMLInputElement | null>(null);

    const { ref: rhfCheckInRef, ...checkInProps } = register('check_in');
    const { ref: rhfCheckOutRef, ...checkOutProps } = register('check_out');

    const formatearFecha = (str?: string) => {
        if (!str) {
            return null;
        }

        try {
            const [y, m, d] = str.split('-');

            if (!y || !m || !d) {
                return str;
            }

            return `${d}/${m}/${y}`;
        } catch {
            return str;
        }
    };

    const abrirPicker = (ref: React.RefObject<HTMLInputElement | null>) => {
        if (ref.current) {
            if (typeof ref.current.showPicker === 'function') {
                ref.current.showPicker();
            } else {
                ref.current.focus();
            }
        }
    };

    return (
        <>
            <div className="sticky top-24 rounded-3xl border border-border bg-card p-6 font-sans shadow-xl">
                {/* Precio Base por Noche */}
                <div className="flex items-baseline justify-between border-b border-border pb-5">
                    <div>
                        <div className="flex items-baseline gap-1">
                            <span className="text-3xl font-black tracking-tight text-foreground">
                                {room.moneda || '$'}
                                {precioNoche}
                            </span>
                            <span className="text-xs font-bold text-muted-foreground">
                                / noche
                            </span>
                        </div>
                        <span className="text-[11px] font-medium text-muted-foreground">
                            Impuestos incluidos • Cancelación flexible
                        </span>
                    </div>

                    <div className="rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-black text-emerald-600 dark:text-emerald-400">
                        Disponible
                    </div>
                </div>

                {/* Formulario Rápido de Reserva */}
                <form onSubmit={handleSubmit} className="mt-5 space-y-4">
                    {/* Selector de Rango de Fechas */}
                    <div className="grid grid-cols-2 gap-2">
                        {/* Llegada */}
                        <div
                            onClick={() => abrirPicker(checkInInputRef)}
                            role="button"
                            tabIndex={0}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    abrirPicker(checkInInputRef);
                                }
                            }}
                            className="relative cursor-pointer rounded-2xl border border-border bg-background p-3 transition-colors hover:border-primary/40"
                        >
                            <div className="flex items-center gap-1.5 text-[10px] font-black text-muted-foreground uppercase">
                                <CalendarDays className="size-3 text-primary dark:text-rose-400" />
                                <span>Llegada</span>
                            </div>
                            <div className="mt-1 truncate text-xs font-bold text-foreground">
                                {formatearFecha(checkIn) || 'dd/mm/aaaa'}
                            </div>
                            <input
                                type="date"
                                aria-label="Fecha de llegada"
                                min={new Date().toISOString().split('T')[0]}
                                {...checkInProps}
                                ref={(el) => {
                                    rhfCheckInRef(el);
                                    checkInInputRef.current = el;
                                }}
                                className="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            />
                        </div>

                        {/* Salida */}
                        <div
                            onClick={() => abrirPicker(checkOutInputRef)}
                            role="button"
                            tabIndex={0}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    abrirPicker(checkOutInputRef);
                                }
                            }}
                            className="relative cursor-pointer rounded-2xl border border-border bg-background p-3 transition-colors hover:border-primary/40"
                        >
                            <div className="flex items-center gap-1.5 text-[10px] font-black text-muted-foreground uppercase">
                                <CalendarCheck className="size-3 text-primary dark:text-rose-400" />
                                <span>Salida</span>
                            </div>
                            <div className="mt-1 truncate text-xs font-bold text-foreground">
                                {formatearFecha(checkOut) || 'dd/mm/aaaa'}
                            </div>
                            <input
                                type="date"
                                aria-label="Fecha de salida"
                                min={
                                    checkIn ||
                                    new Date().toISOString().split('T')[0]
                                }
                                {...checkOutProps}
                                ref={(el) => {
                                    rhfCheckOutRef(el);
                                    checkOutInputRef.current = el;
                                }}
                                className="absolute inset-0 h-full w-full cursor-pointer opacity-0"
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
                                No hay habitaciones disponibles en esta
                                categoría para las fechas seleccionadas.
                            </span>
                        </div>
                    )}

                    {/* Selector de Huéspedes */}
                    <div>
                        <label className="text-[11px] font-black text-muted-foreground uppercase">
                            Huéspedes
                        </label>
                        <Select
                            value={huespedes}
                            onValueChange={(val) => setValue('huespedes', val)}
                        >
                            <SelectTrigger className="mt-1 h-10 w-full rounded-2xl bg-background text-xs font-bold shadow-xs">
                                <div className="flex items-center gap-2">
                                    <Users className="size-4 text-primary dark:text-rose-400" />
                                    <SelectValue placeholder="Cantidad de huéspedes" />
                                </div>
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

                    {/* Desglose de Precios */}
                    <div className="space-y-2 rounded-2xl border border-border/60 bg-muted/30 p-4 text-xs">
                        <div className="flex justify-between text-muted-foreground">
                            <span>
                                {room.moneda || '$'}
                                {precioNoche} × {noches}{' '}
                                {noches === 1 ? 'noche' : 'noches'}
                            </span>
                            <span className="font-bold text-foreground">
                                {room.moneda || '$'}
                                {totalEstimado}
                            </span>
                        </div>
                        <div className="flex justify-between text-muted-foreground">
                            <span>Tarifa de servicio & piscina</span>
                            <span className="font-bold text-emerald-600 dark:text-emerald-400">
                                Gratis
                            </span>
                        </div>
                        <div className="flex justify-between border-t border-border/70 pt-2 text-sm font-black text-foreground">
                            <span>Total estimado</span>
                            <span className="text-base text-primary dark:text-rose-400">
                                {room.moneda || '$'}
                                {totalEstimado}
                            </span>
                        </div>
                    </div>

                    {/* Botones de Acción */}
                    <div className="space-y-2 pt-1">
                        {/* Botón Principal: Reservar en Línea (Página Dedicada) */}
                        <Link
                            href={`/habitaciones/${room.slug || room.id}/reservar?check_in=${checkIn}&check_out=${checkOut}&huespedes=${huespedes}`}
                            className={`inline-flex w-full items-center justify-center rounded-2xl bg-primary py-3.5 text-xs font-black text-primary-foreground shadow-lg transition-all hover:bg-primary/90 active:scale-95 ${
                                tieneConflictoDisponibilidad
                                    ? 'pointer-events-none opacity-50'
                                    : ''
                            }`}
                        >
                            <CalendarCheck className="mr-2 size-4" />
                            <span>Reservar Suite Ahora</span>
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
                                    <span>Procesando...</span>
                                </>
                            ) : (
                                <>
                                    <MessageCircle className="mr-2 size-4 text-emerald-600 dark:text-emerald-400" />
                                    <span>Consultar por WhatsApp</span>
                                </>
                            )}
                        </Button>
                    </div>

                    {/* Micro-garantías */}
                    <div className="flex items-center justify-center gap-1.5 text-[11px] font-bold text-muted-foreground">
                        <Lock className="size-3 text-muted-foreground" />
                        <span>
                            Sin cargos sorpresa • Confirmación inmediata
                        </span>
                    </div>
                </form>
            </div>
        </>
    );
};

export default HabitacionReservaCard;
