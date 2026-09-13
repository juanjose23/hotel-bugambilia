import { Head, Link } from '@inertiajs/react';
import { UtensilsCrossed, ChefHat, Plus, ShoppingBag } from 'lucide-react';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import { PedidoActivoCard } from '@/modules/clientes/components/pedidos/PedidoActivoCard';
import { PedidoHistorialCard } from '@/modules/clientes/components/pedidos/PedidoHistorialCard';
import type {
    ClienteProfile,
    PortalPedidoResumen,
    EstadisticasHuesped,
} from '@/modules/clientes/types';
import { Button } from '@/modules/shared/components/ui/button';

interface MisPedidosProps {
    cliente: ClienteProfile;
    pedidos_activos: PortalPedidoResumen[];
    historial_pedidos: PortalPedidoResumen[];
    estadisticas: EstadisticasHuesped;
}

export const MisPedidos = ({
    cliente,
    pedidos_activos = [],
    historial_pedidos = [],
}: MisPedidosProps) => {
    const todosLosPedidos = [...pedidos_activos, ...historial_pedidos];

    return (
        <PortalLayout cliente={cliente}>
            <Head>
                <title>Mis Pedidos de Restaurante — Hotel Bugambilias</title>
                <meta
                    name="description"
                    content="Historial y seguimiento de pedidos y comandas de restaurante en Hotel Bugambilias."
                />
            </Head>

            <div className="mx-auto max-w-6xl space-y-8 p-5 sm:p-8 lg:p-10">
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="text-xs font-black tracking-wider text-primary uppercase">
                                Restaurante & Delivery
                            </span>
                            <span>·</span>
                            <span className="text-xs text-muted-foreground">
                                Portal de Huésped
                            </span>
                        </div>
                        <h1 className="mt-1 text-2xl font-black text-foreground sm:text-3xl">
                            Mis Pedidos y Comandas
                        </h1>
                        <p className="mt-0.5 text-sm text-muted-foreground">
                            Seguimiento de pedidos en cocina, entregas a
                            domicilio y órdenes anteriores.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Link href="/restaurante">
                            <Button className="h-10 cursor-pointer gap-2 rounded-2xl px-5 text-xs font-bold shadow-sm">
                                <Plus className="size-4" />
                                <span>Nuevo Pedido</span>
                            </Button>
                        </Link>
                    </div>
                </div>

                {todosLosPedidos.length === 0 ? (
                    <div className="rounded-3xl border border-dashed border-border/80 bg-secondary/20 p-12 text-center">
                        <UtensilsCrossed className="mx-auto size-14 text-muted-foreground/60" />
                        <h3 className="mt-4 text-lg font-bold text-foreground">
                            Aún no has realizado pedidos en el restaurante
                        </h3>
                        <p className="mx-auto mt-1 max-w-md text-xs text-muted-foreground">
                            Explora nuestro menú con platillos gourmet, cortes
                            selectos y comida típica con entrega a domicilio en
                            Estelí.
                        </p>
                        <div className="mt-6">
                            <Link href="/restaurante">
                                <Button className="h-10 cursor-pointer gap-2 rounded-2xl px-6 text-xs font-bold">
                                    <ShoppingBag className="size-4" />
                                    <span>Ver Menú del Restaurante</span>
                                </Button>
                            </Link>
                        </div>
                    </div>
                ) : (
                    <div className="space-y-6">
                        {pedidos_activos.length > 0 && (
                            <div className="space-y-4">
                                <h3 className="flex items-center gap-2 text-base font-black text-foreground">
                                    <ChefHat className="size-5 text-primary" />
                                    <span>
                                        Comandas en Preparación (
                                        {pedidos_activos.length})
                                    </span>
                                </h3>

                                <div className="grid grid-cols-1 gap-4">
                                    {pedidos_activos.map((pedido) => (
                                        <PedidoActivoCard
                                            key={pedido.id}
                                            pedido={pedido}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}

                        {historial_pedidos.length > 0 && (
                            <div className="space-y-4 pt-4">
                                <h3 className="text-base font-black text-foreground">
                                    Historial de Órdenes Anteriores (
                                    {historial_pedidos.length})
                                </h3>

                                <div className="grid grid-cols-1 gap-3">
                                    {historial_pedidos.map((pedido) => (
                                        <PedidoHistorialCard
                                            key={pedido.id}
                                            pedido={pedido}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </PortalLayout>
    );
};

export default MisPedidos;
