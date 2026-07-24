export type MetodoPago = 'tarjeta' | 'paypal';

export interface DatosTarjetaPago {
    titular: string;
    numero: string;
    expiracion: string;
    codigoSeguridad: string;
}
export interface DatosContactoPago {
    nombre: string;
    apellido: string;
    correo: string;
    telefono: string;
    peticiones: string;
}
