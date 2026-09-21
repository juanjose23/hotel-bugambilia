<?php

declare(strict_types=1);

namespace Database\Seeders\Espacios;

use App\Repository\Models\Politicas\Politica;
use Illuminate\Database\Seeder;

class EspacioPoliticaSeeder extends Seeder
{
    public function run(): void
    {
        $politicas = [
            [
                'titulo' => 'Política de Cancelación de Espacios Comerciales y Salones',
                'descripcion' => 'Cancelación sin penalización hasta 48 horas antes del evento. Cancelaciones dentro de las 48 horas previas incurrirán en el cobro del 50% del precio base de reserva.',
                'aplica_penalizacion' => true,
                'estado' => 1,
                'penalizaciones' => [
                    ['min_unidades' => 2, 'max_unidades' => null, 'unidad' => 1, 'porcentaje' => 0.00, 'aplica_no_show' => false, 'orden' => 1],
                    ['min_unidades' => 0, 'max_unidades' => 1, 'unidad' => 1, 'porcentaje' => 50.00, 'aplica_no_show' => false, 'orden' => 2],
                    ['min_unidades' => null, 'max_unidades' => null, 'unidad' => 1, 'porcentaje' => 100.00, 'aplica_no_show' => true, 'orden' => 3],
                ],
            ],
            [
                'titulo' => 'Reglamento Interno del Gimnasio & Fitness Center',
                'descripcion' => 'Horario de uso de 06:00 a 22:00 horas. Calzado deportivo cerrado, toalla personal y vestimenta de entrenamiento obligatorios. Prohibido el ingreso a menores de 14 años sin supervisión de un adulto.',
                'aplica_penalizacion' => false,
                'estado' => 1,
                'penalizaciones' => [],
            ],
            [
                'titulo' => 'Reglamento de Uso de Piscina Infinity & Áreas Húmedas',
                'descripcion' => 'Horario de 07:00 a 21:00 horas. Ducha obligatoria previa al ingreso. Prohibido envases de vidrio en la zona perimetral de agua. Menores de 12 años deben estar acompañados por un adulto responsable.',
                'aplica_penalizacion' => false,
                'estado' => 1,
                'penalizaciones' => [],
            ],
            [
                'titulo' => 'Normativa de Convivencia en Terrazas y Zonas al Aire Libre',
                'descripcion' => 'Se permite el consumo de alimentos y bebidas del restaurante y bar del hotel. Música moderada respetando el horario de descanso a partir de las 22:00 horas.',
                'aplica_penalizacion' => false,
                'estado' => 1,
                'penalizaciones' => [],
            ],
            [
                'titulo' => 'Políticas de Cabinas de Masajes y Tratamientos Spa',
                'descripcion' => 'Se requiere presentarse 10 minutos antes de la hora acordada. En caso de retraso superior a 15 minutos, la sesión se adecuará al tiempo restante para no afectar citas posteriores.',
                'aplica_penalizacion' => false,
                'estado' => 1,
                'penalizaciones' => [],
            ],
        ];

        foreach ($politicas as $pData) {
            $penalizaciones = $pData['penalizaciones'];
            unset($pData['penalizaciones']);

            /** @var Politica $politica */
            $politica = Politica::query()->updateOrCreate(
                ['titulo' => $pData['titulo']],
                $pData
            );

            if (! empty($penalizaciones)) {
                $politica->penalizaciones()->delete();
                $politica->penalizaciones()->createMany($penalizaciones);
            }
        }
    }
}
