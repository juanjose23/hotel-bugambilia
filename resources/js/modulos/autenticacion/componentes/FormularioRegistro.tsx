import { useForm, Link } from '@inertiajs/react';
import {
    Eye,
    EyeOff,
    Mail,
    Lock,
    Phone,
    Hash,
    AlertCircle,
} from 'lucide-react';
import { useState } from 'react';
import { usePropiedadesPagina } from '@/modulos/compartido/hooks/usePropiedadesPagina';
import { Button } from '@/modulos/compartido/ui/boton';
import { Checkbox } from '@/modulos/compartido/ui/casilla';
import { Input } from '@/modulos/compartido/ui/entrada';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/modulos/compartido/ui/selector';
import { Card, CardContent } from '@/modulos/compartido/ui/tarjeta';
import { OPCIONES_TIPO_IDENTIFICACION } from '../constantes/autenticacionConstantes';
import type {
    TipoPersona,
    TipoIdentificacion,
} from '../interfaces/autenticacionInterfaces';
import { EncabezadoAuth } from './EncabezadoAuth';
import { CamposPersonaJuridica } from './secciones/CamposPersonaJuridica';
import { CamposPersonaNatural } from './secciones/CamposPersonaNatural';
import { SelectorTipoPersona } from './SelectorTipoPersona';

