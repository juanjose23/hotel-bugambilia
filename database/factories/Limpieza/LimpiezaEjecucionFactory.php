<?php

declare(strict_types=1);

namespace Database\Factories\Limpieza;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\Turno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LimpiezaEjecucion>
 */
class LimpiezaEjecucionFactory extends Factory
{
    protected $model = LimpiezaEjecucion::class;

    public function definition(): array
    {
        return [
            'horario_id' => LimpiezaHorario::factory(),
            'solicitud_id' => null,
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => Habitacion::factory(),
            'turno_id' => Turno::factory(),
            'colaborador_id' => null,
            'carrito_id' => null,
            'fecha' => fake()->date(),
            'hora_inicio' => null,
            'hora_fin' => null,
            'estado' => EstadoLimpieza::Pendiente,
            'detalles_checklist' => null,
            'observaciones' => null,
            'recordatorio_enviado_at' => null,
            'estado_previo' => null,
            'consumos' => null,
        ];
    }

    public function paraEspacio(): static
    {
        return $this->state(fn (array $attrs) => [
            'limpiable_type' => Espacio::class,
            'limpiable_id' => Espacio::factory(),
        ]);
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attrs) => [
            'estado' => EstadoLimpieza::Pendiente,
            'colaborador_id' => null,
            'carrito_id' => null,
            'hora_inicio' => null,
            'hora_fin' => null,
        ]);
    }

    public function enProgreso(): static
    {
        return $this->state(fn (array $attrs) => [
            'estado' => EstadoLimpieza::EnProgreso,
            'hora_inicio' => fake()->randomElement(['08:05:00', '09:10:00', '10:00:00']),
            'hora_fin' => null,
        ]);
    }

    public function completada(bool $conDiscrepancia = false): static
    {
        return $this->state(fn (array $attrs) => [
            'estado' => $conDiscrepancia ? EstadoLimpieza::CompletadaConDiscrepancia : EstadoLimpieza::Completada,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '08:45:00',
            'observaciones' => $conDiscrepancia ? 'Discrepancia en cambio de sábanas.' : 'Limpieza de rutina realizada con éxito.',
        ]);
    }
}
