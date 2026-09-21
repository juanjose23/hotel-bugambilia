<?php

declare(strict_types=1);

namespace Database\Seeders\Servicios;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Interactors\Activos\Gestion\AsignarActivo;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;

/**
 * Asigna activos fijos (flota) a los servicios hoteleros usando el
 * interactor AsignarActivo para mantener una única asignación vigente
 * por activo (cierra la anterior y abre la nueva).
 *
 * Requiere que previamente existan activos individualizados
 * (ActivosSeeder → ActivoFijoSeeder). Idempotente: omite activos que ya
 * estén vigentes en el mismo servicio y no toca las mesas de restaurante.
 */
class ServicioActivoAsignacionSeeder extends Seeder
{
    /**
     * Mapeo servicio → palabras clave del producto para localizar activos.
     * Cada clave se usa una sola vez para no mover activos entre servicios.
     *
     * @var array<string, array<int, string>>
     */
    private const ACTIVOS_POR_SERVICIO = [
        'Cama adicional / Cuna' => ['Cama'],
        'Alquiler de Sala de Juntas Ejecutiva' => ['Televisor', 'Sillón'],
        'Coffee Break Ejecutivo (por persona)' => ['Mesa'],
        'WiFi Simétrico Dedicado Ultra Alta Velocidad' => ['UPS'],
        'Tour Guiado Colonial & Volcanes' => ['Sombrilla'],
    ];

    public function run(): void
    {
        $admin = User::where('email', 'admin@hotel.com')->first() ?? User::first();

        if (! $admin) {
            $this->command->warn('ServicioActivoAsignacionSeeder: no se encontró usuario admin.');

            return;
        }

        $asignador = app(AsignarActivo::class);
        $totalAsignados = 0;

        foreach (self::ACTIVOS_POR_SERVICIO as $nombreServicio => $keywords) {
            $servicio = Servicio::where('nombre', $nombreServicio)->first();

            if ($servicio === null) {
                $this->command->warn("ServicioActivoAsignacionSeeder: servicio no encontrado: {$nombreServicio}");

                continue;
            }

            $asignadosPorServicio = 0;

            foreach ($keywords as $keyword) {
                $activos = Activo::query()
                    ->with(['producto', 'asignacionActiva.asignable'])
                    ->whereHas('producto', fn ($q) => $q->where('nombre', 'like', "%{$keyword}%"))
                    ->get();

                foreach ($activos as $activo) {
                    if ($this->yaAsignadoAlServicio($activo, $servicio)) {
                        continue;
                    }

                    if ($this->estaEnMesaDeRestaurante($activo)) {
                        continue;
                    }

                    $asignador->ejecutar(
                        activoId: (int) $activo->id,
                        asignableType: Servicio::class,
                        asignableId: (int) $servicio->id,
                        userId: (int) $admin->id,
                        motivo: "Activo de la flota del servicio {$servicio->nombre}",
                    );

                    $asignadosPorServicio++;
                    $totalAsignados++;
                }
            }

            $this->command->info("  Servicio '{$servicio->nombre}': {$asignadosPorServicio} activos asignados.");
        }

        $this->command->info("ServicioActivoAsignacionSeeder: {$totalAsignados} activos asignados a servicios.");
    }

    private function yaAsignadoAlServicio(Activo $activo, Servicio $servicio): bool
    {
        $vigente = $activo->asignacionActiva;

        return $vigente !== null
            && $vigente->asignable_type === Servicio::class
            && (int) $vigente->asignable_id === (int) $servicio->id;
    }

    private function estaEnMesaDeRestaurante(Activo $activo): bool
    {
        $asignable = $activo->asignacionActiva?->asignable;

        return $asignable instanceof Espacio
            && $asignable->tipo === TipoEspacio::MESA;
    }
}
