import {
    Lock,
    Eye,
    EyeOff,
    KeyRound,
    Loader2,
    ShieldAlert,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';
import { useCambiarContrasenaForm } from '../hooks/useCambiarContrasenaForm';

export const CambiarContrasenaForm = () => {
    const [mostrarContrasena, setMostrarContrasena] = useState(false);
    const { register, handleSubmit, errors, isSubmitting } =
        useCambiarContrasenaForm();

    return (
        <form onSubmit={handleSubmit} noValidate className="space-y-4">
            {/* Alerta Informativa */}
            <div className="flex items-start gap-2.5 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-900 dark:text-amber-200">
                <ShieldAlert className="mt-0.5 size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                <p>
                    Por motivos de seguridad, es necesario que actualices tu
                    contraseña antes de continuar navegando en tu cuenta.
                </p>
            </div>

            {/* Contraseña Actual */}
            <div>
                <label
                    htmlFor="current_password"
                    className="mb-1.5 block text-xs font-black tracking-wide text-foreground uppercase"
                >
                    Contraseña Actual *
                </label>
                <div className="relative">
                    <Lock className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        id="current_password"
                        type={mostrarContrasena ? 'text' : 'password'}
                        autoComplete="current-password"
                        autoFocus
                        placeholder="••••••••"
                        {...register('current_password')}
                        className="h-11 rounded-2xl bg-card pr-10 pl-10 text-xs font-medium"
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
                {errors.current_password && (
                    <p className="mt-1.5 text-[11px] font-bold text-destructive">
                        {errors.current_password.message}
                    </p>
                )}
            </div>

            {/* Nueva Contraseña */}
            <div>
                <label
                    htmlFor="password"
                    className="mb-1.5 block text-xs font-black tracking-wide text-foreground uppercase"
                >
                    Nueva Contraseña *
                </label>
                <div className="relative">
                    <Lock className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        id="password"
                        type={mostrarContrasena ? 'text' : 'password'}
                        autoComplete="new-password"
                        placeholder="Mínimo 8 caracteres"
                        {...register('password')}
                        className="h-11 rounded-2xl bg-card pr-10 pl-10 text-xs font-medium"
                    />
                </div>
                {errors.password && (
                    <p className="mt-1.5 text-[11px] font-bold text-destructive">
                        {errors.password.message}
                    </p>
                )}
            </div>

            {/* Confirmar Nueva Contraseña */}
            <div>
                <label
                    htmlFor="password_confirmation"
                    className="mb-1.5 block text-xs font-black tracking-wide text-foreground uppercase"
                >
                    Confirmar Nueva Contraseña *
                </label>
                <div className="relative">
                    <Lock className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        id="password_confirmation"
                        type={mostrarContrasena ? 'text' : 'password'}
                        autoComplete="new-password"
                        placeholder="Repite la nueva contraseña"
                        {...register('password_confirmation')}
                        className="h-11 rounded-2xl bg-card pl-10 text-xs font-medium"
                    />
                </div>
                {errors.password_confirmation && (
                    <p className="mt-1.5 text-[11px] font-bold text-destructive">
                        {errors.password_confirmation.message}
                    </p>
                )}
            </div>

            {/* Botón de Enviar */}
            <Button
                type="submit"
                disabled={isSubmitting}
                className="mt-4 w-full cursor-pointer rounded-2xl bg-primary py-3.5 text-xs font-black text-primary-foreground shadow-md transition-all hover:bg-primary/90 active:scale-95"
            >
                {isSubmitting ? (
                    <>
                        <Loader2 className="mr-2 size-4 animate-spin" />
                        <span>Guardando contraseña...</span>
                    </>
                ) : (
                    <>
                        <KeyRound className="mr-2 size-4" />
                        <span>Actualizar Contraseña</span>
                    </>
                )}
            </Button>
        </form>
    );
};

export default CambiarContrasenaForm;
