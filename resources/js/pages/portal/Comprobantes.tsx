import { Head } from '@inertiajs/react';
import { PortalLayout } from '@/modules/clientes/components/layouts/PortalLayout';
import { ComprobantesCuenta } from '@/modules/clientes/components/reservas/ComprobantesCuenta';
import type {
    CuentaComprobantes,
    BancoComprobante,
    ComprobanteCliente,
} from '@/modules/clientes/components/reservas/ComprobantesCuenta';
export default function Comprobantes(props: {
    cuenta: CuentaComprobantes;
    bancos: BancoComprobante[];
    comprobantes: ComprobanteCliente[];
}) {
    return (
        <PortalLayout>
            <Head title="Comprobantes de transferencia" />
            <ComprobantesCuenta {...props} />
        </PortalLayout>
    );
}
