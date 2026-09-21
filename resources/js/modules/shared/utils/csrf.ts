/**
 * Obtiene el token CSRF inyectado en el meta tag del documento HTML por Laravel.
 */
export const obtenerCsrfToken = (): string => {
    return (
        (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)
            ?.content || ''
    );
};

export default obtenerCsrfToken;
