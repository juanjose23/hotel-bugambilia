import {
    Calendar,
    Tag,
    Gift,
    CheckCircle2,
    MessageCircle,
    Loader2,
    Users,
    Mail,
    Phone,
    User,
    Info,
} from 'lucide-react';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/modules/shared/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from '@/modules/shared/components/ui/sheet';
import { usePromocionConsultaForm } from '../hooks/usePromocionConsultaForm';
import type { PromocionItem } from '../types';

interface PromocionConsultaSheetProps {
    abierto: boolean;
    alCerrar: () => void;
    promocion: PromocionItem | null;
    telefonoWhatsApp?: string;
}

const PROMO_DEFECTO: PromocionItem = {
    id: 0,
    codigo: 'PROMO',
    slug: 'promocion',
    nombre: 'Promoción Especial',
    tipo: 'Especial',
    precio_final: 0,
    moneda: '$',
    imagen: '/images/hero-main.webp',
};

export const PromocionConsultaSheet = ({
    abierto,
    alCerrar,
    promocion,
    telefonoWhatsApp,
}: PromocionConsultaSheetProps) => {
    const promoActiva = promocion || PROMO_DEFECTO;

    const { register, handleSubmit, setValue, watch, errors, isSubmitting } =
        usePromocionConsultaForm({
            promocion: promoActiva,
            telefonoWhatsApp,
            alCompletar: alCerrar,
        });

    const huespedes = watch('huespedes');

    if (!promocion) {
        return null;
    }

    return (
        <Sheet open={abierto} onOpenChange={(open) => !open && alCerrar()}>
            <SheetContent
                side="right"
                className="w-full overflow-y-auto p-6 font-sans sm:max-w-md"
            >
                <SheetHeader className="text-left">
                    <div className="inline-flex w-fit items-center gap-1.5 rounded-full border border-primary/30 bg-primary/10 px-3 py-0.5 text-[11px] font-black text-primary uppercase dark:border-rose-500/40 dark:bg-rose-950/60 dark:text-rose-200">
                        <Tag className="size-3" />
                        <span>{promocion.codigo}</span>
                    </div>

                    <SheetTitle className="mt-2 text-xl font-black tracking-tight text-foreground">
                        {promocion.nombre}
                    </SheetTitle>

                    <SheetDescription className="text-xs text-muted-foreground">
                        {promocion.descripcion ||
                            'Detalles de la oferta y solicitud de reserva para Hotel Bugambilias.'}
                    </SheetDescription>
                </SheetHeader>

                {/* Resumen de Tarifas y Descuentos */}
                <div className="mt-5 rounded-2xl border border-border bg-muted/40 p-4">
                    <div className="flex items-baseline justify-between">
                        <div>
                            <span className="text-xs font-bold text-muted-foreground uppercase">
                                Tarifa de Paquete
                            </span>
                            <div className="flex items-baseline gap-2">
                                <span className="text-2xl font-black text-foreground">
                                    {promocion.moneda}
                                    {Number(promocion.precio_final).toFixed(0)}
                                </span>
                                {promocion.precio_original &&
                                    promocion.precio_original >
                                        promocion.precio_final && (
                                        <span className="text-xs font-bold text-muted-foreground line-through">
                                            {promocion.moneda}
                                            {Number(
                                                promocion.precio_original,
                                            ).toFixed(0)}
                                        </span>
                                    )}
                            </div>
                        </div>

                        {promocion.descuento_porcentaje && (
                            <span className="rounded-full bg-rose-600 px-3 py-1 text-xs font-black text-white">
                                {Math.round(promocion.descuento_porcentaje)}%
                                OFF
                            </span>
                        )}
                    </div>
                </div>

                {/* Beneficios Incluidos */}
                {promocion.beneficios && promocion.beneficios.length > 0 && (
                    <div className="mt-5 space-y-2.5">
                        <div className="flex items-center gap-1.5 text-xs font-black text-foreground uppercase">
                            <Gift className="size-3.5 text-primary dark:text-rose-400" />
                            <span>Beneficios incluidos:</span>
                        </div>
                        <div className="space-y-2">
                            {promocion.beneficios.map((ben) => (
                                <div
                                    key={ben.id}
                                    className="flex items-start gap-2.5 rounded-xl border border-border/60 bg-card p-3 text-xs"
                                >
                                    <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                    <div>
                                        <div className="font-bold text-foreground">
                                            {ben.titulo}
                                        </div>
                                        {ben.descripcion && (
                                            <p className="mt-0.5 text-[11px] text-muted-foreground">
                                                {ben.descripcion}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Formulario de Consulta / Reserva */}
                <form onSubmit={handleSubmit} className="mt-6 space-y-4">
                    <div className="text-xs font-black text-foreground uppercase">
                        Tus Datos para la Reserva:
                    </div>

                    {/* Nombre */}
                    <div>
                        <div className="mb-1 flex items-center gap-1.5 text-[11px] font-black text-muted-foreground uppercase">
                            <User className="size-3" />
                            <span>Nombre Completo *</span>
                        </div>
                        <Input
                            {...register('nombre')}
                            placeholder="Ej. Juan Pérez"
                            className="h-10 rounded-xl text-xs"
                        />
                        {errors.nombre && (
                            <p className="mt-1 text-[11px] font-bold text-destructive">
                                {errors.nombre.message}
                            </p>
                        )}
                    </div>

                    {/* Email y Teléfono */}
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <div className="mb-1 flex items-center gap-1.5 text-[11px] font-black text-muted-foreground uppercase">
                                <Mail className="size-3" />
                                <span>Email *</span>
                            </div>
                            <Input
                                type="email"
                                {...register('email')}
                                placeholder="tu@correo.com"
                                className="h-10 rounded-xl text-xs"
                            />
                            {errors.email && (
                                <p className="mt-1 text-[11px] font-bold text-destructive">
                                    {errors.email.message}
                                </p>
                            )}
                        </div>

                        <div>
                            <div className="mb-1 flex items-center gap-1.5 text-[11px] font-black text-muted-foreground uppercase">
                                <Phone className="size-3" />
                                <span>Teléfono *</span>
                            </div>
                            <Input
                                {...register('telefono')}
                                placeholder="+505 8888-8888"
                                className="h-10 rounded-xl text-xs"
                            />
                            {errors.telefono && (
                                <p className="mt-1 text-[11px] font-bold text-destructive">
                                    {errors.telefono.message}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Fecha Tentativa y Huéspedes */}
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <div className="mb-1 flex items-center gap-1.5 text-[10px] font-black text-muted-foreground uppercase">
                                <Calendar className="size-3" />
                                <span>Fecha Deseada</span>
                            </div>
                            <Input
                                type="date"
                                {...register('fecha_tentativa')}
                                className="h-10 rounded-xl text-xs"
                            />
                        </div>

                        <div>
                            <div className="mb-1 flex items-center gap-1.5 text-[10px] font-black text-muted-foreground uppercase">
                                <Users className="size-3" />
                                <span>Huéspedes</span>
                            </div>
                            <Select
                                value={huespedes}
                                onValueChange={(v) => setValue('huespedes', v)}
                            >
                                <SelectTrigger className="h-10 rounded-xl text-xs font-bold">
                                    <SelectValue placeholder="Huéspedes" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">1 persona</SelectItem>
                                    <SelectItem value="2">
                                        2 personas
                                    </SelectItem>
                                    <SelectItem value="3">
                                        3 personas
                                    </SelectItem>
                                    <SelectItem value="4">
                                        4+ personas
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    {/* Botón Submit WhatsApp */}
                    <Button
                        type="submit"
                        disabled={isSubmitting}
                        className="w-full cursor-pointer rounded-2xl bg-emerald-600 py-3.5 text-xs font-black text-white shadow-md transition-all hover:bg-emerald-700 active:scale-95 dark:bg-emerald-700 dark:hover:bg-emerald-800"
                    >
                        {isSubmitting ? (
                            <>
                                <Loader2 className="mr-2 size-4 animate-spin" />
                                <span>Enviando...</span>
                            </>
                        ) : (
                            <>
                                <MessageCircle className="mr-2 size-4" />
                                <span>Solicitar Oferta por WhatsApp</span>
                            </>
                        )}
                    </Button>

                    <div className="flex items-center justify-center gap-1 text-center text-[11px] text-muted-foreground">
                        <Info className="size-3" />
                        <span>Sujeto a disponibilidad de fechas.</span>
                    </div>
                </form>
            </SheetContent>
        </Sheet>
    );
};

export default PromocionConsultaSheet;
