import { Head } from '@inertiajs/react';
import { Sparkles } from 'lucide-react';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import { PerfilFormulario } from '@/modules/clientes/components/perfil/PerfilFormulario';
import type {
    ClienteProfile,
    EstadisticasHuesped,
} from '@/modules/clientes/types';

interface PerfilPageProps {
    cliente: ClienteProfile;
    estadisticas: EstadisticasHuesped;
}

export const Perfil = ({ cliente, estadisticas }: PerfilPageProps) => {
    return (
        <PortalLayout cliente={cliente}>
            <Head>
                <title>Mi Perfil — Portal de Huéspedes</title>
                <meta
                    name="description"
                    content="Administra tus datos de contacto, identificación y preferencias de huésped."
                />
            </Head>

            <div className="mx-auto max-w-4xl space-y-8 p-5 sm:p-8 lg:p-10">
                {/* Header */}
                <div>
                    <span className="text-xs font-black tracking-wider text-primary uppercase">
                        Portal de Huéspedes
                    </span>
                    <h1 className="mt-1 text-2xl font-black text-foreground sm:text-3xl">
                        Mi Perfil & Preferencias
                    </h1>
                    <p className="mt-0.5 text-sm text-muted-foreground">
                        Mantén tus datos actualizados para agilizar tus futuras
                        reservaciones y facturación.
                    </p>
                </div>

                {/* Banner de Estado del Huésped */}
                <div className="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-primary/20 bg-gradient-to-r from-primary/10 via-card to-card p-6 shadow-xs">
                    <div className="flex items-center gap-4">
                        <div className="flex size-14 items-center justify-center rounded-2xl bg-primary text-xl font-bold text-white shadow-md shadow-primary/20">
                            {cliente.nombre
                                ? cliente.nombre
                                      .split(' ')
                                      .map((n) => n[0])
                                      .slice(0, 2)
                                      .join('')
                                      .toUpperCase()
                                : 'HB'}
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <h3 className="text-lg font-black text-foreground">
                                    {cliente.nombre}
                                </h3>
                                <span className="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-600 dark:text-amber-400">
                                    <Sparkles className="size-3" />
                                    <span>{cliente.tipo_cliente || 'VIP'}</span>
                                </span>
                            </div>
                            <span className="text-xs text-muted-foreground">
                                {cliente.email}
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center gap-4 text-xs font-bold text-muted-foreground">
                        <div className="text-center">
                            <span className="block text-base font-black text-foreground">
                                {estadisticas.total_reservas}
                            </span>
                            <span>Estancias</span>
                        </div>
                        <div className="text-center">
                            <span className="block text-base font-black text-primary">
                                {estadisticas.completadas}
                            </span>
                            <span>Completadas</span>
                        </div>
                    </div>
                </div>

                {/* Formulario de Perfil */}
                <div className="rounded-3xl border border-border/70 bg-card p-6 shadow-xs sm:p-8">
                    <div className="mb-6 border-b border-border/40 pb-4">
                        <h4 className="text-sm font-bold tracking-wider text-muted-foreground uppercase">
                            Información Personal y Contacto
                        </h4>
                    </div>
                    <PerfilFormulario cliente={cliente} />
                </div>
            </div>
        </PortalLayout>
    );
};

Perfil.layout = (page: React.ReactNode) => page;

export default Perfil;
