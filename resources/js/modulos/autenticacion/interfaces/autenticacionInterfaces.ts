export type TipoPersona = 'natural' | 'juridica';
export type TipoIdentificacion = 'cedula' | 'ruc' | 'pasaporte';

export interface PropiedadesEncabezadoAuth {
    badge?: string;
    titulo: string;
    subtituloEnfasis?: string;
    descripcion: string;
}

export interface PropiedadesSelectorTipoPersona {
    tipoPersona: TipoPersona;
    onSeleccionar: (tipo: TipoPersona) => void;
}

export interface PropiedadesFormularioCambiarContrasena {
    token?: string;
    email?: string;
}
