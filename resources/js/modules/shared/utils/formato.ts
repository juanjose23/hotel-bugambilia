export const formatearNumero = (valor: number): string => {
    return new Intl.NumberFormat('es-NI', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(valor);
};
