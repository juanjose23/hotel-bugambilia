export type Usuario = {
    id: number;
    name: string;
    email: string;
    persona?: {
        id: number;
        nombre_completo: string | null;
        telefono: string | null;
    } | null;
    avatar?: string;
    is_admin?: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};
export type Autenticacion = {
    user: Usuario | null;
};
