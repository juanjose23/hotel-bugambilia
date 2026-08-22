<?php

declare(strict_types=1);

return [
    'name' => env('HOTEL_NAME', 'Hotel Bugambilias'),
    'telefono' => env('HOTEL_TELEFONO', '+505 8713 6805'),
    'email' => env('HOTEL_EMAIL', 'recepcion@bugambiliashotel.com'),
    'direccion' => env('HOTEL_DIRECCION', 'Salida Sur Estelí, Restaurante Absoluto 1c. Oeste, 2c. Sur, 1c. Oeste'),
    'logo' => env('HOTEL_LOGO', 'images/logo-horizontal.png'),
    'icon' => env('HOTEL_ICON', 'images/hotel-icon.png'),
    'reservas' => [
        'recordatorio_habilitado' => env('RESERVAS_RECORDATORIO_HABILITADO', true),
        'anticipacion_minutos' => env('RESERVAS_RECORDATORIO_MINUTOS', 30),
        'tolerancia_minutos' => env('RESERVAS_RECORDATORIO_TOLERANCIA', 5),
        'permisos_generales' => ['page_CalendarioReservas', 'ViewAny:Reserva'],
        'permisos_mesas' => ['page_GestionMesas'],
    ],
];
