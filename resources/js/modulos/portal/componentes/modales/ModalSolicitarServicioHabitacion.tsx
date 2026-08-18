import { useState } from 'react';
import { Sparkles, CheckCircle2, AlertCircle, X, Bell } from 'lucide-react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Label } from '@/modulos/compartido/ui/etiqueta';
import { Badge } from '@/modulos/compartido/ui/insignia';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';

interface PropiedadesModalSolicitarServicioHabitacion {
    reserva: ReservaClienteDomain | null;
    estaAbierto: boolean;
    alCerrar: () => void;
}

const OPCIONES_SERVICIOS = [
    {
        id: 'limpieza',
        titulo: 'Limpieza Express & Cambio de Sábanas',
        descripcion: 'Servicio de mucama prioritario para refrescar su habitación.',
        icono: '🧹',
    },
    {
        id: 'amenidades',
        titulo: 'Toallas & Amenidades Adicionales',
        descripcion: 'Solicite juego de toallas extra, champú, jabones o almohadas.',
        icono: '🧴',
    },
    {
        id: 'lavanderia',
        titulo: 'Servicio de Lavandería & Planchado',
        descripcion: 'Recogida de prendas para lavado, secado y planchado express.',
        icono: '👕',
    },
    {
        id: 'asistencia',
        titulo: 'Soporte Técnico / Conectividad',
        descripcion: 'Asistencia con Wi-Fi, climatización, Smart TV o caja fuerte.',
        icono: '🛠️',
    },
];

export const ModalSolicitarServicioHabitacion = ({
    reserva,
    estaAbierto,
    alCerrar,
}: PropiedadesModalSolicitarServicioHabitacion) => {
    const [servicioSeleccionado, setServicioSeleccionado] = useState<string>('limpieza');
    const [notas, setNotas] = useState<string>('');
    const [enviado, setEnviado] = useState<boolean>(false);
    const [cargando, setCargando] = useState<boolean>(false);

    if (!estaAbierto || !reserva) {
        return null;
    }

    const manejarSolicitud = (e: React.FormEvent) => {
        e.preventDefault();
        setCargando(true);

        setTimeout(() => {
            setCargando(false);
            setEnviado(true);
        }, 800);
    };

    const resetearYcerrar = () => {
        setEnviado(false);
        setNotas('');
        setServicioSeleccionado('limpieza');
        alCerrar();
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs animate-in fade-in duration-200">
            <div className="relative w-full max-w-lg overflow-hidden rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-2xl md:p-8">
                {/* Botón de cierre */}
                <button
                    type="button"
                    onClick={resetearYcerrar}
                    className="absolute top-4 right-4 flex size-8 cursor-pointer items-center justify-center rounded-full border border-border bg-background text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    <X className="size-4" />
                </button>

                {enviado ? (
                    <div className="space-y-4 text-center py-6">
                        <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 className="size-8" />
                        </div>
                        <h3 className="text-xl font-black text-foreground">
                            ¡Solicitud Enviada con Éxito!
                        </h3>
                        <p className="text-xs font-medium text-muted-foreground">
                            Su solicitud para la reserva <span className="font-mono font-bold text-bugambilia-600 dark:text-bugambilia-400">{reserva.codigo_reserva}</span> ha sido registrada. Nuestro personal de recepción y ama de llaves se encuentra en camino.
                        </p>
                        <div className="pt-2">
                            <Button
                                onClick={resetearYcerrar}
                                className="w-full rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                            >
                                Entendido
                            </Button>
                        </div>
                    </div>
                ) : (
                    <form onSubmit={manejarSolicitud} className="space-y-5">
                        <div className="space-y-1">
                            <Badge className="border-bugambilia-500/30 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                                <Bell className="mr-1 size-3" />
                                Atención en Habitación
                            </Badge>
                            <h2 className="text-xl font-black text-foreground md:text-2xl">
                                Pedir Servicio a la Habitación
                            </h2>
                            <p className="text-xs font-medium text-muted-foreground">
                                {reserva.detalles} — Código: <span className="font-mono font-bold text-bugambilia-600 dark:text-bugambilia-400">{reserva.codigo_reserva}</span>
                            </p>
                        </div>

                        {/* Opciones de servicio */}
                        <div className="space-y-2 max-h-60 overflow-y-auto pr-1">
                            {OPCIONES_SERVICIOS.map((opt) => (
                                <button
                                    key={opt.id}
                                    type="button"
                                    onClick={() => setServicioSeleccionado(opt.id)}
                                    className={`flex w-full cursor-pointer items-start gap-3 rounded-2xl border p-3.5 text-left transition-all ${
                                        servicioSeleccionado === opt.id
                                            ? 'border-bugambilia-600 bg-bugambilia-500/10 shadow-xs dark:border-bugambilia-500'
                                            : 'border-border/70 bg-background hover:border-bugambilia-500/30'
                                    }`}
                                >
                                    <span className="text-2xl">{opt.icono}</span>
                                    <div className="grow">
                                        <span className="block text-xs font-black text-foreground">
                                            {opt.titulo}
                                        </span>
                                        <span className="block text-[11px] font-medium text-muted-foreground">
                                            {opt.descripcion}
                                        </span>
                                    </div>
                                </button>
                            ))}
                        </div>

                        {/* Notas adicionales */}
                        <div className="space-y-1.5">
                            <Label className="text-xs font-bold text-foreground">
                                Especificaciones o Comentarios Adicionales
                            </Label>
                            <Input
                                value={notas}
                                onChange={(e) => setNotas(e.target.value)}
                                placeholder="Ej: Entregar 2 toallas adicionales después de las 3:00 PM"
                                className="rounded-xl border-border/80 text-xs"
                            />
                        </div>

                        <div className="flex items-center gap-2 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={resetearYcerrar}
                                className="flex-1 rounded-full font-bold"
                            >
                                Cancelar
                            </Button>
                            <Button
                                type="submit"
                                disabled={cargando}
                                className="flex-1 rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                            >
                                {cargando ? 'Enviando...' : 'Confirmar Solicitud'}
                            </Button>
                        </div>
                    </form>
                )}
            </div>
        </div>
    );
};

export default ModalSolicitarServicioHabitacion;
