import { ShieldCheck, Lock, ChevronUp } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/modules/shared/components/ui/sheet';

export interface BookingSummaryRow {
    label: string;
    value: string;
    subvalue?: string;
    icon?: React.ReactNode;
}

export interface BookingSummaryPricing {
    subtotal: number;
    subtotalLabel?: string;
    servicesTotal?: number;
    servicesLabel?: string;
    discount?: number;
    discountLabel?: string;
    total: number;
    dueToday?: number;
    dueTodayLabel?: string;
    currency: string;
}

export interface BookingSummaryProps {
    title?: string;
    subtitle?: string;
    resourceName: string;
    resourceCategory?: string;
    resourceImage?: string;
    rows: BookingSummaryRow[];
    pricing: BookingSummaryPricing;
    guarantees?: string[];
    actionButton?: React.ReactNode;
    className?: string;
}

export const BookingSummary = ({
    title = 'Tu Reserva',
    subtitle,
    resourceName,
    resourceCategory,
    resourceImage,
    rows,
    pricing,
    guarantees = [
        'Confirmación inmediata sin cargos sorpresa',
        'Mejor tarifa garantizada directamente con el hotel',
    ],
    actionButton,
    className = '',
}: BookingSummaryProps) => {
    const [mobileSheetOpen, setMobileSheetOpen] = useState(false);
    const { currency } = pricing;

    const renderPriceRow = (
        label: string,
        amount: number,
        prefix = '',
        className = 'text-muted-foreground',
    ) => (
        <div
            className={`flex items-center justify-between text-xs ${className}`}
        >
            <span>{label}</span>
            <span className="font-bold text-foreground">
                {prefix}
                {currency}
                {amount.toFixed(2)}
            </span>
        </div>
    );

    const summaryContent = (
        <div className="space-y-4">
            {/* Header del recurso */}
            <div className="flex items-center gap-3.5 border-b border-border/60 pb-4">
                {resourceImage && (
                    <img
                        src={resourceImage}
                        alt={resourceName}
                        className="size-16 shrink-0 rounded-2xl border border-border object-cover"
                    />
                )}
                <div className="min-w-0 flex-1">
                    {resourceCategory && (
                        <span className="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-black text-primary uppercase dark:bg-rose-950/40 dark:text-rose-300">
                            {resourceCategory}
                        </span>
                    )}
                    <h3 className="mt-1 truncate text-sm font-black text-foreground">
                        {resourceName}
                    </h3>
                    {subtitle && (
                        <p className="mt-0.5 text-[11px] text-muted-foreground">
                            {subtitle}
                        </p>
                    )}
                </div>
            </div>

            {/* Filas de detalles (Fechas, horarios, comensales, etc.) */}
            {rows.length > 0 && (
                <div className="space-y-2 rounded-2xl border border-border/70 bg-background/80 p-3.5 text-xs">
                    {rows.map((row, index) => (
                        <div
                            key={index}
                            className="flex items-center justify-between gap-2 text-muted-foreground"
                        >
                            <span className="flex items-center gap-1.5 font-medium">
                                {row.icon}
                                {row.label}:
                            </span>
                            <span className="text-right font-bold text-foreground">
                                {row.value}
                                {row.subvalue && (
                                    <span className="block text-[10px] font-normal text-muted-foreground">
                                        {row.subvalue}
                                    </span>
                                )}
                            </span>
                        </div>
                    ))}
                </div>
            )}

            {/* Desglose de precios */}
            <div className="space-y-2 rounded-2xl border border-border/80 bg-muted/20 p-4">
                {renderPriceRow(
                    pricing.subtotalLabel || 'Subtotal base',
                    pricing.subtotal,
                )}

                {pricing.servicesTotal !== undefined &&
                    pricing.servicesTotal > 0 &&
                    renderPriceRow(
                        pricing.servicesLabel || 'Servicios y extras',
                        pricing.servicesTotal,
                        '+',
                    )}

                {pricing.discount !== undefined && pricing.discount > 0 && (
                    <div className="flex items-center justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400">
                        <span>
                            {pricing.discountLabel || 'Descuento aplicado'}
                        </span>
                        <span>
                            -{currency}
                            {pricing.discount.toFixed(2)}
                        </span>
                    </div>
                )}

                {/* Total */}
                <div className="flex items-baseline justify-between border-t border-border pt-2 text-sm font-black text-foreground">
                    <span>Total</span>
                    <span className="text-lg text-primary dark:text-rose-400">
                        {currency}
                        {pricing.total.toFixed(2)}
                    </span>
                </div>

                {/* Pago hoy si aplica */}
                {pricing.dueToday !== undefined && (
                    <div className="flex items-center justify-between border-t border-dashed border-border/70 pt-2 text-xs font-black">
                        <span className="text-muted-foreground">
                            {pricing.dueTodayLabel || 'Monto a pagar hoy:'}
                        </span>
                        <span className="font-extrabold text-foreground">
                            {currency}
                            {pricing.dueToday.toFixed(2)}
                        </span>
                    </div>
                )}
            </div>

            {/* Micro-garantías */}
            {guarantees.length > 0 && (
                <div className="space-y-1.5 pt-1 text-[11px] text-muted-foreground">
                    {guarantees.map((item, idx) => (
                        <div key={idx} className="flex items-center gap-2">
                            <ShieldCheck className="size-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                            <span>{item}</span>
                        </div>
                    ))}
                </div>
            )}

            {/* Botón de acción embebido si se pasa */}
            {actionButton && <div className="pt-2">{actionButton}</div>}
        </div>
    );

    return (
        <>
            {/* Desktop Sticky Sidebar (>= 1024px) */}
            <div
                className={`sticky top-24 hidden rounded-3xl border border-border bg-card p-6 shadow-xl lg:block ${className}`}
            >
                <div className="mb-4 flex items-center justify-between border-b border-border/60 pb-3">
                    <h2 className="text-sm font-black tracking-tight text-foreground uppercase">
                        {title}
                    </h2>
                    <span className="flex items-center gap-1 text-[11px] font-bold text-muted-foreground">
                        <Lock className="size-3 text-emerald-600 dark:text-emerald-400" />
                        <span>Pago seguro</span>
                    </span>
                </div>
                {summaryContent}
            </div>

            {/* Mobile Fixed Bottom Bar (< 1024px) */}
            <div className="fixed right-0 bottom-0 left-0 z-40 border-t border-border bg-card/95 px-4 py-3 shadow-2xl backdrop-blur-xl lg:hidden">
                <div className="container mx-auto flex items-center justify-between gap-3">
                    {/* Botón para desplegar resumen completo */}
                    <button
                        type="button"
                        onClick={() => setMobileSheetOpen(true)}
                        className="flex flex-col text-left focus:outline-hidden"
                    >
                        <span className="flex items-center gap-1 text-[10px] font-bold text-muted-foreground uppercase">
                            <span>Resumen de reserva</span>
                            <ChevronUp className="size-3" />
                        </span>
                        <div className="flex items-baseline gap-1">
                            <span className="text-base font-black text-foreground">
                                {currency}
                                {pricing.total.toFixed(2)}
                            </span>
                            {pricing.dueToday !== undefined &&
                                pricing.dueToday !== pricing.total && (
                                    <span className="text-[10px] text-muted-foreground">
                                        (Hoy: {currency}
                                        {pricing.dueToday.toFixed(2)})
                                    </span>
                                )}
                        </div>
                    </button>

                    {/* Botón de acción en barra móvil */}
                    {actionButton && (
                        <div className="shrink-0">{actionButton}</div>
                    )}
                </div>
            </div>

            {/* Bottom Sheet Drawer for Mobile */}
            <Sheet open={mobileSheetOpen} onOpenChange={setMobileSheetOpen}>
                <SheetContent
                    side="bottom"
                    className="max-h-[85vh] overflow-y-auto rounded-t-3xl border-t border-border bg-card p-6 font-sans"
                >
                    <SheetHeader className="mb-3 text-left">
                        <div className="flex items-center justify-between">
                            <SheetTitle className="text-base font-black text-foreground">
                                {title}
                            </SheetTitle>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={() => setMobileSheetOpen(false)}
                                className="h-7 text-xs font-bold"
                            >
                                Cerrar
                            </Button>
                        </div>
                        <SheetDescription className="text-xs text-muted-foreground">
                            Detalles completos de tu reserva en Hotel
                            Bugambilias
                        </SheetDescription>
                    </SheetHeader>

                    {summaryContent}

                    <div className="mt-4">
                        <Button
                            type="button"
                            onClick={() => setMobileSheetOpen(false)}
                            className="w-full rounded-2xl py-3 text-xs font-bold"
                        >
                            Volver al formulario
                        </Button>
                    </div>
                </SheetContent>
            </Sheet>
        </>
    );
};

export default BookingSummary;
