<?php

declare(strict_types=1);

namespace Database\Seeders\Servicios;

use App\Repository\Models\Politicas\Politica;
use Illuminate\Database\Seeder;

class ServicioPoliticaSeeder extends Seeder
{
    public function run(): void
    {
        $politicas = [
            [
                'titulo' => 'Política de Cancelación y Reprogramación de Spa',
                'descripcion' => 'Las cancelaciones de sesiones de spa y masajes deben realizarse con al menos 4 horas de anticipación. Cancelaciones tardías o no presentación (no-show) incurrirán en un cargo del 50% del valor del servicio.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de Traslados y Transporte',
                'descripcion' => 'Los servicios de traslado al aeropuerto requieren notificación del itinerario de vuelo con al menos 24 horas de antelación. Las esperas en el aeropuerto superiores a 45 minutos después del aterrizaje podrán generar cargos adicionales.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de Lavandería y Cuidado de Prendas',
                'descripcion' => 'El hotel no se hace responsable por prendas que requieran lavado en seco salvo que se especifique previamente. Cualquier daño imputable al proceso de lavado será indemnizado según el valor estimado con un tope de hasta 5 veces el costo del servicio de lavado.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de Salones y Equipos Audiovisuales',
                'descripcion' => 'El cliente es responsable del uso adecuado de las instalaciones, proyectores y equipos de sonido. Cualquier daño a los dispositivos tecnológicos será cargado a la cuenta del huésped u organizador del evento.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de Experiencias VIP y Decoraciones',
                'descripcion' => 'Los paquetes de decoración romántica y cenas privadas deben solicitarse con un mínimo de 12 horas de antelación para garantizar la frescura de las flores, ingredientes de repostería y vinos seleccionados.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de No Fumar y Convivencia',
                'descripcion' => 'Está terminantemente prohibido fumar en áreas cerradas del spa, salones ejecutivos y habitaciones durante la prestación de servicios.',
                'estado' => 1,
            ],
        ];

        foreach ($politicas as $pol) {
            Politica::updateOrCreate(
                ['titulo' => $pol['titulo']],
                [
                    'descripcion' => $pol['descripcion'],
                    'estado' => $pol['estado'],
                ]
            );
        }
    }
}
