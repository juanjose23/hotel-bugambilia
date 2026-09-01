import { Sparkles, Gift, Tag, Clock, ArrowUpCircle } from 'lucide-react';
import type { BeneficioClienteItem } from '../types';

interface BeneficiosClienteBadgeProps {
    beneficio: BeneficioClienteItem;
    className?: string;
}

const renderIcono = (tipo?: string) => {
    switch (tipo) {
        case 'descuento_reserva':
        case 'descuento_restaurante':
            return <Tag className="size-3.5 shrink-0" />;
        case 'anticipo_reducido':
            return <Sparkles className="size-3.5 shrink-0" />;
        case 'late_checkout':
            return <Clock className="size-3.5 shrink-0" />;
        case 'upgrade_habitacion':
            return <ArrowUpCircle className="size-3.5 shrink-0" />;
        case 'cortesia':
        default:
            return <Gift className="size-3.5 shrink-0" />;
    }
};

export const BeneficiosClienteBadge = ({
    beneficio,
    className = '',
}: BeneficiosClienteBadgeProps) => {
    const textoBeneficio = () => {
        if (beneficio.nombre) {
            return beneficio.nombre;
        }

        if (beneficio.tipo === 'descuento_reserva') {
            return beneficio.es_porcentaje
                ? `${beneficio.valor}% Descuento VIP`
                : `$${beneficio.valor} Descuento VIP`;
        }

        if (beneficio.tipo === 'anticipo_reducido') {
            return 'Abono Flexible / Sin Pago Previo';
        }

        if (beneficio.tipo === 'late_checkout') {
            return 'Late Check-out de Cortesía';
        }

        if (beneficio.tipo === 'upgrade_habitacion') {
            return 'Upgrade de Suite Garantizado';
        }

        return 'Beneficio Exclusivo';
    };

    return (
        <div
            className={`inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-black text-amber-700 dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-300 ${className}`}
        >
            {renderIcono(beneficio.tipo)}
            <span>{textoBeneficio()}</span>
        </div>
    );
};

export default BeneficiosClienteBadge;
