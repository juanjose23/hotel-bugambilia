import type { Autenticacion } from '@/modulos/compartido/interfaces/auth';
declare module '@inertiajs/core' {
    export interface PageProps {
        name: string;
        auth: Autenticacion;
        sidebarOpen: boolean;
        hotel: {
            name: string;
            slogan: string;
            telefono: string;
            whatsapp: string;
            email: string;
            email_reservaciones: string;
            direccion: string;
            direccion_corta: string;
            checkin: string;
            checkout: string;
            fundado: number;
            icon: string;
            logo: string;
        };
        flash?: {
            exito?: string;
            warning?: string;
            info?: string;
            error?: string;
            success?: string;
        };
        [key: string]: unknown;
    }
}
