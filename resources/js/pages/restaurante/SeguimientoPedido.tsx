import { Head, Link, router } from '@inertiajs/react';
import { useEffect } from 'react';
import { StripePaymentForm } from '@/modules/reservas/components/StripePaymentForm';
import type { StripePaymentData } from '@/modules/reservas/types';
import restauranteService from '@/modules/restaurante/services/restauranteService';
import { GuiaPasoAPaso } from '@/modules/shared/components/GuiaPasoAPaso';
import { Button } from '@/modules/shared/components/ui/button';

interface PedidoSeguimiento {
    id: number;
    codigo: string;
    estado: number;
    estado_nombre: string;
    total: number;
    pagado: number;
    saldo: number;
    moneda: string;
    items: {
        id: number;
        nombre: string;
        cantidad: number;
        subtotal: number;
        estado: string;
    }[];
    stripe: StripePaymentData | null;
}

export default function SeguimientoPedido({
    pedido,
}: {
    pedido: PedidoSeguimiento;
}) {
    useEffect(() => {
        if ([5, 6, 7].includes(pedido.estado)) {
            return;
        }

        const timer = setInterval(
            () => router.reload({ only: ['pedido'] }),
            10000,
        );

        return () => clearInterval(timer);
    }, [pedido.estado]);

    return (
        <div className="container mx-auto max-w-3xl space-y-6 px-4 py-10">
            <Head title={`Pedido ${pedido.codigo}`} />
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">
                    Tu pedido {pedido.codigo}
                </h1>
                <GuiaPasoAPaso
                    pasos={[
                        {
                            elemento: '[data-guia="pedido-estado"]',
                            titulo: 'Sigue la preparación',
                            descripcion:
                                'El estado se actualiza cada diez segundos. Que el pedido esté preparado no significa que ya esté pagado.',
                        },
                        {
                            elemento: '[data-guia="pedido-cuenta"]',
                            titulo: 'Revisa el cobro',
                            descripcion:
                                'Aquí aparecen el total real, los pagos aplicados y el saldo pendiente. El efectivo se confirma cuando caja lo recibe.',
                        },
                        {
                            elemento: '[data-guia="pedido-pago"]',
                            titulo: 'Pago con tarjeta',
                            descripcion:
                                'Puedes retomar un pago pendiente desde esta pantalla. La cocina recibe la comanda con tarjeta cuando el servidor confirma el pago.',
                        },
                    ]}
                />
            </div>
            <section
                data-guia="pedido-estado"
                className="rounded-2xl border bg-card p-5"
            >
                <p className="text-sm text-muted-foreground">
                    Estado operativo
                </p>
                <p className="text-xl font-bold">{pedido.estado_nombre}</p>
                <p className="mt-2 text-sm text-muted-foreground">
                    Conserva esta página en tu navegador para consultar tu
                    pedido. Si lo creaste con tu cuenta de cliente, también
                    podrás consultarlo al iniciar sesión.
                </p>
            </section>
            <section className="divide-y rounded-2xl border bg-card p-5">
                {pedido.items.map((item) => (
                    <div
                        key={item.id}
                        className="flex justify-between gap-4 py-3"
                    >
                        <div>
                            <p className="font-medium">
                                {item.cantidad} × {item.nombre}
                            </p>
                            <p className="text-xs text-muted-foreground">
                                {item.estado}
                            </p>
                        </div>
                        <span>
                            {pedido.moneda} {item.subtotal.toFixed(2)}
                        </span>
                    </div>
                ))}
            </section>
            <section
                data-guia="pedido-cuenta"
                className="grid grid-cols-3 gap-3 rounded-2xl border p-5"
            >
                {[
                    ['Total', pedido.total],
                    ['Pagado', pedido.pagado],
                    ['Pendiente', pedido.saldo],
                ].map(([label, value]) => (
                    <div key={label}>
                        <p className="text-sm text-muted-foreground">{label}</p>
                        <strong>
                            {pedido.moneda} {Number(value).toFixed(2)}
                        </strong>
                    </div>
                ))}
            </section>
            {pedido.stripe && pedido.estado !== 7 && (
                <section
                    data-guia="pedido-pago"
                    className="rounded-2xl border p-5"
                >
                    <h2 className="mb-4 font-bold">
                        Completar pago con tarjeta
                    </h2>
                    <StripePaymentForm
                        stripeData={pedido.stripe}
                        onSuccess={async (paymentIntentId) => {
                            await restauranteService.confirmarPagoStripePedido({
                                pedido_id: pedido.id,
                                payment_intent_id: paymentIntentId,
                            });
                            router.reload({ only: ['pedido'] });
                        }}
                        onError={() => router.reload({ only: ['pedido'] })}
                    />
                </section>
            )}
            <div className="flex flex-wrap gap-3">
                <Button
                    variant="outline"
                    onClick={() => router.reload({ only: ['pedido'] })}
                >
                    Actualizar estado
                </Button>
                <Link href="/restaurante">
                    <Button>Volver al restaurante</Button>
                </Link>
            </div>
        </div>
    );
}
