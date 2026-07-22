import { usePage, Link, useForm } from '@inertiajs/react';
import { Eye, EyeOff, Mail, Lock, AlertCircle } from 'lucide-react';
import type React from 'react';
import { useState } from 'react';
import { Checkbox } from '@/modules/shared/ui/checkbox';

export default function LoginForm() {
    const pageProps = usePage().props as any;
    const hotelName = pageProps.hotel?.name || 'Hotel Bugambilias';

    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e: React.SubmitEvent) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <section className="bg-background py-16 font-sans md:py-24">
            <div className="container mx-auto px-4 sm:px-6">
                <div className="mx-auto max-w-md">
                    {/* Header Badge & Title */}
                    <div className="mb-8 text-center">
                        <div className="mb-4">
                            <img
                                src="/images/logo-dark.png"
                                alt="Hotel Bugambilias"
                                className="mx-auto h-10 object-contain sm:h-12 dark:hidden"
                            />
                            <img
                                src="/images/logo-claro.png"
                                alt="Hotel Bugambilias"
                                className="mx-auto hidden h-10 object-contain sm:h-12 dark:block"
                            />
                        </div>
                        <h1 className="mb-2 text-3xl font-black tracking-tight text-foreground sm:text-4xl">
                            Bienvenido de{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                Vuelta
                            </span>
                        </h1>
                        <p className="text-xs font-medium text-muted-foreground sm:text-sm">
                            Inicie sesión para gestionar sus reservas en{' '}
                            {hotelName}
                        </p>
                    </div>

                    {/* Luxury Card */}
                    <div className="shadow-airbnb-hover rounded-3xl border border-border/80 bg-card p-6 sm:p-8">
                        {pageProps.flash?.warning && (
                            <div className="mb-5 flex items-center gap-2 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4 text-xs font-bold text-amber-600 dark:text-amber-400">
                                <AlertCircle className="h-4 w-4 shrink-0" />
                                <span>{pageProps.flash.warning}</span>
                            </div>
                        )}

                        <form onSubmit={handleSubmit} className="space-y-5">
                            <div>
                                <label
                                    htmlFor="email"
                                    className="mb-2 block text-xs font-extrabold tracking-wider text-foreground uppercase"
                                >
                                    Correo Electrónico
                                </label>
                                <div className="relative">
                                    <Mail className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                    <input
                                        id="email"
                                        type="email"
                                        placeholder="ejemplo@correo.com"
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                        className="w-full rounded-2xl border border-border/80 bg-background py-3 pr-4 pl-10 text-xs text-foreground transition-colors outline-none placeholder:text-muted-foreground/60 focus:border-bugambilia-500 sm:text-sm"
                                        required
                                    />
                                </div>
                                {errors.email && (
                                    <p className="mt-1 text-xs font-semibold text-rose-500">
                                        {errors.email}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="password"
                                    className="mb-2 block text-xs font-extrabold tracking-wider text-foreground uppercase"
                                >
                                    Contraseña
                                </label>
                                <div className="relative">
                                    <Lock className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                    <input
                                        id="password"
                                        type={
                                            showPassword ? 'text' : 'password'
                                        }
                                        placeholder="••••••••"
                                        value={data.password}
                                        onChange={(e) =>
                                            setData('password', e.target.value)
                                        }
                                        className="w-full rounded-2xl border border-border/80 bg-background py-3 pr-12 pl-10 text-xs text-foreground transition-colors outline-none placeholder:text-muted-foreground/60 focus:border-bugambilia-500 sm:text-sm"
                                        required
                                    />
                                    <button
                                        type="button"
                                        className="absolute top-3 right-3.5 cursor-pointer text-muted-foreground transition-colors hover:text-foreground"
                                        onClick={() =>
                                            setShowPassword(!showPassword)
                                        }
                                    >
                                        {showPassword ? (
                                            <EyeOff className="h-4 w-4" />
                                        ) : (
                                            <Eye className="h-4 w-4" />
                                        )}
                                    </button>
                                </div>
                                {errors.password && (
                                    <p className="mt-1 text-xs font-semibold text-rose-500">
                                        {errors.password}
                                    </p>
                                )}
                            </div>

                            <div className="flex items-center justify-between pt-1 text-xs">
                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="remember"
                                        checked={data.remember}
                                        onCheckedChange={(checked) =>
                                            setData(
                                                'remember',
                                                checked as boolean,
                                            )
                                        }
                                    />
                                    <label
                                        htmlFor="remember"
                                        className="cursor-pointer font-semibold text-muted-foreground"
                                    >
                                        Recordarme
                                    </label>
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="shadow-airbnb hover:shadow-airbnb-hover mt-2 w-full cursor-pointer rounded-full bg-bugambilia-600 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase transition-all duration-300 hover:scale-[1.02] hover:bg-bugambilia-700 disabled:opacity-50"
                            >
                                {processing
                                    ? 'Iniciando sesión...'
                                    : 'Iniciar Sesión'}
                            </button>
                        </form>

                        <div className="mt-6 text-center text-xs text-muted-foreground">
                            ¿No tiene una cuenta aún?{' '}
                            <Link
                                href="/registro"
                                className="font-extrabold text-bugambilia-600 hover:underline dark:text-bugambilia-400"
                            >
                                Regístrese aquí
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
