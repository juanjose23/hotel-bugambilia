<?php

declare(strict_types=1);

namespace Database\Seeders\Reservas;

use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Genera 2.000 cargos de servicios adicionales en los folios de cuentas
 * de reservas históricas existentes.
 *
 * Distribución de cargos (conceptos):
 *   - Spa & Masajes:         400 cargos
 *   - Lavandería:            350 cargos
 *   - Minibar:               600 cargos  ← mayor frecuencia (consumo diario)
 *   - Room Service:          400 cargos
 *   - Traslados / Tours:     250 cargos
 *
 * Los cargos se insertan en cuenta_detalles sin modificar los totales
 * de la cuenta padre (son cargos históricos para fines de reporte).
 */
final class ServiciosAdicionalesHistoricoSeeder extends Seeder
{
    private const CHUNK_SIZE = 200;

    /** @var array<int, array{concepto: string, min: float, max: float, tipo: int}> */
    private const SERVICIOS = [
        // Spa & Masajes
        ['concepto' => 'Masaje Relajante 60 min',        'min' => 600.0,  'max' => 900.0,  'tipo' => 5],
        ['concepto' => 'Masaje de Tejido Profundo',       'min' => 700.0,  'max' => 1100.0, 'tipo' => 5],
        ['concepto' => 'Facial Hidratante',               'min' => 450.0,  'max' => 750.0,  'tipo' => 5],
        ['concepto' => 'Sesión de Sauna',                 'min' => 200.0,  'max' => 350.0,  'tipo' => 5],
        ['concepto' => 'Paquete Spa Pareja',              'min' => 1400.0, 'max' => 2000.0, 'tipo' => 5],

        // Lavandería
        ['concepto' => 'Lavandería - Ropa Casual',        'min' => 80.0,   'max' => 200.0,  'tipo' => 6],
        ['concepto' => 'Lavandería Express',              'min' => 120.0,  'max' => 280.0,  'tipo' => 6],
        ['concepto' => 'Planchado Formal',                'min' => 60.0,   'max' => 150.0,  'tipo' => 6],
        ['concepto' => 'Lavandería - Traje de Baño',      'min' => 40.0,   'max' => 80.0,   'tipo' => 6],

        // Minibar
        ['concepto' => 'Minibar - Agua Mineral',          'min' => 25.0,   'max' => 50.0,   'tipo' => 7],
        ['concepto' => 'Minibar - Refresco',              'min' => 30.0,   'max' => 60.0,   'tipo' => 7],
        ['concepto' => 'Minibar - Cerveza Importada',     'min' => 80.0,   'max' => 120.0,  'tipo' => 7],
        ['concepto' => 'Minibar - Vino Tinto 200ml',      'min' => 150.0,  'max' => 250.0,  'tipo' => 7],
        ['concepto' => 'Minibar - Whisky Miniatura',      'min' => 120.0,  'max' => 200.0,  'tipo' => 7],
        ['concepto' => 'Minibar - Snack Mixto',           'min' => 40.0,   'max' => 90.0,   'tipo' => 7],
        ['concepto' => 'Minibar - Chocolate Premium',     'min' => 50.0,   'max' => 100.0,  'tipo' => 7],
        ['concepto' => 'Minibar - Jugo Natural',          'min' => 35.0,   'max' => 70.0,   'tipo' => 7],

        // Room Service
        ['concepto' => 'Room Service - Desayuno',         'min' => 180.0,  'max' => 350.0,  'tipo' => 8],
        ['concepto' => 'Room Service - Cena Completa',    'min' => 280.0,  'max' => 550.0,  'tipo' => 8],
        ['concepto' => 'Room Service - Café y Repostería', 'min' => 80.0,   'max' => 160.0,  'tipo' => 8],
        ['concepto' => 'Room Service - Menú Niños',       'min' => 120.0,  'max' => 220.0,  'tipo' => 8],
        ['concepto' => 'Room Service - Bebidas',          'min' => 60.0,   'max' => 200.0,  'tipo' => 8],

        // Traslados / Tours
        ['concepto' => 'Traslado Aeropuerto',             'min' => 400.0,  'max' => 700.0,  'tipo' => 9],
        ['concepto' => 'Tour Volcán Masaya',              'min' => 600.0,  'max' => 1000.0, 'tipo' => 9],
        ['concepto' => 'Tour Ciudad Colonial Granada',    'min' => 500.0,  'max' => 900.0,  'tipo' => 9],
        ['concepto' => 'Alquiler Bicicleta (día)',        'min' => 150.0,  'max' => 250.0,  'tipo' => 9],
        ['concepto' => 'Transporte Privado al Centro',    'min' => 200.0,  'max' => 400.0,  'tipo' => 9],
    ];

