import { router } from '@inertiajs/react';
import {
    Ticket,
    QrCode,
    UserCheck,
    Lock,
    Mail,
    ArrowRight,
    X,
    Building2,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Badge } from '@/modulos/compartido/ui/insignia';

interface PropiedadesModalAccesoPortalCliente {
    estaAbierto: boolean;
    alCerrar: () => void;
    onAccesoExitoso?: () => void;
}

export const ModalAccesoPortalCliente = ({
    estaAbierto,
    alCerrar,
}: PropiedadesModalAccesoPortalCliente) => {
    const [metodo, setMetodo] = useState<'codigo' | 'qr' | 'login'>('codigo');

    // Estado Método 1: Código + Identificación
    const [codigoReserva, setCodigoReserva] = useState('');
    const [identificacion, setIdentificacion] = useState('');

    // Estado Método 2: Código QR
    const [codigoQr, setCodigoQr] = useState('');
    // Estado Método 3: Usuario + Contraseña
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    const [cargando, setCargando] = useState(false);
    const [errorMsg, setErrorMsg] = useState<string | null>(null);

    if (!estaAbierto) {
        return null;
    }

    // Método 1: Acceso por Código + Identificación
    const manejarAccesoCodigo = (e: React.FormEvent) => {
        e.preventDefault();

        if (!codigoReserva.trim()) {
            setErrorMsg('Por favor ingrese el código de reservación.');

            return;
        }

        setCargando(true);
        setErrorMsg(null);

        router.get(
            '/mis-reservas',
            {
                codigo: codigoReserva.trim(),
                identificacion: identificacion.trim(),
            },
            {
                onFinish: () => {
                    setCargando(false);
                    alCerrar();
                },
                onError: () => {
                    setCargando(false);
                    setErrorMsg(
                        'No se encontró ninguna reserva con esos datos.',
                    );
                },
            },
        );
    };

    // Método 2: Acceso por Código QR del Voucher
    const manejarAccesoQr = (e: React.FormEvent) => {
        e.preventDefault();
        const tokenLimpio = codigoQr.trim();

        if (!tokenLimpio) {
            setErrorMsg('Por favor escanee o ingrese el código del QR.');

            return;
        }

        setCargando(true);
        setErrorMsg(null);

        router.get(
            '/mis-reservas',
            {
                codigo: tokenLimpio,
            },
            {
                onFinish: () => {
                    setCargando(false);
                    alCerrar();
                },
                onError: () => {
                    setCargando(false);
                    setErrorMsg('El código QR ingresado no es válido.');
                },
            },
        );
    };

    // Método 3: Iniciar Sesión Tradicional (Email + Password)
    const manejarLoginTradicional = (e: React.FormEvent) => {
        e.preventDefault();

        if (!email.trim() || !password) {
            setErrorMsg('Ingrese su correo electrónico y contraseña.');

            return;
        }

        setCargando(true);
        setErrorMsg(null);

        router.post(
            '/login',
            {
                email: email.trim(),
                password,
            },
            {
                onSuccess: () => {
                    router.get('/mis-reservas');
                    setCargando(false);
                    alCerrar();
                },
                onError: (errors) => {
                    setCargando(false);
                    setErrorMsg(
                        errors.email ||
                            errors.password ||
                            'Credenciales de acceso incorrectas.',
                    );
                },
            },
        );
    };

    return (
        <div className="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 font-sans backdrop-blur-md">
            <div className="relative w-full max-w-lg overflow-hidden rounded-3xl border border-border/80 bg-card p-6 shadow-2xl md:p-8">
                {/* Botón Cerrar */}
                <button
                    type="button"
                    onClick={alCerrar}
                    className="absolute top-4 right-4 z-10 flex size-9 cursor-pointer items-center justify-center rounded-full bg-muted/80 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    <X className="size-4" />
                </button>

                {/* Encabezado Principal */}
                <div className="mb-6 space-y-1">
                    <div className="flex items-center gap-2">
                        <Badge
                            variant="outline"
                            className="border-bugambilia-500/40 bg-bugambilia-500/10 px-3 py-0.5 text-xs font-extrabold text-bugambilia-600 dark:text-bugambilia-400"
                        >
                            <Building2 className="mr-1.5 size-3.5" />
                            Portal de Huéspedes
                        </Badge>
                    </div>
                    <h2 className="text-xl font-black text-foreground sm:text-2xl">
                        Acceso a Mis Reservaciones
                    </h2>
                    <p className="text-xs font-medium text-muted-foreground">
                        Elija la forma en que desea consultar sus estancias en
                        Hotel Bugambilias.
                    </p>
                </div>

                {/* Selector de los 3 Métodos de Acceso */}
                <div className="mb-6 grid grid-cols-3 gap-1.5 rounded-2xl border border-border/80 bg-muted/40 p-1">
                    <button
                        type="button"
                        onClick={() => {
                            setMetodo('codigo');
                            setErrorMsg(null);
                        }}
                        className={`flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-center text-xs font-extrabold transition-all ${
                            metodo === 'codigo'
                                ? 'bg-bugambilia-600 text-white shadow-md'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <Ticket className="size-4" />
                        <span className="text-[11px] leading-tight">
                            Código & ID
                        </span>
                    </button>

                    <button
                        type="button"
                        onClick={() => {
                            setMetodo('qr');
                            setErrorMsg(null);
                        }}
                        className={`flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-center text-xs font-extrabold transition-all ${
                            metodo === 'qr'
                                ? 'bg-bugambilia-600 text-white shadow-md'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <QrCode className="size-4" />
                        <span className="text-[11px] leading-tight">
                            Escáner QR
                        </span>
                    </button>

                    <button
                        type="button"
                        onClick={() => {
                            setMetodo('login');
                            setErrorMsg(null);
                        }}
                        className={`flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-center text-xs font-extrabold transition-all ${
                            metodo === 'login'
                                ? 'bg-bugambilia-600 text-white shadow-md'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        <UserCheck className="size-4" />
                        <span className="text-[11px] leading-tight">
                            Usuario & Pass
                        </span>
                    </button>
                </div>

                {/* Mensaje de Error si Existe */}
                {errorMsg && (
                    <div className="mb-4 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-3 text-xs font-bold text-rose-600 dark:text-rose-400">
                        {errorMsg}
                    </div>
                )}

                {/* ----------------------------------------------------------------- */}
                {/* MÉTODO 1: Código de Reserva + Número de Identificación */}
                {/* ----------------------------------------------------------------- */}
                {metodo === 'codigo' && (
                    <form onSubmit={manejarAccesoCodigo} className="space-y-4">
                        <div className="space-y-1.5">
                            <label className="text-xs font-bold text-foreground">
                                Código de Reservación
                            </label>
                            <div className="relative">
                                <Ticket className="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder="Ej: RES-2026-X89"
                                    value={codigoReserva}
                                    onChange={(e) =>
                                        setCodigoReserva(
                                            e.target.value.toUpperCase(),
                                        )
                                    }
                                    className="h-11 rounded-2xl pl-10 font-mono text-xs font-bold uppercase"
                                />
                            </div>
                            <span className="text-[11px] text-muted-foreground">
                                Se encuentra en su correo de confirmación o
                                ticket digital.
                            </span>
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-xs font-bold text-foreground">
                                Número de Cédula, Pasaporte o Teléfono
                            </label>
                            <div className="relative">
                                <UserCheck className="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder="Ej: 161-050498-0001X o +505 8888 8888"
                                    value={identificacion}
                                    onChange={(e) =>
                                        setIdentificacion(e.target.value)
                                    }
                                    className="h-11 rounded-2xl pl-10 text-xs font-bold"
                                />
                            </div>
                        </div>

                        <Button
                            type="submit"
                            disabled={cargando}
                            className="mt-2 w-full rounded-2xl bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                        >
                            {cargando
                                ? 'Buscando Reserva...'
                                : 'Acceder a mi Reserva'}
                            <ArrowRight className="ml-2 size-4" />
                        </Button>
                    </form>
                )}

                {/* ----------------------------------------------------------------- */}
                {/* MÉTODO 2: Escáner QR de Voucher Digital / Impreso */}
                {/* ----------------------------------------------------------------- */}
                {metodo === 'qr' && (
                    <form
                        onSubmit={manejarAccesoQr}
                        className="space-y-4 text-center"
                    >
                        <div className="flex flex-col items-center justify-center rounded-3xl border border-dashed border-border/80 bg-muted/20 p-6">
                            <div className="mb-3 flex size-16 items-center justify-center rounded-2xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                                <QrCode className="size-8" />
                            </div>
                            <h4 className="text-sm font-black text-foreground">
                                Escanee el Código QR de su Voucher
                            </h4>
                            <p className="mt-1 text-xs text-muted-foreground">
                                Apunte la cámara hacia el QR de su pase de
                                reservación o ingrese el código alfanumérico
                                decodificado.
                            </p>

                            <div className="mt-4 w-full">
                                <Input
                                    type="text"
                                    placeholder="Pegue o escriba el código QR..."
                                    value={codigoQr}
                                    onChange={(e) =>
                                        setCodigoQr(e.target.value)
                                    }
                                    className="h-11 rounded-2xl text-center font-mono text-xs font-extrabold uppercase"
                                />
                            </div>
                        </div>

                        <Button
                            type="submit"
                            disabled={cargando}
                            className="w-full rounded-2xl bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                        >
                            {cargando
                                ? 'Verificando QR...'
                                : 'Ingresar con Código QR'}
                            <ArrowRight className="ml-2 size-4" />
                        </Button>
                    </form>
                )}

                {/* ----------------------------------------------------------------- */}
                {/* MÉTODO 3: Iniciar Sesión Tradicional (Email + Password) */}
                {/* ----------------------------------------------------------------- */}
                {metodo === 'login' && (
                    <form
                        onSubmit={manejarLoginTradicional}
                        className="space-y-4"
                    >
                        <div className="space-y-1.5">
                            <label className="text-xs font-bold text-foreground">
                                Correo Electrónico
                            </label>
                            <div className="relative">
                                <Mail className="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="email"
                                    placeholder="cliente@ejemplo.com"
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                    className="h-11 rounded-2xl pl-10 text-xs font-bold"
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-xs font-bold text-foreground">
                                Contraseña
                            </label>
                            <div className="relative">
                                <Lock className="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="password"
                                    placeholder="••••••••"
                                    value={password}
                                    onChange={(e) =>
                                        setPassword(e.target.value)
                                    }
                                    className="h-11 rounded-2xl pl-10 text-xs font-bold"
                                />
                            </div>
                        </div>

                        <Button
                            type="submit"
                            disabled={cargando}
                            className="mt-2 w-full rounded-2xl bg-bugambilia-600 font-extrabold text-white hover:bg-bugambilia-700 dark:bg-bugambilia-500"
                        >
                            {cargando ? 'Autenticando...' : 'Iniciar Sesión'}
                            <ArrowRight className="ml-2 size-4" />
                        </Button>
                    </form>
                )}
            </div>
        </div>
    );
};

export default ModalAccesoPortalCliente;