export const FormularioRegistro = () => {
    const { hotel, flash } = usePropiedadesPagina();
    const hotelName = hotel?.name || 'Hotel Bugambilias';
    const warning = flash?.warning;
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        tipo_persona: 'natural' as TipoPersona,
        primer_nombre: '',
        primer_apellido: '',
        razon_social: '',
        tipo_identificacion: 'cedula' as TipoIdentificacion,
        numero_identificacion: '',
        email: '',
        phone: '',
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
        <section className="bg-background py-4 font-sans sm:py-8">
            <div className="container mx-auto px-4 sm:px-6">
                <div className="mx-auto max-w-lg">
                    <EncabezadoAuth
                        badge="Registro de Huésped"
                        titulo="Crear una"
                        subtituloEnfasis="Cuenta"
                        descripcion={`Disfrute de ofertas exclusivas y reservas preferenciales en ${hotelName}`}
                    />

                    <Card className="rounded-3xl border-border/80 bg-card p-6 sm:p-7">
                        <CardContent className="p-0">
                            {warning && (
                                <div className="mb-4 flex items-center gap-2 rounded-2xl border border-rose-500/20 bg-rose-500/10 p-3.5 text-xs font-bold text-rose-600 dark:text-rose-400">
                                    <AlertCircle className="size-4 shrink-0" />
                                    <span>{warning}</span>
                                </div>
                            )}

                            <form
                                onSubmit={handleSubmit}
                                className="flex flex-col gap-3.5"
                            >
                                <SelectorTipoPersona
                                    tipoPersona={data.tipo_persona}
                                    onSeleccionar={(tipo) =>
                                        setData('tipo_persona', tipo)
                                    }
                                />

                                {isNatural ? (
                                    <CamposPersonaNatural
                                        primerNombre={data.primer_nombre}
                                        primerApellido={data.primer_apellido}
                                        onChangeNombre={(val) =>
                                            setData('primer_nombre', val)
                                        }
                                        onChangeApellido={(val) =>
                                            setData('primer_apellido', val)
                                        }
                                        errorNombre={errors.primer_nombre}
                                        errorApellido={errors.primer_apellido}
                                    />
                                ) : (
                                    <CamposPersonaJuridica
                                        razonSocial={data.razon_social}
                                        onChangeRazonSocial={(val) =>
                                            setData('razon_social', val)
                                        }
                                        errorRazonSocial={errors.razon_social}
                                    />
                                )}

                                <div className="grid grid-cols-3 gap-2">
                                    <div className="col-span-1 flex flex-col gap-1">
                                        <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Doc.
                                        </label>
                                        <Select
                                            value={data.tipo_identificacion}
                                            onValueChange={(val: string) =>
                                                setData(
                                                    'tipo_identificacion',
                                                    val as TipoIdentificacion,
                                                )
                                            }
                                        >
                                            <SelectTrigger className="w-full rounded-2xl bg-muted/40 text-xs">
                                                <SelectValue placeholder="Tipo" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {OPCIONES_TIPO_IDENTIFICACION.map(
                                                    (opcion) => (
                                                        <SelectItem
                                                            key={opcion.value}
                                                            value={opcion.value}
                                                        >
                                                            {opcion.label}
                                                        </SelectItem>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="col-span-2 flex flex-col gap-1">
                                        <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Número Identificación
                                        </label>
                                        <div className="relative">
                                            <Hash className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                                            <Input
                                                type="text"
                                                placeholder="001-000000-0000A"
                                                required
                                                value={
                                                    data.numero_identificacion
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'numero_identificacion',
                                                        e.target.value,
                                                    )
                                                }
                                                className="pl-10"
                                            />
                                        </div>
                                        {errors.numero_identificacion && (
                                            <p className="text-xs font-semibold text-rose-500">
                                                {errors.numero_identificacion}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    <div className="flex flex-col gap-1">
                                        <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Correo
                                        </label>
                                        <div className="relative">
                                            <Mail className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                                            <Input
                                                type="email"
                                                placeholder="ejemplo@correo.com"
                                                required
                                                value={data.email}
                                                onChange={(e) =>
                                                    setData(
                                                        'email',
                                                        e.target.value,
                                                    )
                                                }
                                                className="pl-10"
                                            />
                                        </div>
                                        {errors.email && (
                                            <p className="text-xs font-semibold text-rose-500">
                                                {errors.email}
                                            </p>
                                        )}
                                    </div>
                                    <div className="flex flex-col gap-1">
                                        <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Teléfono
                                        </label>
                                        <div className="relative">
                                            <Phone className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                                            <Input
                                                type="tel"
                                                placeholder="+505 8000 0000"
                                                required
                                                value={data.phone}
                                                onChange={(e) =>
                                                    setData(
                                                        'phone',
                                                        e.target.value,
                                                    )
                                                }
                                                className="pl-10"
                                            />
                                        </div>
                                        {errors.phone && (
                                            <p className="text-xs font-semibold text-rose-500">
                                                {errors.phone}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    <div className="flex flex-col gap-1">
                                        <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Contraseña
                                        </label>
                                        <div className="relative">
                                            <Lock className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                                            <Input
                                                type={
                                                    showPassword
                                                        ? 'text'
                                                        : 'password'
                                                }
                                                placeholder="••••••••"
                                                required
                                                value={data.password}
                                                onChange={(e) =>
                                                    setData(
                                                        'password',
                                                        e.target.value,
                                                    )
                                                }
                                                className="pr-10 pl-10"
                                            />
                                            <button
                                                type="button"
                                                className="absolute top-2.5 right-3 cursor-pointer text-muted-foreground hover:text-foreground"
                                                onClick={() =>
                                                    setShowPassword(
                                                        !showPassword,
                                                    )
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
                                        <label className="text-xs font-extrabold tracking-wider text-foreground uppercase">
                                            Confirmar
                                        </label>
                                        <div className="relative">
                                            <Lock className="absolute top-3 left-3.5 size-4 text-muted-foreground" />
                                            <Input
                                                type={
                                                    showPassword
                                                        ? 'text'
                                                        : 'password'
                                                }
                                                placeholder="••••••••"
                                                required
                                                value={
                                                    data.password_confirmation
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'password_confirmation',
                                                        e.target.value,
                                                    )
                                                }
                                                className="pl-10"
                                            />
                                        </div>
                                        {errors.password_confirmation && (
                                            <p className="text-xs font-semibold text-rose-500">
                                                {errors.password_confirmation}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="flex items-center gap-2 pt-1">
                                    <Checkbox
                                        id="terms"
                                        checked={data.acceptTerms}
                                        onCheckedChange={(checked) =>
                                            setData(
                                                'acceptTerms',
                                                checked === true,
                                            )
                                        }
                                    />
                                    <label
                                        htmlFor="terms"
                                        className="cursor-pointer text-xs font-medium text-muted-foreground hover:text-foreground"
                                    >
                                        Acepto los términos de servicio y
                                        políticas de privacidad
                                    </label>
                                </div>
                                {errors.acceptTerms && (
                                    <p className="text-xs font-semibold text-rose-500">
                                        {errors.acceptTerms}
                                    </p>
                                )}

                                <Button
                                    type="submit"
                                    disabled={processing}
                                    size="lg"
                                    className="mt-2 w-full rounded-2xl bg-primary font-black tracking-wider text-primary-foreground uppercase hover:bg-primary/90"
                                >
                                    {processing
                                        ? 'Creando cuenta...'
                                        : 'Crear Cuenta'}
                                </Button>
                            </form>

                            <div className="mt-5 text-center text-xs text-muted-foreground">
                                ¿Ya tiene una cuenta?{' '}
                                <Link
                                    href="/login"
                                    prefetch
                                    className="font-extrabold text-primary hover:underline"
                                >
                                    Inicie sesión aquí
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    );
};

export default FormularioRegistro;