    // Cuántos cargos por tipo (tipo: 5=spa, 6=lavandería, 7=minibar, 8=roomservice, 9=traslados)
    private const CUOTAS_POR_TIPO = [
        5 => 400,
        6 => 350,
        7 => 600,
        8 => 400,
        9 => 250,
    ];

    public function run(): void
    {
        mt_srand(20261202);

        $cuentaIds = $this->obtenerCuentasHistoricas();

        if ($cuentaIds->isEmpty()) {
            $this->command->warn('No hay cuentas históricas. Ejecute ReservaHistoricaSeeder primero.');

            return;
        }

        $rawMonedaId = Moneda::query()->where('es_predeterminada', true)->value('id') ?? Moneda::query()->value('id');
        $monedaId = is_numeric($rawMonedaId) ? (int) $rawMonedaId : 1;

        // Limpiar ejecuciones previas para idempotencia
        DB::table('cuenta_detalles')->where('descripcion', 'Carga histórica de servicios adicionales')->delete();

        $detallesInsert = [];
        $totalGenerados = 0;

        foreach (self::CUOTAS_POR_TIPO as $tipo => $cantidad) {
            $serviciosDeTipo = array_values(array_filter(self::SERVICIOS, fn ($s) => $s['tipo'] === $tipo));

            for ($i = 0; $i < $cantidad; $i++) {
                $servicio = $serviciosDeTipo[mt_rand(0, count($serviciosDeTipo) - 1)];
                $cuentaId = $cuentaIds[mt_rand(0, $cuentaIds->count() - 1)];
                $fecha = $this->fechaAleatoria(365);
                $cantidad_ = mt_rand(1, 3);
                $precio = round(mt_rand((int) ($servicio['min'] * 100), (int) ($servicio['max'] * 100)) / 100, 2);
                $sub = round($precio * $cantidad_, 2);

                $detallesInsert[] = [
                    'cuenta_id' => $cuentaId,
                    'moneda_id' => $monedaId,
                    'origen_type' => null,
                    'origen_id' => null,
                    'tipo_detalle' => $tipo,
                    'espacio_id' => null,
                    'concepto' => $servicio['concepto'],
                    'descripcion' => 'Carga histórica de servicios adicionales',
                    'cantidad' => $cantidad_,
                    'precio_unitario' => $precio,
                    'subtotal' => $sub,
                    'total' => $sub,
                    'estado' => 1, // activo/cobrado
                    'metadatos' => null,
                    'creador_id' => null,
                    'anulado_por' => null,
                    'anulado_en' => null,
                    'created_at' => $fecha->format('Y-m-d H:i:s'),
                    'updated_at' => $fecha->format('Y-m-d H:i:s'),
                ];

                $totalGenerados++;

                if (count($detallesInsert) >= self::CHUNK_SIZE) {
                    DB::table('cuenta_detalles')->insert($detallesInsert);
                    $detallesInsert = [];
                }
            }
        }

        if (! empty($detallesInsert)) {
            DB::table('cuenta_detalles')->insert($detallesInsert);
        }

        $this->command->info("✓ Servicios adicionales: {$totalGenerados} cargos históricos insertados (spa, lavandería, minibar, room service, traslados).");
    }

    /**
     * Devuelve una muestra de IDs de cuentas históricas cerradas.
     *
     * @return Collection<int, int>
     */
    private function obtenerCuentasHistoricas(): Collection
    {
        return Cuenta::query()
            ->whereNotNull('cerrada_at')
            ->whereNotNull('reserva_id')
            ->inRandomOrder()
            ->limit(500)
            ->pluck('id')
            ->map(fn ($id) => is_numeric($id) ? (int) $id : 0);
    }

    private function fechaAleatoria(int $diasAtras): Carbon
    {
        return Carbon::now()
            ->subDays(mt_rand(1, $diasAtras))
            ->setHour(mt_rand(7, 22))
            ->setMinute(mt_rand(0, 59))
            ->setSecond(0);
    }
}
