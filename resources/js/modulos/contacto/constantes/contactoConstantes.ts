export const OPCIONES_ASUNTO_CONTACTO = [
    { value: 'reserva', label: 'Reservaciones de Habitaciones' },
    { value: 'servicios', label: 'Servicios & Gastronomía' },
    { value: 'eventos', label: 'Eventos, Bodas & Conferencias' },
    { value: 'transporte', label: 'Transporte Privado' },
    { value: 'otro', label: 'Otro Requerimiento' },
] as const;

export const PREGUNTAS_FRECUENTES_DEFECTO = [
    {
        question: '¿Cuáles son los horarios de entrada y salida?',
        answer: 'La entrada es a partir de las 14:00 horas y la salida hasta las 12:00 horas. Ofrecemos servicio de entrada temprana y salida tardía sujeto a disponibilidad.',
    },
    {
        question: '¿Ofrecen transporte desde el aeropuerto?',
        answer: 'Sí, ofrecemos servicio de transporte desde el aeropuerto de Managua. El costo es de $45 USD por trayecto y debe reservarse con al menos 24 horas de anticipación.',
    },
    {
        question: '¿Aceptan mascotas?',
        answer: 'Aceptamos mascotas pequeñas en habitaciones seleccionadas con un cargo adicional de $15 USD por noche. Es necesario notificar al momento de la reserva.',
    },
    {
        question: '¿Qué métodos de pago aceptan?',
        answer: 'Aceptamos efectivo (córdobas y dólares), tarjetas de crédito (Visa, MasterCard, American Express) y transferencias bancarias.',
    },
    {
        question: '¿Tienen Wi-Fi gratuito?',
        answer: 'Sí, ofrecemos Wi-Fi gratuito de alta velocidad en todas las áreas del hotel, incluyendo habitaciones, lobby, restaurante y áreas comunes.',
    },
    {
        question: '¿Cuál es la política de cancelación?',
        answer: 'Las cancelaciones son gratuitas hasta 24 horas antes de la fecha de llegada. Para cancelaciones tardías se cobra el equivalente a una noche.',
    },
    {
        question: '¿Ofrecen tours por la región?',
        answer: 'Sí, organizamos tours por los principales atractivos de Estelí incluyendo fincas de café, reservas naturales y sitios históricos. Consulta en recepción.',
    },
    {
        question: '¿El hotel es accesible para personas con discapacidad?',
        answer: 'Contamos con habitaciones y áreas comunes adaptadas para personas con movilidad reducida, incluyendo rampas y baños accesibles.',
    },
];
