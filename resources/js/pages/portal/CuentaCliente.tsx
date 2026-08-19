import { Head, Link } from '@inertiajs/react';
import {
    User,
    Lock,
    Calendar,
    Receipt,
    CreditCard,
    ShieldCheck,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { FormularioCambiarContrasena } from '@/modulos/autenticacion/componentes/FormularioCambiarContrasena';
import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';
import { usePropiedadesPagina } from '@/modulos/compartido/hooks/usePropiedadesPagina';
import { Badge } from '@/modulos/compartido/ui/insignia';
import { Card } from '@/modulos/compartido/ui/tarjeta';
import { LayoutPortalCliente } from '@/modulos/portal/componentes/layouts/LayoutPortalCliente';

interface PropiedadesCuentaCliente {
    reservas?: ReservaClienteDomain[];
}

export const CuentaCliente = ({ reservas = [] }: PropiedadesCuentaCliente) => {
    const { auth } = usePropiedadesPagina();
    const usuario = auth?.user;

    const reservasActivas = reservas.filter(
        (r) => r.estado === 1 || r.estado === 2,
    );

    const totalPendiente = reservas.reduce(
        (acc, r) => acc + (r.estado_cuenta?.saldo_pendiente || 0),
        0,
    );

    return (
        <>
            <Head>
                <title>Mi Cuenta — Portal de Huéspedes Hotel Bugambilias</title>
            </Head>

            <section className="min-h-screen bg-background pt-4 pb-16 font-sans">
                <div className="container mx-auto max-w-4xl space-y-6 px-3 sm:px-6">
                    {/* Header */}
                    <div className="flex flex-col gap-2 rounded-3xl border border-border/80 bg-card p-6 shadow-xs sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-3">
                            <div className="flex size-12 items-center justify-center rounded-2xl bg-bugambilia-600 text-white shadow-xs">
                                <User className="size-6" />
                            </div>
                            <div>
                                <h1 className="text-xl font-black text-foreground sm:text-2xl">
                                    {usuario?.name ||
                                        'Huésped Hotel Bugambilias'}
                                </h1>
                                <p className="text-xs font-medium text-muted-foreground">
                                    {usuario?.email || 'Cuenta de usuario'}
                                </p>
                            </div>
                        </div>

                        <Badge
                            variant="outline"
                            className="self-start border-emerald-500/40 bg-emerald-500/10 text-emerald-600 sm:self-center dark:text-emerald-400"
                        >
                            <ShieldCheck className="mr-1 size-3.5" />
                            Huésped Autenticado
                        </Badge>
                    </div>

                    {/* Resumen de Estadísticas de Cuenta */}
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <Card className="flex items-center gap-3 p-4">
                            <div className="flex size-10 items-center justify-center rounded-xl bg-bugambilia-500/10 text-bugambilia-600 dark:text-bugambilia-400">
                                <Calendar className="size-5" />
                            </div>
                            <div>
                                <span className="block text-[10px] font-extrabold text-muted-foreground uppercase">
                                    Total de Reservas
                                </span>
                                <span className="text-lg font-black text-foreground">
                                    {reservas.length}
                                </span>
                            </div>
                        </Card>

                        <Card className="flex items-center gap-3 p-4">
                            <div className="flex size-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <Receipt className="size-5" />
                            </div>
                            <div>
                                <span className="block text-[10px] font-extrabold text-muted-foreground uppercase">
                                    Estancias Activas
                                </span>
                                <span className="text-lg font-black text-emerald-600 dark:text-emerald-400">
                                    {reservasActivas.length}
                                </span>
                            </div>
                        </Card>

                        <Card className="flex items-center gap-3 p-4">
                            <div className="flex size-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                <CreditCard className="size-5" />
                            </div>
                            <div>
                                <span className="block text-[10px] font-extrabold text-muted-foreground uppercase">
                                    Saldo Pendiente
                                </span>
                                <span className="text-lg font-black text-foreground">
                                    ${totalPendiente.toFixed(2)}
                                </span>
                            </div>
                        </Card>
                    </div>

                    {/* Formulario de Cambio de Contraseña & Seguridad */}
                    <Card className="space-y-4 p-6">
                        <div className="flex items-center gap-2 border-b border-border/60 pb-3">
                            <Lock className="size-4 text-bugambilia-600 dark:text-bugambilia-400" />
                            <h2 className="text-sm font-black tracking-wider text-foreground uppercase">
                                Seguridad & Cambio de Contraseña
                            </h2>
                        </div>
                        <FormularioCambiarContrasena />
                    </Card>

                    {/* Botón Acceso Rápido a Reservas */}
                    <div className="text-center">
                        <Link
                            href="/portal"
                            className="inline-flex items-center gap-2 rounded-full bg-bugambilia-600 px-6 py-2.5 text-xs font-extrabold text-white shadow-xs transition-colors hover:bg-bugambilia-700"
                        >
                            Ver Mis Reservaciones en el Portal
                        </Link>
                    </div>
                </div>
            </section>
        </>
    );
};

CuentaCliente.layout = (page: ReactNode) => (
    <LayoutPortalCliente>{page}</LayoutPortalCliente>
);

export default CuentaCliente;
