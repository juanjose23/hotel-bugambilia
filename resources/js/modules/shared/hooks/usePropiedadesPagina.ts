import type { PageProps } from '@inertiajs/core';
import { usePage } from '@inertiajs/react';

export interface UsuarioAuth {
    id: number;
    name: string;
    email: string;
    is_admin?: boolean;
    persona?: {
        id: number;
        nombre_completo: string;
        telefono?: string;
    } | null;
}

export interface HotelInfoDomain {
    name?: string;
    nombre?: string;
    slogan?: string;
    telefono?: string;
    whatsapp?: string;
    email?: string;
    email_reservaciones?: string;
    direccion?: string;
    direccion_corta?: string;
    checkin?: string;
    checkout?: string;
    fundado?: number | string;
    icon?: string;
    logo?: string;
}

export interface FlashMessages {
    exito?: string | null;
    success?: string | null;
    error?: string | null;
    warning?: string | null;
    info?: string | null;
}

export interface PropiedadesCompartidasInertia extends PageProps {
    name?: string;
    sidebarOpen?: boolean;
    auth?: {
        user?: UsuarioAuth | null;
    };
    hotel?: HotelInfoDomain;
    flash?: FlashMessages;
    [key: string]: unknown;
}

export function usePropiedadesPagina<
    T extends Record<string, unknown> = Record<string, unknown>,
>() {
    const { props } = usePage<PropiedadesCompartidasInertia & T>();

    return props;
}

export default usePropiedadesPagina;
