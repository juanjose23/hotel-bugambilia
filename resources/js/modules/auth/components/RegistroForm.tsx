import { Link } from '@inertiajs/react';
import {
    User,
    Mail,
    Phone,
    Lock,
    Eye,
    EyeOff,
    Building2,
    Loader2,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import { Input } from '@/modules/shared/components/ui/input';
import { useRegistroForm } from '../hooks/useRegistroForm';
import type { TipoPersona } from '../types';
import { GoogleButton } from './GoogleButton';

export const RegistroForm = () => {
    const [mostrarContrasena, setMostrarContrasena] = useState(false);
    const {
        register,
        handleSubmit,
        setValue,
        tipoPersona,
        errors,
        isSubmitting,
    } = useRegistroForm();

    const cambiarTipoPersona = (tipo: TipoPersona) => {
        setValue('tipo_persona', tipo);

        if (tipo === 'juridica') {
            setValue('tipo_identificacion', 'ruc');
        } else {
            setValue('tipo_identificacion', 'cedula');
        }
    };

    return (
        <form onSubmit={handleSubmit} noValidate className="space-y-2">
            {/* Selector de Tipo de Cuenta */}
            <div className="flex rounded-full border border-border/60 bg-muted/40 p-0.5">
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={() => cambiarTipoPersona('natural')}
                    className={`flex h-7 flex-1 items-center justify-center gap-1 rounded-full py-1 text-[11px] font-bold transition-all ${
                        tipoPersona === 'natural'
                            ? 'bg-card text-foreground shadow-xs'
                            : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    <User className="size-3" />
                    <span>Persona</span>
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={() => cambiarTipoPersona('juridica')}
                    className={`flex h-7 flex-1 items-center justify-center gap-1 rounded-full py-1 text-[11px] font-bold transition-all ${
                        tipoPersona === 'juridica'
                            ? 'bg-card text-foreground shadow-xs'
                            : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    <Building2 className="size-3" />
                    <span>Empresa</span>
                </Button>
            </div>

            {/* Campos de Nombre según Tipo */}
            {tipoPersona === 'juridica' ? (
                <div className="space-y-0.5">
                    <div className="relative">
                        <Building2 className="pointer-events-none absolute top-1/2 left-3.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="razon_social"
                            placeholder="Razón Social o Empresa *"
                            {...register('razon_social')}
                            className="h-9 rounded-full border-0 bg-slate-100 pr-3 pl-9 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                        />
                    </div>
                    {errors.razon_social && (
                        <p className="px-2.5 text-[10px] font-bold text-destructive">
                            {errors.razon_social.message}
                        </p>
                    )}
                </div>
            ) : (
                <div className="grid grid-cols-2 gap-1.5">
                    <div className="space-y-0.5">
                        <div className="relative">
                            <User className="pointer-events-none absolute top-1/2 left-3.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="primer_nombre"
                                placeholder="Nombre *"
                                {...register('primer_nombre')}
                                className="h-9 rounded-full border-0 bg-slate-100 pr-2 pl-9 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                            />
                        </div>
                        {errors.primer_nombre && (
                            <p className="px-2 text-[10px] font-bold text-destructive">
                                {errors.primer_nombre.message}
                            </p>
                        )}
                    </div>

                    <div className="space-y-0.5">
                        <div className="relative">
                            <User className="pointer-events-none absolute top-1/2 left-3.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="primer_apellido"
                                placeholder="Apellido *"
                                {...register('primer_apellido')}
                                className="h-9 rounded-full border-0 bg-slate-100 pr-2 pl-9 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                            />
                        </div>
                        {errors.primer_apellido && (
                            <p className="px-2 text-[10px] font-bold text-destructive">
                                {errors.primer_apellido.message}
                            </p>
                        )}
                    </div>
                </div>
            )}

            {/* Correo y Teléfono en 2 columnas */}
            <div className="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
                <div className="space-y-0.5">
                    <div className="relative">
                        <Mail className="pointer-events-none absolute top-1/2 left-3.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="email"
                            type="email"
                            autoComplete="email"
                            placeholder="Correo *"
                            {...register('email')}
                            className="h-9 rounded-full border-0 bg-slate-100 pr-2 pl-9 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                        />
                    </div>
                    {errors.email && (
                        <p className="px-2.5 text-[10px] font-bold text-destructive">
                            {errors.email.message}
                        </p>
                    )}
                </div>

                <div className="space-y-0.5">
                    <div className="relative">
                        <Phone className="pointer-events-none absolute top-1/2 left-3.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="phone"
                            type="tel"
                            placeholder="Teléfono"
                            {...register('phone')}
                            className="h-9 rounded-full border-0 bg-slate-100 pr-2 pl-9 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                        />
                    </div>
                    {errors.phone && (
                        <p className="px-2.5 text-[10px] font-bold text-destructive">
                            {errors.phone.message}
                        </p>
                    )}
                </div>
            </div>

            {/* Contraseñas en 2 columnas */}
            <div className="grid grid-cols-2 gap-1.5">
                <div className="space-y-0.5">
                    <div className="relative">
                        <Lock className="pointer-events-none absolute top-1/2 left-3.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="password"
                            type={mostrarContrasena ? 'text' : 'password'}
                            autoComplete="new-password"
                            placeholder="Contraseña *"
                            {...register('password')}
                            className="h-9 rounded-full border-0 bg-slate-100 pr-7 pl-9 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                        />
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            onClick={() =>
                                setMostrarContrasena(!mostrarContrasena)
                            }
                            className="absolute top-1/2 right-2 size-6 -translate-y-1/2 rounded-full p-0 text-muted-foreground hover:bg-transparent hover:text-foreground"
                            aria-label={
                                mostrarContrasena
                                    ? 'Ocultar contraseña'
                                    : 'Mostrar contraseña'
                            }
                        >
                            {mostrarContrasena ? (
                                <EyeOff className="size-3" />
                            ) : (
                                <Eye className="size-3" />
                            )}
                        </Button>
                    </div>
                    {errors.password && (
                        <p className="px-2 text-[10px] font-bold text-destructive">
                            {errors.password.message}
                        </p>
                    )}
                </div>

                <div className="space-y-0.5">
                    <div className="relative">
                        <Lock className="pointer-events-none absolute top-1/2 left-3.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="password_confirmation"
                            type={mostrarContrasena ? 'text' : 'password'}
                            autoComplete="new-password"
                            placeholder="Confirmar *"
                            {...register('password_confirmation')}
                            className="h-9 rounded-full border-0 bg-slate-100 pr-2 pl-9 text-xs font-medium focus-visible:ring-2 focus-visible:ring-primary/30 dark:bg-zinc-800/70"
                        />
                    </div>
                    {errors.password_confirmation && (
                        <p className="px-2 text-[10px] font-bold text-destructive">
                            {errors.password_confirmation.message}
                        </p>
                    )}
                </div>
            </div>

            {/* Botón de Enviar */}
            <Button
                type="submit"
                disabled={isSubmitting}
                className="mt-1.5 h-10 w-full cursor-pointer rounded-full bg-primary text-xs font-black text-primary-foreground shadow-md shadow-primary/20 transition-all hover:bg-primary/90 active:scale-[0.98]"
            >
                {isSubmitting ? (
                    <>
                        <Loader2 className="mr-2 size-3.5 animate-spin" />
                        <span>Creando cuenta...</span>
                    </>
                ) : (
                    <span>Registrarme</span>
                )}
            </Button>

            {/* Divisor */}
            <div className="relative my-2 flex items-center justify-center">
                <div className="w-full border-t border-border/70" />
                <span className="absolute bg-card px-2.5 text-[10px] font-medium text-muted-foreground">
                    O continúa con tu cuenta
                </span>
            </div>

            {/* Botón Circular de Google */}
            <div className="flex items-center justify-center pt-0.5">
                <GoogleButton variante="circular" />
            </div>

            {/* Enlace a Login */}
            <div className="border-t border-border/40 pt-2 text-center text-xs text-muted-foreground">
                ¿Ya tienes una cuenta registrada?{' '}
                <Link
                    href="/auth/login"
                    className="font-black text-primary hover:underline"
                >
                    Inicia sesión aquí
                </Link>
            </div>
        </form>
    );
};

export default RegistroForm;
