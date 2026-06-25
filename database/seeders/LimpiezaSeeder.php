<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Ubicacion;
use App\Models\Colaboradores\Colaborador;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Limpieza\LimpiezaHorario;
use App\Models\Limpieza\Turno;
use App\Models\Shared\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LimpiezaSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = Carbon::now();

        // 1. Obtener colaboradores base
        $juan = Colaborador::where('codigo', 'COL-0001')->first();
        $mariana = Colaborador::where('codigo', 'COL-0002')->first();
        $carlos = Colaborador::where('codigo', 'COL-0003')->first();

        // Si no existen colaboradores base (por si acaso), creamos unos ficticios
        if (! $juan) {
            $juan = Colaborador::factory()->create(['codigo' => 'COL-0001']);
        }
        if (! $mariana) {
            $mariana = Colaborador::factory()->create(['codigo' => 'COL-0002']);
        }
        if (! $carlos) {
            $carlos = Colaborador::factory()->create(['codigo' => 'COL-0003']);
        }

        // 2. Crear Carritos / Bodegas de Limpieza
        $carritoA = Ubicacion::firstOrCreate(
            ['nombre' => 'Carrito Limpieza A'],
            [
                'tipo' => 'almacen',
                'descripcion' => 'Carrito móvil de limpieza para Planta Baja',
                'orden' => 10,
                'estado' => 1,
            ]
        );

        $carritoB = Ubicacion::firstOrCreate(
            ['nombre' => 'Carrito Limpieza B'],
            [
                'tipo' => 'almacen',
                'descripcion' => 'Carrito móvil de limpieza para Planta Alta',
                'orden' => 11,
                'estado' => 1,
            ]
        );

        // 3. Crear Zonas Físicas de Limpieza
        $zonaLobby = Ubicacion::firstOrCreate(
            ['nombre' => 'Zona Recepción y Lobby'],
            [
                'tipo' => 'zona',
                'orden' => 20,
                'estado' => 1,
            ]
        );

        $zonaAlberca = Ubicacion::firstOrCreate(
            ['nombre' => 'Zona Alberca y Jardines'],
            [
                'tipo' => 'zona',
                'orden' => 21,
                'estado' => 1,
            ]
        );

        // 4. Crear Turnos / Bloques de Horarios
        $turnoMañana = Turno::firstOrCreate(
            ['nombre' => 'Turno Matutino A'],
            [
                'lider_id' => $juan->id,
                'apoyo_id' => $mariana->id,
                'carritos_ids' => [$carritoA->id],
                'hora_inicio' => '07:00:00',
                'hora_fin' => '15:00:00',
                'estado' => true,
            ]
        );

        $turnoTarde = Turno::firstOrCreate(
            ['nombre' => 'Turno Vespertino B'],
            [
                'lider_id' => $carlos->id,
                'apoyo_id' => $juan->id,
                'carritos_ids' => [$carritoB->id],
                'hora_inicio' => '15:00:00',
                'hora_fin' => '23:00:00',
                'estado' => true,
            ]
        );

        // 5. Crear Planificación de Horarios (limp_horarios)
        $habitaciones = Habitacion::limit(5)->get();
        $espacios = Espacio::limit(3)->get();

        // Checklist base para sementar ejecuciones
        $checklistBase = [
            'Tender camas y cambiar sábanas',
            'Sacudir polvo de superficies y mobiliario',
            'Limpiar y desinfectar el cuarto de baño',
            'Barrer y trapear los pisos',
            'Reponer toallas limpias',
            'Reponer amenidades (jabón, shampoo, café)',
            'Vaciar papeleras y colocar bolsas nuevas',
        ];

        // Planificar limpieza de Habitaciones (Diaria)
        foreach ($habitaciones as $index => $habitacion) {
            $turno = ($index % 2 === 0) ? $turnoMañana : $turnoTarde;
            $horaEstimada = sprintf('%02d:00:00', 8 + $index);

            $horario = LimpiezaHorario::firstOrCreate(
                [
                    'turno_id' => $turno->id,
                    'hora_estimada' => $horaEstimada,
                    'frecuencia' => 'diaria',
                ],
                [
                    'checklist' => $checklistBase,
                    'activo' => true,
                ]
            );

            $horario->detalles()->firstOrCreate([
                'limpiable_type' => Habitacion::class,
                'limpiable_id' => $habitacion->id,
            ]);
        }

        // Planificar limpieza de Espacios (Semanal)
        $diasSemana = ['lunes', 'miercoles', 'viernes'];
        foreach ($espacios as $index => $espacio) {
            $turno = ($index % 2 === 0) ? $turnoMañana : $turnoTarde;
            $dia = $diasSemana[$index % count($diasSemana)];

            $horario = LimpiezaHorario::firstOrCreate(
                [
                    'turno_id' => $turno->id,
                    'hora_estimada' => '10:30:00',
                    'frecuencia' => 'semanal',
                    'dia_semana' => $dia,
                ],
                [
                    'checklist' => $checklistBase,
                    'activo' => true,
                ]
            );

            $horario->detalles()->firstOrCreate([
                'limpiable_type' => Espacio::class,
                'limpiable_id' => $espacio->id,
            ]);
        }

        // 6. Crear Ejecuciones de Limpieza de Prueba para Hoy
        $fechaHoy = $ahora->toDateString();

        $horarios = LimpiezaHorario::where('turno_id', $turnoMañana->id)->with('detalles')->get();

        if ($horarios->count() >= 3) {
            // Ejecución 1: Completada
            $h1 = $horarios->get(0);
            $d1 = $h1?->detalles?->first();
            if ($h1 && $d1) {
                LimpiezaEjecucion::firstOrCreate(
                    [
                        'horario_id' => $h1->id,
                        'fecha' => $fechaHoy,
                        'limpiable_type' => $d1->limpiable_type,
                        'limpiable_id' => $d1->limpiable_id,
                    ],
                    [
                        'turno_id' => $turnoMañana->id,
                        'colaborador_id' => $mariana->id,
                        'hora_inicio' => '08:05:00',
                        'hora_fin' => '08:42:00',
                        'estado' => EstadoLimpieza::Completada,
                        'detalles_checklist' => [
                            'Tender camas y cambiar sábanas' => true,
                            'Sacudir polvo de superficies y mobiliario' => true,
                            'Limpiar y desinfectar el cuarto de baño' => true,
                            'Barrer y trapear los pisos' => true,
                            'Reponer toallas limpias' => true,
                            'Reponer amenidades (jabón, shampoo, café)' => true,
                            'Vaciar papeleras y colocar bolsas nuevas' => true,
                        ],
                        'observaciones' => 'Limpieza de rutina realizada con éxito. Sin novedades.',
                    ]
                );
            }

            // Ejecución 2: En Progreso
            $h2 = $horarios->get(1);
            $d2 = $h2?->detalles?->first();
            if ($h2 && $d2) {
                LimpiezaEjecucion::firstOrCreate(
                    [
                        'horario_id' => $h2->id,
                        'fecha' => $fechaHoy,
                        'limpiable_type' => $d2->limpiable_type,
                        'limpiable_id' => $d2->limpiable_id,
                    ],
                    [
                        'turno_id' => $turnoMañana->id,
                        'colaborador_id' => $juan->id,
                        'hora_inicio' => '10:05:00',
                        'hora_fin' => null,
                        'estado' => EstadoLimpieza::EnProgreso,
                    ]
                );
            }

            // Ejecución 3: Pendiente
            $h3 = $horarios->get(2);
            $d3 = $h3?->detalles?->first();
            if ($h3 && $d3) {
                LimpiezaEjecucion::firstOrCreate(
                    [
                        'horario_id' => $h3->id,
                        'fecha' => $fechaHoy,
                        'limpiable_type' => $d3->limpiable_type,
                        'limpiable_id' => $d3->limpiable_id,
                    ],
                    [
                        'turno_id' => $turnoMañana->id,
                        'colaborador_id' => $mariana->id,
                        'hora_inicio' => null,
                        'hora_fin' => null,
                        'estado' => EstadoLimpieza::Pendiente,
                    ]
                );
            }
        }

        // 7. Seed Cart Stocks and Room Stock requirements to test workflow
        $insumos = [
            ['producto_id' => 1,  'variante_id' => 1,  'nombre' => 'Shampoo'],
            ['producto_id' => 2,  'variante_id' => 4,  'nombre' => 'Acondicionador'],
            ['producto_id' => 3,  'variante_id' => 7,  'nombre' => 'Jabón'],
            ['producto_id' => 33, 'variante_id' => 97, 'nombre' => 'Toallas'],
        ];

        // Seed carritos physical stock
        foreach ([$carritoA, $carritoB] as $carrito) {
            foreach ($insumos as $insumo) {
                $stockExists = DB::table('inv_stock')
                    ->where('producto_id', $insumo['producto_id'])
                    ->where('producto_variante_id', $insumo['variante_id'])
                    ->where('ubicacion_id', $carrito->id)
                    ->exists();

                if (! $stockExists) {
                    $loteId = DB::table('inv_lotes')->insertGetId([
                        'codigo_lote' => 'LOTE-CARRITO-'.$carrito->id.'-'.$insumo['variante_id'],
                        'producto_id' => $insumo['producto_id'],
                        'producto_variante_id' => $insumo['variante_id'],
                        'estado' => EstadoLote::Disponible->value,
                        'cantidad_disponible' => 50.0,
                        'cantidad_inicial' => 50.0,
                        'ubicacion_id' => $carrito->id,
                        'fecha_vencimiento' => now()->addMonths(12),
                        'fecha_recepcion' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('inv_stock')->insert([
                        'producto_id' => $insumo['producto_id'],
                        'producto_variante_id' => $insumo['variante_id'],
                        'lote_id' => $loteId,
                        'ubicacion_id' => $carrito->id,
                        'cantidad' => 50.0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Seed room stock requirements (ideal vs actual discrepancies)
        $todasHabitaciones = Habitacion::all();
        foreach ($todasHabitaciones as $habitacion) {
            foreach ($insumos as $insumo) {
                Stock::firstOrCreate(
                    [
                        'stockable_type' => Habitacion::class,
                        'stockable_id' => $habitacion->id,
                        'producto_variante_id' => $insumo['variante_id'],
                    ],
                    [
                        'cantidad_ideal' => 4.0,
                        'cantidad_actual' => 1.0, // Faltan 3 para sugerir abastecimiento!
                    ]
                );
            }
        }
    }
}
