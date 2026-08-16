import { Link } from '@inertiajs/react';
import { CheckCircle2, QrCode, ArrowRight } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';

interface PropiedadesResumenQrCheckIn {
    codigoReserva: string;
    titularNombre: string;
    habitacionNombre?: string;
    fechaEntrada: string;
    fechaSalida: string;
}

export const ResumenQrCheckIn = ({
    codigoReserva,
    titularNombre,
    habitacionNombre = 'Suite Boutique',
    fechaEntrada,
    fechaSalida,
}: PropiedadesResumenQrCheckIn) => {
    return (
        <Card className="rounded-3xl border border-emerald-500/30 bg-card p-6 text-center font-sans shadow-xl sm:p-10">
            <CardContent className="flex flex-col items-center gap-6 p-0">
                <div className="flex size-16 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <CheckCircle2 className="size-10" />
                </div>

                <div>
                    <Badge
                        variant="outline"
                        className="mb-2 border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                    >
                        Check-in Digital Exitoso
                    </Badge>
                    <h2 className="text-2xl font-black text-foreground sm:text-3xl">
                        ¡Bienvenido a Hotel Bugambilias!
                    </h2>
                    <p className="mt-2 text-xs font-medium text-muted-foreground sm:text-sm">
                        Su registro anticipado ha sido procesado correctamente.
                        Muestre este pase en recepción para recibir la llave de
                        su habitación.
                    </p>
                </div>

                {/* Pase de Entrada QR */}
                <div className="flex w-full max-w-sm flex-col items-center rounded-3xl border border-border/80 bg-muted/40 p-6 shadow-xs">
                    <div className="mb-4 flex size-40 items-center justify-center rounded-2xl border border-border bg-white p-3 shadow-inner">
                        <QrCode className="size-full text-zinc-900" />
                    </div>
                    <span className="text-xs font-black tracking-wider text-muted-foreground uppercase">
                        Código de Reserva
                    </span>
                    <span className="text-lg font-black text-bugambilia-600 dark:text-bugambilia-400">
                        {codigoReserva}
                    </span>
                    <span className="mt-1 text-xs font-extrabold text-foreground">
                        {titularNombre}
                    </span>
                    <span className="text-[11px] text-muted-foreground">
                        {habitacionNombre} • {fechaEntrada} al {fechaSalida}
                    </span>
                </div>

                <div className="flex flex-wrap items-center justify-center gap-3">
                    <Button
                        asChild
                        size="lg"
                        className="rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700"
                    >
                        <Link href="/inicio">
                            Ir al Inicio <ArrowRight className="ml-2 size-4" />
                        </Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
};
