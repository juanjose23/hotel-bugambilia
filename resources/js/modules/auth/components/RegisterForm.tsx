import { usePage, Link, useForm } from '@inertiajs/react';
import {
    Eye,
    EyeOff,
    Mail,
    Lock,
    User,
    Phone,
    AlertCircle,
    Building2,
    UserRound,
    Hash,
} from 'lucide-react';
import type React from 'react';
import { useState } from 'react';
import { Checkbox } from '@/modules/shared/ui/checkbox';

export default function RegisterForm() {
    const pageProps = usePage().props as any;
    const hotelName = pageProps.hotel?.name || 'Hotel Bugambilias';

    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        tipo_persona: 'natural' as 'natural' | 'juridica',
        // Natural
        primer_nombre: '',
        primer_apellido: '',
        // Juridica
        razon_social: '',
        // Identificación
        tipo_identificacion: 'cedula',
        numero_identificacion: '',
        // Contacto
        email: '',
        phone: '',
        // Seguridad
        password: '',
        password_confirmation: '',
        acceptTerms: false,
    });

    const handleSubmit = (e: React.SubmitEvent) => {
        e.preventDefault();
        post('/registro');
    };

    const isNatural = data.tipo_persona === 'natural';

    return (
        <section className="bg-background py-16 font-sans md:py-24">
            <div className="container mx-auto px-4 sm:px-6">
                <div className="mx-auto max-w-lg">
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
                            Crear una{' '}
                            <span className="font-serif font-normal text-bugambilia-600 italic dark:text-bugambilia-400">
                                Cuenta
                            </span>
                        </h1>
                        <p className="text-xs font-medium text-muted-foreground sm:text-sm">
                            Disfrute de ofertas exclusivas y reservas
                            preferenciales en {hotelName}
                        </p>
                    </div>

                    <div className="shadow-airbnb-hover rounded-3xl border border-border/80 bg-card p-6 sm:p-8">
                        {pageProps.flash?.warning && (
                            <div className="mb-5 flex items-center gap-2 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4 text-xs font-bold text-amber-600 dark:text-amber-400">
                                <AlertCircle className="h-4 w-4 shrink-0" />
                                <span>{pageProps.flash.warning}</span>
                            </div>
                        )}

                        <form onSubmit={handleSubmit} className="space-y-4">
                            {/* Tipo de Persona */}
                            <div className="grid grid-cols-2 gap-2 rounded-2xl bg-muted/50 p-1">
                                <button
                                    type="button"
                                    onClick={() =>
                                        setData('tipo_persona', 'natural')
                                    }
                                    className={`flex items-center justify-center gap-2 rounded-xl py-2.5 text-xs font-extrabold tracking-wider uppercase transition-all ${
                                        isNatural
                                            ? 'border border-border bg-card text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    <UserRound className="h-3.5 w-3.5" />
                                    Persona Natural
                                </button>
                                <button
                                    type="button"
                                    onClick={() =>
                                        setData('tipo_persona', 'juridica')
                                    }
                                    className={`flex items-center justify-center gap-2 rounded-xl py-2.5 text-xs font-extrabold tracking-wider uppercase transition-all ${
                                        !isNatural
                                            ? 'border border-border bg-card text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    <Building2 className="h-3.5 w-3.5" />
                                    Empresa
                                </button>
                            </div>

                            {/* Nombre / Razón Social */}
                            {isNatural ? (
                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="mb-1.5 block text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Nombre
                                        </label>
                                        <div className="relative">
                                            <User className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                            <input
                                                type="text"
                                                placeholder="Su nombre"
                                                required
                                                value={data.primer_nombre}
                                                onChange={(e) =>
                                                    setData(
                                                        'primer_nombre',
                                                        e.target.value,
                                                    )
                                                }
                                                className="w-full rounded-2xl border border-border/80 bg-background py-3 pr-3 pl-10 text-xs text-foreground transition-colors outline-none focus:border-bugambilia-500 sm:text-sm"
                                            />
                                        </div>
                                        {errors.primer_nombre && (
                                            <p className="mt-1 text-xs font-semibold text-rose-500">
                                                {errors.primer_nombre}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="mb-1.5 block text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Apellido
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Su apellido"
                                            required
                                            value={data.primer_apellido}
                                            onChange={(e) =>
                                                setData(
                                                    'primer_apellido',
                                                    e.target.value,
                                                )
                                            }
                                            className="w-full rounded-2xl border border-border/80 bg-background px-4 py-3 text-xs text-foreground transition-colors outline-none focus:border-bugambilia-500 sm:text-sm"
                                        />
                                        {errors.primer_apellido && (
                                            <p className="mt-1 text-xs font-semibold text-rose-500">
                                                {errors.primer_apellido}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            ) : (
                                <div>
                                    <label className="mb-1.5 block text-xs font-extrabold tracking-wider text-foreground uppercase">
                                        Razón Social
                                    </label>
                                    <div className="relative">
                                        <Building2 className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                        <input
                                            type="text"
                                            placeholder="Nombre de la empresa o razón social"
                                            required
                                            value={data.razon_social}
                                            onChange={(e) =>
                                                setData(
                                                    'razon_social',
                                                    e.target.value,
                                                )
                                            }
                                            className="w-full rounded-2xl border border-border/80 bg-background py-3 pr-4 pl-10 text-xs text-foreground transition-colors outline-none focus:border-bugambilia-500 sm:text-sm"
                                        />
                                    </div>
                                    {errors.razon_social && (
                                        <p className="mt-1 text-xs font-semibold text-rose-500">
                                            {errors.razon_social}
                                        </p>
                                    )}
                                </div>
                            )}

                            {/* Identificación - Solo para empresas */}
                            {!isNatural && (
                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="mb-1.5 block text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Tipo ID
                                        </label>
                                        <div className="relative">
                                            <Hash className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                            <select
                                                value={data.tipo_identificacion}
                                                onChange={(e) =>
                                                    setData(
                                                        'tipo_identificacion',
                                                        e.target.value,
                                                    )
                                                }
                                                className="w-full appearance-none rounded-2xl border border-border/80 bg-background py-3 pr-3 pl-10 text-xs text-foreground transition-colors outline-none focus:border-bugambilia-500 sm:text-sm"
                                            >
                                                <option value="nit">NIT</option>
                                                <option value="ruc">RUC</option>
                                                <option value="otro">
                                                    Otro
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label className="mb-1.5 block text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Número ID
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Número de identificación"
                                            required={!isNatural}
                                            value={data.numero_identificacion}
                                            onChange={(e) =>
                                                setData(
                                                    'numero_identificacion',
                                                    e.target.value,
                                                )
                                            }
                                            className="w-full rounded-2xl border border-border/80 bg-background px-4 py-3 text-xs text-foreground transition-colors outline-none focus:border-bugambilia-500 sm:text-sm"
                                        />
                                        {errors.numero_identificacion && (
                                            <p className="mt-1 text-xs font-semibold text-rose-500">
                                                {errors.numero_identificacion}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* Email */}
                            <div>
                                <label className="mb-1.5 block text-xs font-extrabold tracking-wider text-foreground uppercase">
                                    Correo Electrónico
                                </label>
                                <div className="relative">
                                    <Mail className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                    <input
                                        type="email"
                                        placeholder="ejemplo@correo.com"
                                        required
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                        className="w-full rounded-2xl border border-border/80 bg-background py-3 pr-4 pl-10 text-xs text-foreground transition-colors outline-none focus:border-bugambilia-500 sm:text-sm"
                                    />
                                </div>
                                {errors.email && (
                                    <p className="mt-1 text-xs font-semibold text-rose-500">
                                        {errors.email}
                                    </p>
                                )}
                            </div>

                            {/* Teléfono - Solo para empresas */}
                            {!isNatural && (
                                <div>
                                    <label className="mb-1.5 block text-xs font-extrabold tracking-wider text-foreground uppercase">
                                        Teléfono / WhatsApp
                                    </label>
                                    <div className="relative">
                                        <Phone className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                        <input
                                            type="tel"
                                            placeholder="+505 8713 6805"
                                            required={!isNatural}
                                            value={data.phone}
                                            onChange={(e) =>
                                                setData('phone', e.target.value)
                                            }
                                            className="w-full rounded-2xl border border-border/80 bg-background py-3 pr-4 pl-10 text-xs text-foreground transition-colors outline-none focus:border-bugambilia-500 sm:text-sm"
                                        />
                                    </div>
                                    {errors.phone && (
                                        <p className="mt-1 text-xs font-semibold text-rose-500">
                                            {errors.phone}
                                        </p>
                                    )}
                                </div>
                            )}

                            {/* Password */}
                            <div>
                                <label className="mb-1.5 block text-xs font-extrabold tracking-wider text-foreground uppercase">
                                    Contraseña
                                </label>
                                <div className="relative">
                                    <Lock className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                    <input
                                        type={
                                            showPassword ? 'text' : 'password'
                                        }
                                        placeholder="Mínimo 8 caracteres"
                                        required
                                        value={data.password}
                                        onChange={(e) =>
                                            setData('password', e.target.value)
                                        }
                                        className="w-full rounded-2xl border border-border/80 bg-background py-3 pr-12 pl-10 text-xs text-foreground transition-colors outline-none focus:border-bugambilia-500 sm:text-sm"
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

                            {/* Confirm */}
                            <div>
                                <label className="mb-1.5 block text-xs font-extrabold tracking-wider text-foreground uppercase">
                                    Confirmar Contraseña
                                </label>
                                <div className="relative">
                                    <Lock className="absolute top-3.5 left-3.5 h-4 w-4 text-muted-foreground" />
                                    <input
                                        type={
                                            showPassword ? 'text' : 'password'
                                        }
                                        placeholder="Repita su contraseña"
                                        required
                                        value={data.password_confirmation}
                                        onChange={(e) =>
                                            setData(
                                                'password_confirmation',
                                                e.target.value,
                                            )
                                        }
                                        className="w-full rounded-2xl border border-border/80 bg-background py-3 pr-12 pl-10 text-xs text-foreground transition-colors outline-none focus:border-bugambilia-500 sm:text-sm"
                                    />
                                </div>
                            </div>

                            <div className="flex items-start space-x-2 pt-2">
                                <Checkbox
                                    id="acceptTerms"
                                    checked={data.acceptTerms}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'acceptTerms',
                                            checked as boolean,
                                        )
                                    }
                                    required
                                />
                                <label
                                    htmlFor="acceptTerms"
                                    className="cursor-pointer text-xs leading-snug text-muted-foreground"
                                >
                                    Acepto los Términos y Condiciones y la
                                    Política de Privacidad de {hotelName}.
                                </label>
                            </div>

                            <button
                                type="submit"
                                disabled={processing || !data.acceptTerms}
                                className="shadow-airbnb hover:shadow-airbnb-hover mt-4 w-full cursor-pointer rounded-full bg-bugambilia-600 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase transition-all duration-300 hover:scale-[1.02] hover:bg-bugambilia-700 disabled:opacity-50"
                            >
                                {processing
                                    ? 'Creando cuenta...'
                                    : 'Crear Cuenta'}
                            </button>
                        </form>

                        <div className="mt-6 text-center text-xs text-muted-foreground">
                            ¿Ya tiene una cuenta registrada?{' '}
                            <Link
                                href="/login"
                                className="font-extrabold text-bugambilia-600 hover:underline dark:text-bugambilia-400"
                            >
                                Inicie sesión aquí
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
