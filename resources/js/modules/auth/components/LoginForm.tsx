import { Link } from '@inertiajs/react';
import { Mail, Lock, Eye, EyeOff, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import { Checkbox } from '@/modules/shared/components/ui/checkbox';
import { Input } from '@/modules/shared/components/ui/input';
import { useLoginForm } from '../hooks/useLoginForm';
import { GoogleButton } from './GoogleButton';

export const LoginForm = () => {
    const [mostrarContrasena, setMostrarContrasena] = useState(false);
    const { register, handleSubmit, setValue, watch, errors, isSubmitting } =
        useLoginForm();

    const remember = watch('remember');

    return (
        <form onSubmit={handleSubmit} noValidate className="space-y-4">
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
                        {...register('email')}
                        className="h-11 rounded-full border-0 bg-slate-100 pr-4 pl-11 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                    />
                </div>
                {errors.email && (
                    <p className="px-3 text-[11px] font-bold text-destructive">
                        {errors.email.message}
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
                        {...register('password')}
                        className="h-11 rounded-full border-0 bg-slate-100 pr-11 pl-11 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={() => setMostrarContrasena(!mostrarContrasena)}
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
                {errors.password && (
                    <p className="px-3 text-[11px] font-bold text-destructive">
                        {errors.password.message}
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
                            setValue('remember', !!checked)
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

            {/* Botón Principal Iniciar Sesión en Cápsula */}
            <Button
                type="submit"
                disabled={isSubmitting}
                className="mt-2 h-11 w-full cursor-pointer rounded-full bg-primary text-xs font-black text-primary-foreground shadow-md shadow-primary/20 transition-all hover:bg-primary/90 active:scale-[0.98]"
            >
                {isSubmitting ? (
                    <>
                        <Loader2 className="mr-2 size-4 animate-spin" />
                        <span>Iniciando sesión...</span>
                    </>
                ) : (
                    <span>Iniciar Sesión</span>
                )}
            </Button>

            {/* Separador */}
            <div className="relative my-4 flex items-center justify-center">
                <div className="w-full border-t border-border/70" />
                <span className="absolute bg-card px-3 text-[11px] font-medium text-muted-foreground">
                    O continúa con tu cuenta
                </span>
            </div>

            {/* Botón Circular de Google */}
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
        </form>
    );
};

export default LoginForm;
