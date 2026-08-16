export type MetodoPago = 'tarjeta';

export interface DatosContactoPago {
    nombre: string;
    apellido: string;
    correo: string;
    telefono: string;
    peticiones: string;
}

export interface ConfiguracionStripePago {
    reservaId?: number | string;
    clientSecret: string;
    publishableKey: string;
    monto: number;
    moneda: string;
}

export type StripeElementsInstance = {
    create: (
        tipo: 'payment',
        opciones?: { layout?: 'tabs' | 'accordion' },
    ) => { mount: (selector: string) => void };
    submit: () => Promise<{ error?: { message?: string } }>;
};

export type StripeInstance = {
    elements: (opciones: { clientSecret: string }) => StripeElementsInstance;
    confirmPayment: (opciones: {
        elements: StripeElementsInstance;
        confirmParams: { return_url: string };
        redirect: 'if_required';
    }) => Promise<{
        error?: { message?: string };
        paymentIntent?: { id: string; status?: string };
    }>;
};

declare global {
    interface Window {
        Stripe?: (publishableKey: string) => StripeInstance;
    }
}
