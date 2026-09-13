<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Delivery de Restaurante
    |--------------------------------------------------------------------------
    |
    | Define los departamentos, municipios y zonas permitidas para entrega a
    | domicilio, costo de envío base y pedido mínimo.
    |
    */
    'delivery' => [
        'habilitado' => (bool) env('RESTAURANTE_DELIVERY_HABILITADO', true),
        'costo_base' => (float) env('RESTAURANTE_DELIVERY_COSTO', 50.0),
        'pedido_minimo' => (float) env('RESTAURANTE_DELIVERY_MINIMO', 0.0),
        'departamentos_permitidos' => [
            [
                'codigo' => 'EST',
                'nombre' => 'Estelí',
                'activo' => true,
                'costo_envio' => 50.0,
                'municipios' => [
                    'Estelí',
                    'La Trinidad',
                    'Condega',
                    'Pueblo Nuevo',
                    'San Juan de Limay',
                    'San Nicolás',
                ],
            ],
            [
                'codigo' => 'MAD',
                'nombre' => 'Madriz',
                'activo' => false,
                'costo_envio' => 120.0,
                'municipios' => [
                    'Somoto',
                    'Palacagüina',
                    'Yalagüina',
                    'Totogalpa',
                ],
            ],
            [
                'codigo' => 'NVA',
                'nombre' => 'Nueva Segovia',
                'activo' => false,
                'costo_envio' => 150.0,
                'municipios' => [
                    'Ocotal',
                    'Dipilto',
                    'Mozonte',
                ],
            ],
            [
                'codigo' => 'MGA',
                'nombre' => 'Managua',
                'activo' => false,
                'costo_envio' => 250.0,
                'municipios' => [
                    'Managua',
                    'Ciudad Sandino',
                    'Tipitapa',
                ],
            ],
        ],
    ],
];
