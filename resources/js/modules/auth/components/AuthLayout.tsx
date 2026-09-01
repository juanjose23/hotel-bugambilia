import { Link, usePage } from '@inertiajs/react';
import {
    ChevronLeft,
    Moon,
    Sun,
    ShieldCheck,
    Award,
    Sparkles,
    Star,
    Lock,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/modules/shared/components/ui/button';
import { useTema } from '@/modules/shared/hooks/useTema';

interface AuthLayoutProps {
    children: ReactNode;
    titulo: string;
    subtitulo: string;
    imagenFondo?: string;
}

export const AuthLayout = ({
    children,
    titulo,
    subtitulo,
    imagenFondo = '/images/pool-front-view.webp',
}: AuthLayoutProps) => {
    const { tema, alternarTema } = useTema();
    const ruta = usePage().url;
    const esLogin = ruta.includes('login');
    const esRegistro = ruta.includes('registro');

    return (
        <div className="flex h-screen w-screen flex-col overflow-hidden bg-background font-sans text-foreground antialiased selection:bg-primary selection:text-primary-foreground lg:flex-row">
            {/* PANEL IZQUIERDO: Fotografía Inmersiva Boutique (Solo Desktop 50%) */}
            <div className="relative hidden h-full flex-col justify-between overflow-hidden bg-zinc-950 p-10 text-white lg:flex lg:w-1/2 xl:p-12">
                <img
                    src={imagenFondo}
                    alt="Hotel Bugambilias Estelí"
                    className="absolute inset-0 h-full w-full object-cover brightness-[0.4] transition-transform duration-1000 ease-out hover:scale-105 dark:brightness-[0.25]"
                />
                <div
                    aria-hidden="true"
                    className="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-black/60"
                />

                {/* Logo Corporativo Oficial */}
                <div className="relative z-10 flex items-center justify-between">
                    <Link href="/" className="group flex items-center gap-2">
                        <img
                            src="/images/logo-claro.webp"
                            alt="Hotel Bugambilias"
                            className="h-8.5 w-auto object-contain transition-transform group-hover:scale-105"
                        />
                    </Link>

                    <div className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-0.5 text-[11px] font-bold text-white shadow-xs backdrop-blur-md">
                        <Sparkles className="size-3 text-amber-300" />
                        <span>Experiencia Boutique</span>
                    </div>
                </div>

                {/* Mensaje Editorial Desktop */}
                <div className="relative z-10 my-auto py-4">
                    <div className="mb-2 flex items-center gap-1.5">
                        <div className="flex items-center gap-0.5 text-amber-400">
                            {[...Array(5)].map((_, i) => (
                                <Star
                                    key={i}
                                    className="size-3.5 fill-amber-400 text-amber-400"
                                />
                            ))}
                        </div>
                        <span className="text-[11px] font-black tracking-wide text-white/90">
                            4.9 / 5.0 en Estelí
                        </span>
                    </div>

                    <h2 className="text-3xl leading-tight font-black tracking-tight text-white xl:text-4xl">
                        Tu oasis de tranquilidad y confort exclusivo en Estelí.
                    </h2>

                    <p className="mt-2 max-w-md text-xs leading-relaxed text-white/80 xl:text-sm">
                        Gestiona tus estancias en habitaciones de lujo, eventos
                        en salones y gastronomía en Restaurante Absoluto.
                    </p>

                    {/* Beneficios VIP */}
                    <div className="mt-4 grid max-w-md grid-cols-1 gap-2">
                        <div className="flex items-center gap-2.5 rounded-xl border border-white/15 bg-white/10 p-2.5 backdrop-blur-md">
                            <div className="flex size-7.5 shrink-0 items-center justify-center rounded-lg bg-primary/40 text-white">
                                <Award className="size-3.5" />
                            </div>
                            <div className="text-xs">
                                <span className="block text-[11px] font-bold text-white">
                                    Tarifas preferenciales garantizadas
                                </span>
                                <p className="text-[10px] text-white/70">
                                    Descuento exclusivo directo en cada reserva
                                    iniciada.
                                </p>
                            </div>
                        </div>

                        <div className="flex items-center gap-2.5 rounded-xl border border-white/15 bg-white/10 p-2.5 backdrop-blur-md">
                            <div className="flex size-7.5 shrink-0 items-center justify-center rounded-lg bg-primary/40 text-white">
                                <ShieldCheck className="size-3.5" />
                            </div>
                            <div className="text-xs">
                                <span className="block text-[11px] font-bold text-white">
                                    Check-in prioritario sin comisiones
                                </span>
                                <p className="text-[10px] text-white/70">
                                    Confirmación instantánea de tu habitación.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Pie Inferior Desktop */}
                <div className="relative z-10 flex items-center justify-between border-t border-white/10 pt-3 text-[11px] text-white/60">
                    <div className="flex items-center gap-1.5">
                        <Lock className="size-3 text-rose-300" />
                        <span>Cifrado SSL de 256 bits</span>
                    </div>
                    <span>© {new Date().getFullYear()} Hotel Bugambilias</span>
                </div>
            </div>

            {/* PANEL DERECHO / VISTA COMPLETA (Móvil con Imagen de Hotel y Tarjeta Curva) */}
            <div className="relative flex h-full flex-1 flex-col justify-between overflow-y-auto bg-background lg:overflow-hidden">
                {/* CABECERA MÓVIL: Imagen Real del Hotel con Overlay Cinematográfico */}
                <div className="relative flex h-44 w-full shrink-0 flex-col justify-between overflow-hidden p-4 pb-6 text-white sm:h-48 lg:hidden">
                    <img
                        src={imagenFondo}
                        alt="Hotel Bugambilias"
                        className="absolute inset-0 h-full w-full object-cover brightness-[0.5]"
                    />
                    <div
                        aria-hidden="true"
                        className="absolute inset-0 bg-gradient-to-b from-black/80 via-black/30 to-black/70"
                    />

                    {/* Botones Flotantes Superiores en Móvil */}
                    <div className="relative z-10 flex w-full items-center justify-between">
                        <Link
                            href="/"
                            aria-label="Volver al Hotel"
                            className="flex size-8.5 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-md transition-transform active:scale-90"
                        >
                            <ChevronLeft className="size-5" />
                        </Link>

                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            onClick={alternarTema}
                            aria-label="Alternar tema"
                            className="size-8.5 rounded-full bg-black/40 text-white backdrop-blur-md hover:bg-black/50 active:scale-90"
                        >
                            {tema === 'dark' ? (
                                <Sun className="size-4 text-amber-300" />
                            ) : (
                                <Moon className="size-4 text-white" />
                            )}
                        </Button>
                    </div>

                    {/* Logo Centrado sobre la Foto */}
                    <div className="relative z-10 mb-2 flex flex-col items-center justify-center">
                        <img
                            src="/images/logo-claro.webp"
                            alt="Hotel Bugambilias"
                            className="h-10 w-auto object-contain drop-shadow-lg"
                        />
                    </div>
                </div>

                {/* Barra de Navegación solo en Desktop */}
                <div className="hidden w-full shrink-0 items-center justify-between p-6 pb-0 lg:flex">
                    <Link
                        href="/"
                        className="inline-flex items-center gap-1.5 rounded-full border border-border bg-card px-3.5 py-1.5 text-xs font-bold text-foreground shadow-2xs transition-all hover:bg-muted active:scale-95"
                    >
                        <ChevronLeft className="size-3.5 text-primary" />
                        <span>Volver al Hotel</span>
                    </Link>

                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        onClick={alternarTema}
                        aria-label="Alternar tema"
                        className="size-8.5 cursor-pointer rounded-full transition-transform active:scale-95"
                    >
                        {tema === 'dark' ? (
                            <Sun className="size-4 text-amber-400" />
                        ) : (
                            <Moon className="size-4 text-foreground" />
                        )}
                    </Button>
                </div>

                {/* TARJETA DEL FORMULARIO (En móvil solapa con -mt-6 y rounded-t-[48px] ultra curvado) */}
                <div className="relative z-20 -mt-6 flex flex-1 flex-col justify-center rounded-t-[48px] border-t border-border/40 bg-background p-5 pt-4 shadow-2xl sm:p-7 lg:mt-0 lg:rounded-none lg:border-t-0 lg:shadow-none">
                    <div className="mx-auto my-auto w-full max-w-sm">
                        {/* Indicador Curvo Decorativo (Pill Handle) en Móvil */}
                        <div className="mx-auto mb-3 h-1.5 w-12 rounded-full bg-muted-foreground/25 lg:hidden" />

                        {/* Switcher de Pestañas Tipo Píldora */}
                        {(esLogin || esRegistro) && (
                            <div className="mb-3 flex rounded-full border border-border/70 bg-muted/60 p-1">
                                <Link
                                    href="/auth/login"
                                    className={`flex-1 rounded-full py-1 text-center text-xs font-black transition-all ${
                                        esLogin
                                            ? 'bg-card text-foreground shadow-xs'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    Iniciar Sesión
                                </Link>
                                <Link
                                    href="/auth/registro"
                                    className={`flex-1 rounded-full py-1 text-center text-xs font-black transition-all ${
                                        esRegistro
                                            ? 'bg-card text-foreground shadow-xs'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    Crear Cuenta
                                </Link>
                            </div>
                        )}

                        {/* Título */}
                        <div className="mb-2.5">
                            <h1 className="text-xl font-black tracking-tight text-foreground sm:text-2xl">
                                {titulo}
                            </h1>
                            <p className="mt-0.5 text-xs leading-snug text-muted-foreground">
                                {subtitulo}
                            </p>
                        </div>

                        {/* Formulario */}
                        {children}
                    </div>
                </div>

                {/* Footer solo Desktop */}
                <div className="hidden w-full shrink-0 border-t border-border/40 py-2.5 text-center text-xs text-muted-foreground lg:block">
                    <div className="flex flex-wrap items-center justify-center gap-3 text-[11px]">
                        <Link
                            href="/contacto"
                            className="hover:text-foreground hover:underline"
                        >
                            Soporte
                        </Link>
                        <span>•</span>
                        <Link
                            href="/acerca-de"
                            className="hover:text-foreground hover:underline"
                        >
                            Sobre Bugambilias
                        </Link>
                        <span>•</span>
                        <Link
                            href="/habitaciones"
                            className="hover:text-foreground hover:underline"
                        >
                            Habitaciones
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AuthLayout;
