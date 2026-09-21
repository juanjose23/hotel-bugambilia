<?php

declare(strict_types=1);

namespace Database\Seeders\Activos\PlanesMantenimiento;

use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\EstadoPlanMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Activos\ActPlanMantenimiento;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;

class PlanMantenimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Siembra los planes de mantenimiento preventivo, correctivo, inspección y garantía
     * para los activos fijos del hotel, asociando los activos reales y tickets de ejecución.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@hotel.com')->first() ?? User::query()->first();
        $proveedores = Proveedor::query()->orderBy('id')->get();
        $monedaUsd = Moneda::query()->where('codigo', 'USD')->first() ?? Moneda::query()->first();

        if (! $admin || $proveedores->isEmpty() || ! $monedaUsd) {
            $this->command->warn('PlanMantenimientoSeeder: Faltan dependencias mínimas (Admin, Proveedores o Moneda USD).');

            return;
        }

        $proveedorClima = $proveedores->first(fn ($p) => str_contains(strtolower((string) ($p->nombre_comercial ?? $p->razon_social ?? '')), 'clima') || str_contains(strtolower((string) ($p->nombre_comercial ?? $p->razon_social ?? '')), 'refrig'))
            ?? $proveedores->first();

        $proveedorTecnologia = $proveedores->first(fn ($p) => str_contains(strtolower((string) ($p->nombre_comercial ?? $p->razon_social ?? '')), 'tech') || str_contains(strtolower((string) ($p->nombre_comercial ?? $p->razon_social ?? '')), 'elect'))
            ?? $proveedores->get(1)
            ?? $proveedores->first();

        $proveedorGeneral = $proveedores->get(2) ?? $proveedores->first();

        // 1. Obtener grupos de activos por tipo de producto
        $activosAire = Activo::query()->whereHas('producto', fn ($q) => $q->where('nombre', 'like', '%Aire%'))->get();
        $activosTv = Activo::query()->whereHas('producto', fn ($q) => $q->where('nombre', 'like', '%Televisor%')->orWhere('nombre', 'like', '%TV%'))->get();
        $activosMuebles = Activo::query()->whereHas('producto', fn ($q) => $q->where('nombre', 'like', '%Cama%')->orWhere('nombre', 'like', '%Escritorio%')->orWhere('nombre', 'like', '%Mesa%')->orWhere('nombre', 'like', '%Sillón%'))->get();
        $activosUps = Activo::query()->whereHas('producto', fn ($q) => $q->where('nombre', 'like', '%UPS%')->orWhere('nombre', 'like', '%Energía%'))->get();

        $planesDefinicion = [
            // ─── 01. PLAN CLIMATIZACIÓN Y AIRE ACONDICIONADO ───
            [
                'nombre' => 'Plan Semestral de Mantenimiento Preventivo de Aire Acondicionado & Climatización',
                'tipo' => TipoMantenimiento::Preventivo,
                'proveedor_id' => $proveedorClima->id,
                'frecuencia_dias' => 90,
                'fecha_inicio' => now()->subMonths(6)->toDateString(),
                'fecha_fin' => now()->addMonths(6)->toDateString(),
                'costo_estimado' => 1200.00,
                'fecha_ultimo_mantenimiento' => now()->subDays(45)->toDateString(),
                'fecha_proximo_mantenimiento' => now()->addDays(45)->toDateString(),
                'moneda_id' => $monedaUsd->id,
                'descripcion' => 'Limpieza profunda de serpentines, desinfección antibacteriana de filtros, medición de presiones de gas refrigerante ecológico R410A, revisión de sensores térmicos y verificación del drenaje de condensado.',
                'estado' => EstadoPlanMantenimiento::Activo,
                'activos' => $activosAire,
                'tickets' => [
                    [
                        'tipo' => TipoMantenimiento::Preventivo,
                        'fecha_programada' => now()->subDays(45)->toDateString(),
                        'fecha_realizada' => now()->subDays(45)->toDateString(),
                        'costo_real' => 280.00,
                        'estado' => EstadoMantenimiento::Completado,
                        'notas' => 'Mantenimiento preventivo completado con éxito. Limpieza química de evaporadores y recarga de 1.5 lb de gas R410A en unidades principales.',
                    ],
                    [
                        'tipo' => TipoMantenimiento::Preventivo,
                        'fecha_programada' => now()->addDays(45)->toDateString(),
                        'fecha_realizada' => null,
                        'costo_real' => null,
                        'estado' => EstadoMantenimiento::Programado,
                        'notas' => 'Próxima ronda programada: Inspección y cambio preventivo de filtros de aire en habitaciones del ala norte.',
                    ],
                ],
            ],

            // ─── 02. PLAN SMART TVS Y EQUIPOS MULTIMEDIA ───
            [
                'nombre' => 'Plan Trimestral de Calibración & Mantenimiento de Smart TVs y Audiovisual',
                'tipo' => TipoMantenimiento::Preventivo,
                'proveedor_id' => $proveedorTecnologia->id,
                'frecuencia_dias' => 90,
                'fecha_inicio' => now()->subMonths(4)->toDateString(),
                'fecha_fin' => now()->addMonths(8)->toDateString(),
                'costo_estimado' => 450.00,
                'fecha_ultimo_mantenimiento' => now()->subDays(20)->toDateString(),
                'fecha_proximo_mantenimiento' => now()->addDays(70)->toDateString(),
                'moneda_id' => $monedaUsd->id,
                'descripcion' => 'Actualización de firmware hotelero, prueba de puertos HDMI/USB, calibración de balance de color en pantallas 4K y limpieza de paneles con solución antiestática.',
                'estado' => EstadoPlanMantenimiento::Activo,
                'activos' => $activosTv,
                'tickets' => [
                    [
                        'tipo' => TipoMantenimiento::Preventivo,
                        'fecha_programada' => now()->subDays(20)->toDateString(),
                        'fecha_realizada' => now()->subDays(20)->toDateString(),
                        'costo_real' => 150.00,
                        'estado' => EstadoMantenimiento::Completado,
                        'notas' => 'Revisión y actualización de firmware en pantallas de habitaciones estándar y suites ejecutivas.',
                    ],
                    [
                        'tipo' => TipoMantenimiento::Preventivo,
                        'fecha_programada' => now()->addDays(70)->toDateString(),
                        'fecha_realizada' => null,
                        'costo_real' => null,
                        'estado' => EstadoMantenimiento::Programado,
                        'notas' => 'Próxima calibración programada para pantallas de salones y habitaciones familiares.',
                    ],
                ],
            ],

            // ─── 03. PLAN MOBILIARIO, CAMAS Y CARPINTERÍA ───
            [
                'nombre' => 'Plan Cuatrimestral de Ajuste Estructural, Tapicería y Barnizado de Mobiliario',
                'tipo' => TipoMantenimiento::Inspeccion,
                'proveedor_id' => $proveedorGeneral->id,
                'frecuencia_dias' => 120,
                'fecha_inicio' => now()->subMonths(3)->toDateString(),
                'fecha_fin' => now()->addMonths(9)->toDateString(),
                'costo_estimado' => 600.00,
                'fecha_ultimo_mantenimiento' => now()->subDays(30)->toDateString(),
                'fecha_proximo_mantenimiento' => now()->addDays(90)->toDateString(),
                'moneda_id' => $monedaUsd->id,
                'descripcion' => 'Inspección de solidez de somieres y respaldos de camas King/Queen, ajuste de pernos en escritorios ejecutivos, limpieza profunda de tapicería en sillones y tratamiento protector de madera.',
                'estado' => EstadoPlanMantenimiento::Activo,
                'activos' => $activosMuebles,
                'tickets' => [
                    [
                        'tipo' => TipoMantenimiento::Inspeccion,
                        'fecha_programada' => now()->subDays(30)->toDateString(),
                        'fecha_realizada' => now()->subDays(30)->toDateString(),
                        'costo_real' => 180.00,
                        'estado' => EstadoMantenimiento::Completado,
                        'notas' => 'Ajuste estructural de camas y barnizado preventivo en mesas de noche de planta baja.',
                    ],
                ],
            ],

            // ─── 04. PLAN SISTEMAS DE RESPALDO ELÉCTRICO Y UPS ───
            [
                'nombre' => 'Plan Semestral de Diagnóstico y Garantía de Sistemas UPS & Respaldo Eléctrico',
                'tipo' => TipoMantenimiento::Garantia,
                'proveedor_id' => $proveedorTecnologia->id,
                'frecuencia_dias' => 180,
                'fecha_inicio' => now()->subMonths(5)->toDateString(),
                'fecha_fin' => now()->addMonths(7)->toDateString(),
                'costo_estimado' => 500.00,
                'fecha_ultimo_mantenimiento' => now()->subDays(60)->toDateString(),
                'fecha_proximo_mantenimiento' => now()->addDays(120)->toDateString(),
                'moneda_id' => $monedaUsd->id,
                'descripcion' => 'Pruebas de banco de baterías con medidor de impedancia, simulación de corte de energía, calibración de inversores y verificación de cobertura de garantía de fabricante.',
                'estado' => EstadoPlanMantenimiento::Activo,
                'activos' => $activosUps,
                'tickets' => [
                    [
                        'tipo' => TipoMantenimiento::Garantia,
                        'fecha_programada' => now()->subDays(60)->toDateString(),
                        'fecha_realizada' => now()->subDays(60)->toDateString(),
                        'costo_real' => 0.00, // Cubierto por garantía
                        'estado' => EstadoMantenimiento::Completado,
                        'notas' => 'Servicio técnico cubierto por garantía del fabricante. Diagnóstico óptimo en módulos de baterías.',
                    ],
                ],
            ],
        ];

        foreach ($planesDefinicion as $def) {
            $plan = ActPlanMantenimiento::query()->updateOrCreate(
                [
                    'nombre' => $def['nombre'],
                ],
                [
                    'tipo' => $def['tipo'],
                    'proveedor_id' => $def['proveedor_id'],
                    'frecuencia_dias' => $def['frecuencia_dias'],
                    'fecha_inicio' => $def['fecha_inicio'],
                    'fecha_fin' => $def['fecha_fin'],
                    'costo_estimado' => $def['costo_estimado'],
                    'fecha_ultimo_mantenimiento' => $def['fecha_ultimo_mantenimiento'],
                    'fecha_proximo_mantenimiento' => $def['fecha_proximo_mantenimiento'],
                    'moneda_id' => $def['moneda_id'],
                    'descripcion' => $def['descripcion'],
                    'estado' => $def['estado'],
                ]
            );

            // Asignar los activos cubiertos por este plan
            $activosColeccion = $def['activos'];
            if ($activosColeccion->isNotEmpty()) {
                $plan->activos()->syncWithoutDetaching($activosColeccion->pluck('id')->toArray());
            }

            // Crear los tickets asociados a este plan y sus activos
            foreach ($def['tickets'] as $idx => $ticketDef) {
                $activoParaTicket = $activosColeccion->isNotEmpty()
                    ? $activosColeccion->get($idx % $activosColeccion->count())
                    : Activo::query()->first();

                if ($activoParaTicket) {
                    ActivoMantenimiento::query()->updateOrCreate(
                        [
                            'plan_id' => $plan->id,
                            'activo_id' => $activoParaTicket->id,
                            'fecha_programada' => $ticketDef['fecha_programada'],
                        ],
                        [
                            'tipo' => $ticketDef['tipo'],
                            'fecha_realizada' => $ticketDef['fecha_realizada'],
                            'costo_real' => $ticketDef['costo_real'],
                            'realizado_por_id' => $admin->id,
                            'estado' => $ticketDef['estado'],
                            'notas' => $ticketDef['notas'],
                        ]
                    );
                }
            }
        }
    }
}
