<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\Turno;
use Illuminate\Database\Seeder;

class LimpiezaTurnoSeeder extends Seeder
{
    public function run(): void
    {
        $juan = Colaborador::where('codigo', 'COL-0001')->first()
            ?? Colaborador::factory()->create(['codigo' => 'COL-0001']);

        $mariana = Colaborador::where('codigo', 'COL-0002')->first()
            ?? Colaborador::factory()->create(['codigo' => 'COL-0002']);

        $carlos = Colaborador::where('codigo', 'COL-0003')->first()
            ?? Colaborador::factory()->create(['codigo' => 'COL-0003']);

        $carritoA = Ubicacion::firstOrCreate(
            ['nombre' => 'Carrito Limpieza A'],
            ['tipo' => 'carrito', 'descripcion' => 'Carrito móvil de limpieza para Planta Baja', 'orden' => 10, 'estado' => 1],
        );

        $carritoB = Ubicacion::firstOrCreate(
            ['nombre' => 'Carrito Limpieza B'],
            ['tipo' => 'carrito', 'descripcion' => 'Carrito móvil de limpieza para Planta Alta', 'orden' => 11, 'estado' => 1],
        );

        Ubicacion::firstOrCreate(
            ['nombre' => 'Zona Recepción y Lobby'],
            ['tipo' => 'zona', 'orden' => 20, 'estado' => 1],
        );

        Ubicacion::firstOrCreate(
            ['nombre' => 'Zona Alberca y Jardines'],
            ['tipo' => 'zona', 'orden' => 21, 'estado' => 1],
        );

        $turnoManana = Turno::firstOrCreate(
            ['nombre' => 'Turno Matutino A'],
            [
                'lider_id' => $juan->id,
                'apoyo_id' => $mariana->id,
                'hora_inicio' => '07:00:00',
                'hora_fin' => '15:00:00',
                'estado' => true,
            ]
        );
        $turnoManana->carritos()->sync([$carritoA->id]);

        $turnoTarde = Turno::firstOrCreate(
            ['nombre' => 'Turno Vespertino B'],
            [
                'lider_id' => $carlos->id,
                'apoyo_id' => $juan->id,
                'hora_inicio' => '15:00:00',
                'hora_fin' => '23:00:00',
                'estado' => true,
            ]
        );
        $turnoTarde->carritos()->sync([$carritoB->id]);

        $checklistBase = [
            'Tender camas y cambiar sábanas',
            'Sacudir polvo de superficies y mobiliario',
            'Limpiar y desinfectar el cuarto de baño',
            'Barrer y trapear los pisos',
            'Reponer toallas limpias',
            'Reponer amenidades (jabón, shampoo, café)',
            'Vaciar papeleras y colocar bolsas nuevas',
        ];

        $habitaciones = Habitacion::all();
        foreach ($habitaciones as $index => $habitacion) {
            $turno = ($index % 2 === 0) ? $turnoManana : $turnoTarde;
            $hora = sprintf('%02d:00:00', 8 + ($index % 8));

            $horario = LimpiezaHorario::firstOrCreate(
                ['turno_id' => $turno->id, 'hora_estimada' => $hora, 'frecuencia' => 'diaria'],
                ['checklist' => $checklistBase, 'duracion_estimada_minutos' => 30, 'activo' => true],
            );

            $horario->detalles()->firstOrCreate([
                'limpiable_type' => Habitacion::class,
                'limpiable_id' => $habitacion->id,
            ]);
        }

        $diasSemana = ['lunes', 'miercoles', 'viernes', 'sabado'];
        $espacios = Espacio::all();
        foreach ($espacios as $index => $espacio) {
            $turno = ($index % 2 === 0) ? $turnoManana : $turnoTarde;
            $dia = $diasSemana[$index % count($diasSemana)];
            $hora = sprintf('%02d:30:00', 9 + ($index % 6));

            $horario = LimpiezaHorario::firstOrCreate(
                ['turno_id' => $turno->id, 'hora_estimada' => $hora, 'frecuencia' => 'semanal', 'dia_semana' => $dia],
                ['checklist' => $checklistBase, 'duracion_estimada_minutos' => 45, 'activo' => true],
            );

            $horario->detalles()->firstOrCreate([
                'limpiable_type' => Espacio::class,
                'limpiable_id' => $espacio->id,
            ]);
        }
    }
}
