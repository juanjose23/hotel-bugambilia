import { Link } from '@inertiajs/react';
import {
    Mail,
    Lock,
    Eye,
    EyeOff,
    Loader2,
    KeyRound,
    Search,
    ArrowRight,
    UserCheck,
} from 'lucide-react';
import { useState } from 'react';
import { useAccesoCodigoForm } from '@/modules/clientes/hooks/useAccesoCodigoForm';
import { Button } from '@/modules/shared/components/ui/button';
import { Checkbox } from '@/modules/shared/components/ui/checkbox';
import { Input } from '@/modules/shared/components/ui/input';
import { useLoginForm } from '../hooks/useLoginForm';
import { GoogleButton } from './GoogleButton';

export const LoginForm = () => {
    const [metodo, setMetodo] = useState<'password' | 'codigo'>('password');
    const [mostrarContrasena, setMostrarContrasena] = useState(false);

    // Formulario de login normal
    const {
        register: registerLogin,
        handleSubmit: handleLoginSubmit,
        setValue: setLoginValue,
        watch: watchLogin,
        errors: errorsLogin,
        isSubmitting: isSubmittingLogin,
    } = useLoginForm();

    // Formulario de código de reserva
    const {
        register: registerCodigo,
        handleSubmit: handleCodigoSubmit,
        errors: errorsCodigo,
        isSubmitting: isSubmittingCodigo,
    } = useAccesoCodigoForm();

    const remember = watchLogin('remember');

    return (
        <div className="space-y-4">
            {/* Tabs de Selección de Método de Autenticación */}
            <div className="grid grid-cols-2 gap-1 rounded-full bg-slate-100 p-1 dark:bg-zinc-800/80">
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={() => setMetodo('password')}
                    className={`flex cursor-pointer items-center justify-center gap-1.5 rounded-full py-2 text-xs font-bold transition-all ${
                        metodo === 'password'
                            ? 'bg-card text-foreground shadow-sm hover:bg-card/90'
                            : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    <UserCheck className="size-3.5" />
                    <span>Contraseña</span>
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={() => setMetodo('codigo')}
                    className={`flex cursor-pointer items-center justify-center gap-1.5 rounded-full py-2 text-xs font-bold transition-all ${
                        metodo === 'codigo'
                            ? 'bg-card text-foreground shadow-sm hover:bg-card/90'
                            : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    <KeyRound className="size-3.5" />
                    <span>Código Reserva</span>
                </Button>
            </div>

            {metodo === 'password' ? (
                /* FORMULARIO LOGIN TRADICIONAL */
                <form
                    onSubmit={handleLoginSubmit}
                    noValidate
                    className="space-y-4"
                >
                    {/* Campo Correo Electrónico */}
                    <div className="space-y-1">
                        <div className="relative">
                            <Mail className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="email"
                                type="email"
                                autoComplete="email"
                                autoFocus
                                placeholder="Correo Electrónico"
                                {...registerLogin('email')}
                                className="h-11 rounded-full border-0 bg-slate-100 pr-4 pl-11 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                            />
                        </div>
                        {errorsLogin.email && (
                            <p className="px-3 text-[11px] font-bold text-destructive">
                                {errorsLogin.email.message}
                            </p>
                        )}
                    </div>

                    {/* Campo Contraseña */}
                    <div className="space-y-1">
                        <div className="relative">
                            <Lock className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="password"
                                type={mostrarContrasena ? 'text' : 'password'}
                                autoComplete="current-password"
                                placeholder="Contraseña"
                                {...registerLogin('password')}
                                className="h-11 rounded-full border-0 bg-slate-100 pr-11 pl-11 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                onClick={() =>
                                    setMostrarContrasena(!mostrarContrasena)
                                }
                                className="absolute top-1/2 right-2 size-7 -translate-y-1/2 rounded-full p-0 text-muted-foreground hover:bg-transparent hover:text-foreground"
                                aria-label={
                                    mostrarContrasena
                                        ? 'Ocultar contraseña'
                                        : 'Mostrar contraseña'
                                }
                            >
                                {mostrarContrasena ? (
                                    <EyeOff className="size-4" />
                                ) : (
                                    <Eye className="size-4" />
                                )}
                            </Button>
                        </div>
                        {errorsLogin.password && (
                            <p className="px-3 text-[11px] font-bold text-destructive">
                                {errorsLogin.password.message}
                            </p>
                        )}
                    </div>

                    {/* Recordar Sesión */}
                    <div className="flex items-center justify-between px-1">
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="remember"
                                checked={remember}
                                onCheckedChange={(checked) =>
                                    setLoginValue('remember', !!checked)
                                }
                            />
                            <label
                                htmlFor="remember"
                                className="cursor-pointer text-xs font-medium text-muted-foreground select-none"
                            >
                                Recordar mi sesión
                            </label>
                        </div>
                    </div>

                    {/* Botón Principal Iniciar Sesión */}
                    <Button
                        type="submit"
                        disabled={isSubmittingLogin}
                        className="mt-2 h-11 w-full cursor-pointer rounded-full bg-primary text-xs font-black text-primary-foreground shadow-md shadow-primary/20 transition-all hover:bg-primary/90 active:scale-[0.98]"
                    >
                        {isSubmittingLogin ? (
                            <>
                                <Loader2 className="mr-2 size-4 animate-spin" />
                                <span>Iniciando sesión...</span>
                            </>
                        ) : (
                            <span>Iniciar Sesión</span>
                        )}
                    </Button>
                </form>
            ) : (
                /* FORMULARIO ACCESO RÁPIDO CON CÓDIGO DE RESERVA */
                <form
                    onSubmit={handleCodigoSubmit}
                    noValidate
                    className="space-y-4"
                >
                    <div className="rounded-2xl bg-secondary/40 p-3 text-center">
                        <p className="text-xs text-muted-foreground">
                            Ingresa el código de confirmación de tu reserva para
                            acceder de inmediato a tu portal y estancia.
                        </p>
                    </div>

                    <div className="space-y-1">
                        <div className="relative">
                            <Search className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="codigo"
                                type="text"
                                autoFocus
                                placeholder="Ej. RES-2026-ABCD"
                                {...registerCodigo('codigo')}
                                className="h-11 rounded-full border-0 bg-slate-100 pr-4 pl-11 font-mono text-xs font-black tracking-wider uppercase placeholder:font-sans placeholder:tracking-normal focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                            />
                        </div>
                        {errorsCodigo.codigo && (
                            <p className="px-3 text-[11px] font-bold text-destructive">
                                {errorsCodigo.codigo.message}
                            </p>
                        )}
                    </div>

                    <Button
                        type="submit"
                        disabled={isSubmittingCodigo}
                        className="mt-2 h-11 w-full cursor-pointer rounded-full bg-primary text-xs font-black text-primary-foreground shadow-md shadow-primary/20 transition-all hover:bg-primary/90 active:scale-[0.98]"
                    >
                        {isSubmittingCodigo ? (
                            <>
                                <Loader2 className="mr-2 size-4 animate-spin" />
                                <span>Verificando reservación...</span>
                            </>
                        ) : (
                            <div className="flex items-center justify-center gap-1.5">
                                <span>Acceder con mi Reserva</span>
                                <ArrowRight className="size-4" />
                            </div>
                        )}
                    </Button>
                </form>
            )}

            {/* Separador */}
            <div className="relative my-4 flex items-center justify-center">
                <div className="w-full border-t border-border/70" />
                <span className="absolute bg-card px-3 text-[11px] font-medium text-muted-foreground">
                    O continúa con tu cuenta
                </span>
            </div>

            {/* Botón Circular de Google (OAuth) */}
            <div className="flex items-center justify-center pt-0.5">
                <GoogleButton variante="circular" />
            </div>

            {/* Enlace a Registro */}
            <div className="border-t border-border/40 pt-3 text-center text-xs text-muted-foreground">
                ¿No tienes cuenta aún?{' '}
                <Link
                    href="/auth/registro"
                    className="font-black text-primary hover:underline"
                >
                    Regístrate aquí
                </Link>
            </div>
        </div>
    );
};

export default LoginForm;
