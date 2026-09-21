<?php

declare(strict_types=1);

namespace Database\Seeders\Habitaciones;

use App\Repository\Models\Politicas\Politica;
use Illuminate\Database\Seeder;

class HabitacionPoliticaSeeder extends Seeder
{
    public function run(): void
    {
        $politicas = [
            [
                'titulo' => 'Política de Cancelación',
                'descripcion' => 'Cancelación gratuita hasta 24 horas antes de la fecha de llegada. En caso de no presentarse o cancelar fuera de este plazo, se penalizará con el cobro de la primera noche de estancia.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de No Fumar',
                'descripcion' => 'Todas nuestras habitaciones y áreas cerradas son 100% libres de humo de tabaco o cigarrillos electrónicos. Se aplicará una penalización de $100 USD para cubrir costos de limpieza profunda y desodorización si se detecta humo.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de Mascotas',
                'descripcion' => 'No se admiten mascotas en habitaciones estándar ni suites ejecutivas para garantizar el descanso de todos los huéspedes, excepto en las Cabañas Familiares pet-friendly designadas o animales de asistencia médica certificados.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de Check-in y Check-out',
                'descripcion' => 'La hora de entrada (Check-in) es a partir de las 15:00 horas. La hora de salida (Check-out) es a las 11:00 horas. Sujeto a disponibilidad se puede solicitar Early Check-in o Late Check-out con tarifa adicional.',
                'estado' => 1,
            ],
            [
                'titulo' => 'Política de Horas de Silencio y Convivencia',
                'descripcion' => 'Para garantizar el descanso reparador de todos los huéspedes, se solicita mantener el volumen moderado y respetar las horas de silencio entre las 22:00 y las 07:00 horas en pasillos y habitaciones.',
                'estado' => 1,
            ],
        ];

        foreach ($politicas as $pData) {
            Politica::updateOrCreate(
                ['titulo' => $pData['titulo']],
                [
                    'descripcion' => $pData['descripcion'],
                    'estado' => $pData['estado'],
                ]
            );
        }
    }
}
