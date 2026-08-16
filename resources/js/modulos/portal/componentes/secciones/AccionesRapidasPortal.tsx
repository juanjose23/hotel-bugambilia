import { Link } from '@inertiajs/react';
import { Plus, UserPlus, ConciergeBell, Building2 } from 'lucide-react';
import { Card } from '@/modulos/compartido/ui/tarjeta';

export const AccionesRapidasPortal = () => {
    const acciones = [
        {
            titulo: 'Nueva Reserva',
            descripcion: 'Explore habitaciones y espacios disponibles.',
            href: '/habitaciones',
            icono: Plus,
            destacado: true,
        },
        {
            titulo: 'Auto Check-In Express',
            descripcion: 'Registre huéspedes antes de su llegada.',
            href: '/reservas/check-in',
            icono: UserPlus,
            destacado: false,
        },
        {
            titulo: 'Servicios & Experiencias',
            descripcion: 'Tours, spa, transporte y banquetes.',
            href: '/servicios',
            icono: ConciergeBell,
            destacado: false,
        },
        {
            titulo: 'Salones de Eventos',
            descripcion: 'Espacios ejecutivos y sociales.',
            href: '/espacios',
            icono: Building2,
            destacado: false,
        },
    ];

    return (
        <Card className="rounded-3xl border border-border/80 bg-card p-6 font-sans shadow-xs md:p-8">
            <h3 className="mb-4 text-xs font-black tracking-widest text-muted-foreground uppercase">
                Acciones Rápidas del Portal
            </h3>
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                {acciones.map((acc, idx) => {
                    const Icono = acc.icono;

                    return (
                        <Link
                            key={idx}
                            href={acc.href}
                            className={`group flex items-start gap-3 rounded-2xl border p-4 transition-all duration-300 ${
                                acc.destacado
                                    ? 'border-bugambilia-500/40 bg-bugambilia-500/10 hover:bg-bugambilia-500/20'
                                    : 'border-border/70 bg-background hover:border-bugambilia-500/40 hover:shadow-md'
                            }`}
                        >
                            <div className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-bugambilia-600 text-white shadow-2xs group-hover:scale-105">
                                <Icono className="size-5" />
                            </div>
                            <div className="min-w-0">
                                <h4 className="text-xs font-black text-foreground group-hover:text-bugambilia-600 dark:group-hover:text-bugambilia-400">
                                    {acc.titulo}
                                </h4>
                                <p className="mt-0.5 text-[11px] leading-tight font-medium text-muted-foreground">
                                    {acc.descripcion}
                                </p>
                            </div>
                        </Link>
                    );
                })}
            </div>
        </Card>
    );
};

export default AccionesRapidasPortal;
