import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, UtensilsCrossed } from 'lucide-react';
import { useState } from 'react';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import { CatalogoServicioItem } from '@/modules/clientes/components/servicios/CatalogoServicioItem';
import type {
    CatalogoServicioItemData,
    PortalReservaDetalleCompleto,
} from '@/modules/clientes/types';
import { buttonVariants } from '@/modules/shared/components/ui/button';

interface SolicitarServiciosProps {
    reserva: PortalReservaDetalleCompleto;
    servicios: CatalogoServicioItemData[];
}

export const SolicitarServicios = ({
    reserva,
    servicios,
}: SolicitarServiciosProps) => {
    const [categoriaSeleccionada, setCategoriaSeleccionada] =
        useState<string>('todos');
    const [procesandoId, setProcesandoId] = useState<number | null>(null);

    const categorias = [
        'todos',
        ...Array.from(new Set(servicios.map((s) => s.categoria))),
    ];

    const serviciosFiltrados =
        categoriaSeleccionada === 'todos'
            ? servicios
            : servicios.filter((s) => s.categoria === categoriaSeleccionada);

    const handleSolicitarServicio = (
        servicioId: number,
        cantidad: number,
        notas?: string,
    ) => {
        setProcesandoId(servicioId);
        router.post(
            `/portal/reservas/${reserva.id}/servicios`,
            {
                servicio_id: servicioId,
                cantidad,
                notas,
            },
            {
                preserveScroll: true,
                onFinish: () => setProcesandoId(null),
            },
        );
    };

    return (
        <PortalLayout>
            <Head>
                <title>{`Servicios a la Habitación — Reserva #${reserva.codigo_reserva}`}</title>
                <meta
                    name="description"
                    content="Solicita platillos, bebidas, spa y servicios de habitación directamente para tu estancia."
                />
            </Head>

            <div className="mx-auto max-w-5xl space-y-8 p-5 sm:p-8 lg:p-10">
                {/* Header y Retorno */}
                <div className="flex flex-wrap items-center justify-between gap-4 border-b border-border/60 pb-6">
                    <div className="flex items-center gap-3">
                        <Link
                            href={`/portal/reservas/${reserva.id}`}
                            className={buttonVariants({
                                variant: 'ghost',
                                size: 'icon',
                                className: 'rounded-xl',
                            })}
                        >
                            <ArrowLeft className="size-5" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="font-mono text-xs font-bold text-primary">
                                    Reserva #{reserva.codigo_reserva}
                                </span>
                                <span>·</span>
                                <span className="text-xs text-muted-foreground">
                                    {reserva.recurso.nombre}
                                </span>
                            </div>
                            <h1 className="mt-0.5 text-xl font-black text-foreground sm:text-2xl">
                                Servicios & Room Service
                            </h1>
                        </div>
                    </div>
                </div>

                {/* Filtros por Categoría */}
                <div className="flex scrollbar-none items-center gap-2 overflow-x-auto pb-2">
                    {categorias.map((cat) => (
                        <button
                            key={cat}
                            type="button"
                            onClick={() => setCategoriaSeleccionada(cat)}
                            className={`rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap capitalize transition-all ${
                                categoriaSeleccionada === cat
                                    ? 'bg-primary text-white shadow-sm'
                                    : 'bg-secondary/60 text-muted-foreground hover:bg-secondary hover:text-foreground'
                            }`}
                        >
                            {cat === 'todos' ? 'Todos los Servicios' : cat}
                        </button>
                    ))}
                </div>

                {/* Grilla de Servicios */}
                {serviciosFiltrados.length > 0 ? (
                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        {serviciosFiltrados.map((servicio) => (
                            <CatalogoServicioItem
                                key={servicio.id}
                                servicio={servicio}
                                onSolicitar={handleSolicitarServicio}
                                isSubmitting={procesandoId === servicio.id}
                            />
                        ))}
                    </div>
                ) : (
                    <div className="rounded-3xl border border-dashed border-border/80 bg-secondary/20 p-12 text-center">
                        <UtensilsCrossed className="mx-auto size-12 text-muted-foreground/60" />
                        <h4 className="mt-3 text-base font-bold text-foreground">
                            No hay servicios en esta categoría
                        </h4>
                    </div>
                )}
            </div>
        </PortalLayout>
    );
};

SolicitarServicios.layout = (page: React.ReactNode) => page;

export default SolicitarServicios;
