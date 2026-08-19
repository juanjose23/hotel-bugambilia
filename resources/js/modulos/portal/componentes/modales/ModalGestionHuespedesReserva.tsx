import { Users, UserPlus, Trash2, CheckCircle2, X } from 'lucide-react';
import { useState } from 'react';
import type {
    ReservaClienteDomain,
    AcompananteCliente,
} from '@/modulos/clientes/interfaces/cliente';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Label } from '@/modulos/compartido/ui/etiqueta';
import { Badge } from '@/modulos/compartido/ui/insignia';

interface PropiedadesModalGestionHuespedesReserva {
    reserva: ReservaClienteDomain | null;
    estaAbierto: boolean;
    alCerrar: () => void;
}

export const ModalGestionHuespedesReserva = ({
    reserva,
    estaAbierto,
    alCerrar,
}: PropiedadesModalGestionHuespedesReserva) => {
    const [huespedes, setHuespedes] = useState<AcompananteCliente[]>(
        reserva?.acompanantes || [
            {
                id: 1,
                nombre: reserva?.nombre_cliente || 'Huésped Titular',
                identificacion: 'PAS-892019',
                tipo: 'Adulto (Titular)',
            },
        ],
    );

    const [nuevoNombre, setNuevoNombre] = useState('');
    const [nuevaIdentificacion, setNuevaIdentificacion] = useState('');
    const [nuevoTipo, setNuevoTipo] = useState<'Adulto' | 'Niño'>('Adulto');
    const [cargando, setCargando] = useState(false);
    const [guardado, setGuardado] = useState(false);

    if (!estaAbierto || !reserva) {
        return null;
    }

    const capacidadMaximaAdultos = reserva.adultos || 2;
    const capacidadMaximaNinos = reserva.ninos || 1;
    const capacidadTotal = capacidadMaximaAdultos + capacidadMaximaNinos;

    const agregarHuesped = (e: React.FormEvent) => {
        e.preventDefault();

        if (!nuevoNombre.trim()) {
            return;
        }

        if (huespedes.length >= capacidadTotal) {
            alert(
                `La capacidad máxima registrada para esta habitación es de ${capacidadTotal} huéspedes.`,
            );

            return;
        }

        const nuevo: AcompananteCliente = {
            id: Date.now(),
            nombre: nuevoNombre.trim(),
            identificacion: nuevaIdentificacion.trim() || 'Por verificar',
            tipo: nuevoTipo,
        };

        setHuespedes((prev) => [...prev, nuevo]);
        setNuevoNombre('');
        setNuevaIdentificacion('');
    };

    const eliminarHuesped = (id: number | undefined) => {
        if (!id) {
            return;
        }

        setHuespedes((prev) => prev.filter((h) => h.id !== id));
    };

    const guardarCambios = () => {
        setCargando(true);
        setTimeout(() => {
            setCargando(false);
            setGuardado(true);
        }, 700);
    };

    const resetearYcerrar = () => {
        setGuardado(false);
        alCerrar();
    };

    return (
        <div className="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs duration-200">
            <div className="relative w-full max-w-xl overflow-hidden rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-2xl md:p-8">
                {/* Botón de cierre */}
                <button
                    type="button"
                    onClick={resetearYcerrar}
                    className="absolute top-4 right-4 flex size-8 cursor-pointer items-center justify-center rounded-full border border-border bg-background text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    <X className="size-4" />
                </button>

                {guardado ? (
                    <div className="space-y-4 py-6 text-center">
                        <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 className="size-8" />
                        </div>
                        <h3 className="text-xl font-black text-foreground">
                            ¡Nómina de Huéspedes Actualizada!
                        </h3>
                        <p className="text-xs font-medium text-muted-foreground">
                            Los datos de los {huespedes.length} huésped(es)
                            registrados para la reserva{' '}
                            <span className="font-mono font-bold text-bugambilia-600 dark:text-bugambilia-400">
                                {reserva.codigo_reserva}
                            </span>{' '}
                            han sido actualizados en la recepción del hotel.
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
                    <div className="space-y-5">
                        <div className="space-y-1">
                            <Badge className="border-bugambilia-500/30 bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                                <Users className="mr-1 size-3" />
                                Administración del Huésped
                            </Badge>
                            <h2 className="text-xl font-black text-foreground md:text-2xl">
                                Gestión de Huéspedes & Acompañantes
                            </h2>
                            <p className="text-xs font-medium text-muted-foreground">
                                Capacidad autorizada:{' '}
                                <span className="font-bold text-foreground">
                                    {capacidadMaximaAdultos} Adulto(s),{' '}
                                    {capacidadMaximaNinos} Niño(s)
                                </span>{' '}
                                (Máx. {capacidadTotal} pers.)
                            </p>
                        </div>

                        {/* Formulario Agregar Acompañante */}
                        <form
                            onSubmit={agregarHuesped}
                            className="space-y-3 rounded-2xl border border-border/70 bg-muted/30 p-4"
                        >
                            <span className="block text-xs font-black tracking-wider text-foreground uppercase">
                                Registrar Nuevo Acompañante
                            </span>

                            <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <div className="space-y-1">
                                    <Label className="text-[11px] font-bold text-foreground">
                                        Nombre Completo
                                    </Label>
                                    <Input
                                        value={nuevoNombre}
                                        onChange={(e) =>
                                            setNuevoNombre(e.target.value)
                                        }
                                        placeholder="Ej: María Elena Gutiérrez"
                                        className="rounded-xl border-border/80 text-xs"
                                    />
                                </div>

                                <div className="space-y-1">
                                    <Label className="text-[11px] font-bold text-foreground">
                                        Identificación / Pasaporte
                                    </Label>
                                    <Input
                                        value={nuevaIdentificacion}
                                        onChange={(e) =>
                                            setNuevaIdentificacion(
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ej: 001-150892-0004K"
                                        className="rounded-xl border-border/80 text-xs"
                                    />
                                </div>
                            </div>

                            <div className="flex items-center justify-between gap-2 pt-1">
                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => setNuevoTipo('Adulto')}
                                        className={`cursor-pointer rounded-full border px-3 py-1 text-xs font-extrabold ${
                                            nuevoTipo === 'Adulto'
                                                ? 'border-bugambilia-600 bg-bugambilia-600 text-white dark:bg-bugambilia-500'
                                                : 'border-border bg-background text-muted-foreground'
                                        }`}
                                    >
                                        Adulto
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setNuevoTipo('Niño')}
                                        className={`cursor-pointer rounded-full border px-3 py-1 text-xs font-extrabold ${
                                            nuevoTipo === 'Niño'
                                                ? 'border-bugambilia-600 bg-bugambilia-600 text-white dark:bg-bugambilia-500'
                                                : 'border-border bg-background text-muted-foreground'
                                        }`}
                                    >
                                        Niño
                                    </button>
                                </div>

                                <Button
                                    type="submit"
                                    disabled={
                                        !nuevoNombre.trim() ||
                                        huespedes.length >= capacidadTotal
                                    }
                                    className="rounded-full bg-bugambilia-600 text-xs font-extrabold text-white hover:bg-bugambilia-700 disabled:opacity-40 dark:bg-bugambilia-500"
                                >
                                    <UserPlus className="mr-1 size-3.5" />
                                    Agregar
                                </Button>
                            </div>
                        </form>

                        {/* Listado de Huéspedes Registrados */}
                        <div className="space-y-2">
                            <div className="flex items-center justify-between text-xs font-bold text-foreground">
                                <span>
                                    Huéspedes Registrados ({huespedes.length}/
                                    {capacidadTotal}):
                                </span>
                            </div>

                            <div className="max-h-48 space-y-2 overflow-y-auto pr-1">
                                {huespedes.map((h, idx) => (
                                    <div
                                        key={h.id || idx}
                                        className="flex items-center justify-between rounded-2xl border border-border/70 bg-background p-3 shadow-2xs"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="flex size-8 items-center justify-center rounded-full bg-bugambilia-500/10 text-xs font-bold text-bugambilia-600 dark:text-bugambilia-400">
                                                {idx + 1}
                                            </div>
                                            <div>
                                                <span className="block text-xs font-black text-foreground">
                                                    {h.nombre}
                                                </span>
                                                <span className="block text-[10px] font-medium text-muted-foreground">
                                                    ID:{' '}
                                                    {h.identificacion ||
                                                        'Por verificar'}{' '}
                                                    — {h.tipo || 'Adulto'}
                                                </span>
                                            </div>
                                        </div>

                                        {idx > 0 && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    eliminarHuesped(h.id)
                                                }
                                                className="flex size-7 cursor-pointer items-center justify-center rounded-full bg-rose-500/10 text-rose-600 transition-colors hover:bg-rose-500/20 dark:text-rose-400"
                                                title="Eliminar huésped"
                                            >
                                                <Trash2 className="size-3.5" />
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="flex items-center gap-2 border-t border-border/60 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={resetearYcerrar}
                                className="flex-1 rounded-full font-bold"
                            >
                                Cancelar
                            </Button>
                            <Button
                                type="button"
                                onClick={guardarCambios}
                                disabled={cargando}
                                className="flex-1 rounded-full bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                            >
                                {cargando ? 'Guardando...' : 'Guardar Nómina'}
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default ModalGestionHuespedesReserva;
