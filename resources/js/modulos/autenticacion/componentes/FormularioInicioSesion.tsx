import { Link, router, useForm } from '@inertiajs/react';
import { Eye, EyeOff, Mail, Lock, AlertCircle } from 'lucide-react';
import { useState } from 'react';
import { usePropiedadesPagina } from '@/modulos/compartido/hooks/usePropiedadesPagina';
import { Button } from '@/modulos/compartido/ui/boton';
import { Checkbox } from '@/modulos/compartido/ui/casilla';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import { useAlmacenReserva } from '@/modulos/reservas/stores/useAlmacenReserva';
import { EncabezadoAuth } from './EncabezadoAuth';

export const FormularioInicioSesion = () => {
    const { hotel, flash } = usePropiedadesPagina();
    const hotelName = hotel?.name || 'Hotel Bugambilias';
    const [showPassword, setShowPassword] = useState(false);
    const rutaRetorno = useAlmacenReserva(
        (estado) => estado.borrador?.rutaRetorno,
    );

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e: React.SubmitEvent) => {
        e.preventDefault();
        post('/login', {
            onSuccess: () => {
                if (rutaRetorno) {
                    router.visit(rutaRetorno);
                }
            },
        });
    };

    return (
        <section className="bg-background py-4 font-sans sm:py-8">
            <div className="container mx-auto px-4 sm:px-6">
                <div className="mx-auto max-w-md">
                    <EncabezadoAuth
                        badge="Acceso Huéspedes"
                        titulo="Bienvenido de"
                        subtituloEnfasis="Vuelta"
                        descripcion={`Inicie sesión para gestionar sus reservas en ${hotelName}`}
                    />

                    <Card className="rounded-3xl border-border/80 bg-card p-6 sm:p-7">
                        <CardContent className="p-0">
                            {flash?.exito && (
                                <div className="mb-4 flex items-center gap-2 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-3.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    <AlertCircle className="size-4 shrink-0" />
                                    <span>{flash.exito}</span>
                                </div>
                            )}
                            {(flash?.error || flash?.warning) && (
                                <div className="mb-4 flex items-center gap-2 rounded-2xl border border-rose-500/20 bg-rose-500/10 p-3.5 text-xs font-bold text-rose-600 dark:text-rose-400">
                                    <AlertCircle className="size-4 shrink-0" />
                                    <span>{flash.error || flash.warning}</span>
                                </div>
                            )}

                            <form
                                onSubmit={handleSubmit}
                                className="flex flex-col gap-4"
                            >
                                <div className="flex flex-col gap-1">
                                    <label
                                        htmlFor="email"
                                        className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                                    >
                                        Correo Electrónico
                                    </label>
                                    <div className="relative">
                                        <Mail className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                                        <Input
                                            id="email"
                                            type="email"
                                            placeholder="ejemplo@correo.com"
                                            value={data.email}
                                            onChange={(e) =>
                                                setData('email', e.target.value)
                                            }
                                            className="pl-10"
                                            required
                                        />
                                    </div>
                                    {errors.email && (
                                        <p className="text-xs font-semibold text-rose-500">
                                            {errors.email}
                                        </p>
                                    )}
                                </div>

                                <div className="flex flex-col gap-1">
                                    <label
                                        htmlFor="password"
                                        className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                                    >
                                        Contraseña
                                    </label>
                                    <div className="relative">
                                        <Lock className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                                        <Input
                                            id="password"
                                            type={
                                                showPassword
                                                    ? 'text'
                                                    : 'password'
                                            }
                                            placeholder="••••••••"
                                            value={data.password}
                                            onChange={(e) =>
                                                setData(
                                                    'password',
                                                    e.target.value,
                                                )
                                            }
                                            className="pr-12 pl-10"
                                            required
                                        />
                                        <button
                                            type="button"
                                            className="absolute top-2.5 right-3.5 cursor-pointer text-muted-foreground hover:text-foreground"
                                            onClick={() =>
                                                setShowPassword(!showPassword)
                                            }
                                        >
                                            {showPassword ? (
                                                <EyeOff className="size-4" />
                                            ) : (
                                                <Eye className="size-4" />
                                            )}
                                        </button>
                                    </div>
                                    {errors.password && (
                                        <p className="text-xs font-semibold text-rose-500">
                                            {errors.password}
                                        </p>
                                    )}
                                </div>

                                <div className="flex items-center justify-between pt-1 text-xs">
                                    <div className="flex items-center gap-2">
                                        <Checkbox
                                            id="remember"
                                            checked={data.remember}
                                            onCheckedChange={(checked) =>
                                                setData(
                                                    'remember',
                                                    checked === true,
                                                )
                                            }
                                        />
                                        <label
                                            htmlFor="remember"
                                            className="cursor-pointer font-semibold text-muted-foreground hover:text-foreground"
                                        >
                                            Recordarme en este dispositivo
                                        </label>
                                    </div>
                                </div>

                                <Button
                                    type="submit"
                                    disabled={processing}
                                    size="lg"
                                    className="mt-1 w-full rounded-2xl bg-primary font-black tracking-wider text-primary-foreground uppercase hover:bg-primary/90"
                                >
                                    {processing
                                        ? 'Iniciando sesión...'
                                        : 'Iniciar Sesión'}
                                </Button>
                            </form>

                            <div className="mt-5 text-center text-xs text-muted-foreground">
                                ¿No tiene una cuenta aún?{' '}
                                <Link
                                    href="/registro"
                                    prefetch
                                    className="font-extrabold text-primary hover:underline"
                                >
                                    Regístrese aquí
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    );
};

export default FormularioInicioSesion;
