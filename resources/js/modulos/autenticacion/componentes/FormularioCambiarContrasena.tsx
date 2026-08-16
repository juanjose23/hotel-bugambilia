import { useForm, Link } from '@inertiajs/react';
import { Eye, EyeOff, Lock } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/modulos/compartido/ui/boton';
import { Input } from '@/modulos/compartido/ui/entrada';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import type { PropiedadesFormularioCambiarContrasena } from '../interfaces/autenticacionInterfaces';
import { EncabezadoAuth } from './EncabezadoAuth';

export const FormularioCambiarContrasena = ({
    token,
    email,
}: PropiedadesFormularioCambiarContrasena) => {
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        token: token || '',
        email: email || '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: React.SubmitEvent) => {
        e.preventDefault();
        post('/reset-password');
    };

    return (
        <section className="bg-background py-4 font-sans sm:py-8">
            <div className="container mx-auto px-4 sm:px-6">
                <div className="mx-auto max-w-md">
                    <EncabezadoAuth
                        badge="Seguridad de Cuenta"
                        titulo="Restablecer"
                        subtituloEnfasis="Contraseña"
                        descripcion="Ingrese su nueva contraseña de acceso para continuar"
                    />

                    <Card className="rounded-3xl border-border/80 bg-card p-6 sm:p-7">
                        <CardContent className="p-0">
                            <form
                                onSubmit={handleSubmit}
                                className="flex flex-col gap-4"
                            >
                                <div className="flex flex-col gap-1">
                                    <label
                                        htmlFor="password"
                                        className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                                    >
                                        Nueva Contraseña
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

                                <div className="flex flex-col gap-1">
                                    <label
                                        htmlFor="password_confirmation"
                                        className="text-xs font-extrabold tracking-wider text-foreground uppercase"
                                    >
                                        Confirmar Nueva Contraseña
                                    </label>
                                    <div className="relative">
                                        <Lock className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                                        <Input
                                            id="password_confirmation"
                                            type={
                                                showPassword
                                                    ? 'text'
                                                    : 'password'
                                            }
                                            placeholder="••••••••"
                                            value={data.password_confirmation}
                                            onChange={(e) =>
                                                setData(
                                                    'password_confirmation',
                                                    e.target.value,
                                                )
                                            }
                                            className="pl-10"
                                            required
                                        />
                                    </div>
                                    {errors.password_confirmation && (
                                        <p className="text-xs font-semibold text-rose-500">
                                            {errors.password_confirmation}
                                        </p>
                                    )}
                                </div>

                                <Button
                                    type="submit"
                                    disabled={processing}
                                    size="lg"
                                    className="mt-1 w-full rounded-2xl bg-primary font-black tracking-wider text-primary-foreground uppercase hover:bg-primary/90"
                                >
                                    {processing
                                        ? 'Actualizando...'
                                        : 'Actualizar Contraseña'}
                                </Button>
                            </form>

                            <div className="mt-5 text-center text-xs text-muted-foreground">
                                <Link
                                    href="/login"
                                    prefetch
                                    className="font-extrabold text-primary hover:underline"
                                >
                                    Volver al inicio de sesión
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    );
};

export default FormularioCambiarContrasena;
