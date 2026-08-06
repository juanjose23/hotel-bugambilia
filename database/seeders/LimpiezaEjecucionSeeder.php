<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\LimpiezaHorarioDetalle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LimpiezaEjecucionSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = Carbon::now();
        $fechaHoy = $ahora->toDateString();
        $fechaAyer = $ahora->copy()->subDay()->toDateString();

        /** @var Collection<int, Colaborador> $colaboradores */
        $colaboradores = Colaborador::whereIn('codigo', ['COL-0001', 'COL-0002', 'COL-0003'])->get();

        /** @var Collection<int, Ubicacion> $carritos */
        $carritos = Ubicacion::whereIn('nombre', ['Carrito Limpieza A', 'Carrito Limpieza B'])->get();

        $horarios = LimpiezaHorario::with('detalles')->get();

        // Límite de carritos ocupados por ejecuciones demo EnProgreso:
        // siempre se deja al menos un carrito libre para poder usar "Iniciar Limpieza".
        $carritosEnUso = 0;
        $maxCarritosEnUso = max(0, $carritos->count() - 1);

        $checklistBase = [
            'Tender camas y cambiar sábanas',
            'Sacudir polvo de superficies y mobiliario',
            'Limpiar y desinfectar el cuarto de baño',
            'Barrer y trapear los pisos',
            'Reponer toallas limpias',
            'Reponer amenidades (jabón, shampoo, café)',
            'Vaciar papeleras y colocar bolsas nuevas',
        ];

        foreach ($horarios as $index => $horario) {
            /** @var LimpiezaHorarioDetalle|null $detalle */
            $detalle = $horario->detalles->first();
            if (! $detalle) {
                continue;
            }

            $rand = $index % 10;

            $colab = $colaboradores->get($index % count($colaboradores));
            $carrito = $carritos->get($index % count($carritos));

            if ($rand < 4) {
                $hasDiscrepancy = ($index % 5 === 0);
                $estado = $hasDiscrepancy ? EstadoLimpieza::CompletadaConDiscrepancia : EstadoLimpieza::Completada;

                $formattedChecklist = [];
                foreach ($checklistBase as $taskIdx => $task) {
                    $formattedChecklist[$task] = ! ($hasDiscrepancy && $taskIdx === 0);
                }

                LimpiezaEjecucion::firstOrCreate(
                    [
                        'horario_id' => $horario->id,
                        'fecha' => $fechaHoy,
                        'limpiable_type' => $detalle->limpiable_type,
                        'limpiable_id' => $detalle->limpiable_id,
                    ],
                    [
                        'turno_id' => $horario->turno_id,
                        'colaborador_id' => $colab?->id,
                        'carrito_id' => $carrito?->id,
                        'hora_inicio' => sprintf('%02d:05:00', 8 + ($index % 6)),
                        'hora_fin' => sprintf('%02d:45:00', 8 + ($index % 6)),
                        'estado' => $estado,
                        'detalles_checklist' => $formattedChecklist,
                        'observaciones' => $hasDiscrepancy
                            ? 'Discrepancia en cambio de sábanas. Se reporta daño en tela.'
                            : 'Limpieza de rutina realizada con éxito. Sin novedades.',
                    ]
                );
            } elseif ($rand < 6) {
                $carritoEnProgreso = $carritosEnUso < $maxCarritosEnUso ? $carrito?->id : null;
                if ($carritoEnProgreso !== null) {
                    $carritosEnUso++;
                }

                LimpiezaEjecucion::firstOrCreate(
                    [
                        'horario_id' => $horario->id,
                        'fecha' => $fechaHoy,
                        'limpiable_type' => $detalle->limpiable_type,
                        'limpiable_id' => $detalle->limpiable_id,
                    ],
                    [
                        'turno_id' => $horario->turno_id,
                        'colaborador_id' => $colab?->id,
                        'carrito_id' => $carritoEnProgreso,
                        'hora_inicio' => sprintf('%02d:10:00', 10 + ($index % 4)),
                        'hora_fin' => null,
                        'estado' => EstadoLimpieza::EnProgreso,
                    ]
                );
            } else {
                LimpiezaEjecucion::firstOrCreate(
                    [
                        'horario_id' => $horario->id,
                        'fecha' => $fechaHoy,
                        'limpiable_type' => $detalle->limpiable_type,
                        'limpiable_id' => $detalle->limpiable_id,
                    ],
                    [
                        'turno_id' => $horario->turno_id,
                        'colaborador_id' => null,
                        'carrito_id' => null,
                        'hora_inicio' => null,
                        'hora_fin' => null,
                        'estado' => EstadoLimpieza::Pendiente,
                    ]
                );
            }

            if ($index % 7 === 0) {
                LimpiezaEjecucion::firstOrCreate(
                    [
                        'horario_id' => $horario->id,
                        'fecha' => $fechaAyer,
                        'limpiable_type' => $detalle->limpiable_type,
                        'limpiable_id' => $detalle->limpiable_id,
                    ],
                    [
                        'turno_id' => $horario->turno_id,
                        'colaborador_id' => null,
                        'carrito_id' => null,
                        'hora_inicio' => null,
                        'hora_fin' => null,
                        'estado' => EstadoLimpieza::Pendiente,
                    ]
                );

                LimpiezaEjecucion::firstOrCreate(
                    [
                        'horario_id' => $horario->id,
                        'fecha' => $fechaAyer,
                        'limpiable_type' => $detalle->limpiable_type,
                        'limpiable_id' => $detalle->limpiable_id,
                        'estado' => EstadoLimpieza::Completada,
                    ],
                    [
                        'turno_id' => $horario->turno_id,
                        'colaborador_id' => $colab?->id,
                        'carrito_id' => $carrito?->id,
                        'hora_inicio' => '09:00:00',
                        'hora_fin' => '09:50:00',
                        'observaciones' => 'Completada el día de ayer.',
                    ]
                );
            }
        }
    }
}
