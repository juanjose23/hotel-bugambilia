export const OPCIONES_TIPO_PERSONA = [
    { value: 'natural', label: 'Persona Natural' },
    { value: 'juridica', label: 'Persona Jurídica (Empresa)' },
] as const;

export const OPCIONES_TIPO_IDENTIFICACION = [
    { value: 'cedula', label: 'Cédula de Identidad' },
    { value: 'ruc', label: 'RUC' },
    { value: 'pasaporte', label: 'Pasaporte' },
] as const;
