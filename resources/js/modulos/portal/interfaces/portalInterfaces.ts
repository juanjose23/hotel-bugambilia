import type { ReservaClienteDomain } from '@/modulos/clientes/interfaces/cliente';

export interface PropiedadesPortalMisReservas {
    reservas?: ReservaClienteDomain[];
    hotel?: {
        name?: string;
    };
    codigoBusqueda?: string;
}

export type TabPortal = 'overview' | 'activas' | 'historial' | 'servicios';
