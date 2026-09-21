<?php

declare(strict_types=1);

namespace Database\Seeders\Compras\Solicitudes;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Compras\EstadoSolicitud;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Compras\Solicitud;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Contexto compartido encadenado entre los sub-seeders del módulo Compras.
 *
 * @phpstan-type ContextoCompras array{
 *     proveedores: Collection<int, Proveedor>,
 *     productos: Collection<int, Producto>,
 *     unidadMedida: Catalogo|null,
 *     condicionPago: Catalogo|null,
 *     catalogoIds: array<string, int>,
 *     solicitudes: array{
 *         'SOL-COMP-2026': Solicitud,
 *         'SOL-STOCK-2026': Solicitud,
 *         'SOL-INFRA-2026': Solicitud,
 *     },
 * cotizaciones: array{
 *     'SOL-WIN': Cotizacion|null,
 * },
 * }
 *
 * Crea las solicitudes de compra de los distintos escenarios del módulo Compras.
 * Devuelve el contexto compartido con las demás fases del flujo.
 */
class SolicitudesSeeder extends Seeder
{
    public function run(): void
    {
        $this->ejecutar();
    }

    /**
     * @return ContextoCompras
     */
    public function ejecutar(): array
    {
        $colaborador = Colaborador::first();
        if (! $colaborador) {
            throw new \RuntimeException('SolicitudesSeeder requiere al menos un Colaborador (ejecute ColaboradorBaseSeeder antes).');
        }

        $proveedores = Proveedor::limit(5)->get();
        $productos = Producto::limit(100)->get();
        $unidadMedida = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', CatalogoTipo::UNIDAD_MEDIDA->value))->first();
        $condicionPago = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', CatalogoTipo::CONDICION_PAGO->value))->first();

        /** @var array<string, int> $catalogoIds */
        $catalogoIds = [];
        foreach (Catalogo::all() as $catalogo) {
            $catalogoIds[(string) $catalogo->codigo] = (int) $catalogo->id;
        }

        // --- ESCENARIO 1: SOLICITUDES DEPARTAMENTALES EN DIFERENTES ESTADOS ---
        $solicitudesBase = [
            [
                'codigo' => 'SOL-MANT-001',
                'depto' => 'DEP_MANTENIMIENTO',
                'estado' => EstadoSolicitud::Borrador,
                'motivo' => 'Reparación urgente de calderas en zona de lavandería.',
            ],
            [
                'codigo' => 'SOL-ALIM-002',
                'depto' => 'DEP_OPERACIONES',
                'estado' => EstadoSolicitud::Pendiente,
                'motivo' => 'Reposición mensual de granos básicos y café para restaurante.',
            ],
            [
                'codigo' => 'SOL-AMEN-003',
                'depto' => 'DEP_AMA_LLAVES',
                'estado' => EstadoSolicitud::Rechazada,
                'motivo' => 'Compra de toallas de lujo (Rechazada por falta de presupuesto trimestral).',
            ],
            [
                'codigo' => 'SOL-RECP-004',
                'depto' => 'DEP_RECEPCION',
                'estado' => EstadoSolicitud::Cancelada,
                'motivo' => 'Papelería institucional (Cancelada por cambio de diseño de marca).',
            ],
            [
                'codigo' => 'SOL-EQUIP-005',
                'depto' => 'DEP_MANTENIMIENTO',
                'estado' => EstadoSolicitud::Aprobada,
                'motivo' => 'Renovación de aire acondicionado suites y proyector salón B.',
            ],
        ];

        $deptoId = $catalogoIds['DEP_MANTENIMIENTO'] ?? 1;

        foreach ($solicitudesBase as $sBase) {
            if (Solicitud::where('codigo', $sBase['codigo'])->exists()) {
                continue;
            }

            $sol = Solicitud::create([
                'codigo' => $sBase['codigo'],
                'colaborador_id' => $colaborador->id,
                'departamento_solicitante_id' => $deptoId,
                'fecha_solicitud' => now()->subDays(rand(1, 10)),
                'estado' => $sBase['estado'],
                'motivo' => $sBase['motivo'],
            ]);

            $count = ($sBase['codigo'] === 'SOL-MANT-001') ? 15 : 2;
            foreach ($productos->random($count) as $p) {
                $variante = DB::table('producto_variantes')->where('producto_id', $p->id)->first();
                $sol->items()->create([
                    'producto_id' => $p->id,
                    'producto_variante_id' => $variante?->id,
                    'cantidad_solicitada' => rand(5, 50),
                    'unidad_medida_id' => $p->unidad_medida_id ?? $unidadMedida?->id,
                ]);
            }
        }

        // --- ESCENARIO 2: SOLICITUD PARA COMPARATIVA DE PRECIOS ---
        $solicitudComp = Solicitud::where('codigo', 'SOL-COMP-2026')->first();
        if (! $solicitudComp) {
            $solicitudComp = Solicitud::create([
                'codigo' => 'SOL-COMP-2026',
                'colaborador_id' => $colaborador->id,
                'departamento_solicitante_id' => $deptoId,
                'fecha_solicitud' => now()->subDays(15),
                'estado' => EstadoSolicitud::Aprobada,
                'motivo' => 'Equipamiento tecnológico y mobiliario para el nuevo centro de negocios.',
            ]);

            $catTecnologia = array_filter([
                $catalogoIds['CAT_PRO_ELECTRO'] ?? null,
                $catalogoIds['CAT_PRO_MOB'] ?? null,
            ]);
            $prodsComp = Producto::whereIn('categoria_id', $catTecnologia)->take(3)->get();
            foreach ($prodsComp as $prod) {
                $solicitudComp->items()->create([
                    'producto_id' => $prod->id,
                    'producto_variante_id' => DB::table('producto_variantes')->where('producto_id', $prod->id)->value('id'),
                    'cantidad_solicitada' => 5,
                    'cantidad_aprobada' => 5,
                    'unidad_medida_id' => $prod->unidad_medida_id ?? $unidadMedida?->id,
                ]);
            }
        }

        // --- ESCENARIO 3: SOLICITUD DEL FLUJO COMPLETO HASTA RECEPCIÓN ---
        $solicitudFull = Solicitud::where('codigo', 'SOL-STOCK-2026')->first();
        if (! $solicitudFull) {
            $solicitudFull = Solicitud::create([
                'codigo' => 'SOL-STOCK-2026',
                'colaborador_id' => $colaborador->id,
                'departamento_solicitante_id' => $deptoId,
                'fecha_solicitud' => now()->subDays(20),
                'estado' => EstadoSolicitud::Aprobada,
                'motivo' => 'Reposición de insumos de limpieza y suministros operativos.',
            ]);

            $catLimpieza = $catalogoIds['CAT_PRO_LIMP_QUIM'] ?? 0;
            $prodsLimpieza = Producto::where('categoria_id', $catLimpieza)->take(3)->get();
            foreach ($prodsLimpieza as $prod) {
                $solicitudFull->items()->create([
                    'producto_id' => $prod->id,
                    'producto_variante_id' => DB::table('producto_variantes')->where('producto_id', $prod->id)->value('id'),
                    'cantidad_solicitada' => 20,
                    'cantidad_aprobada' => 20,
                    'unidad_medida_id' => $prod->unidad_medida_id ?? $unidadMedida?->id,
                ]);
            }
        }

        // --- ESCENARIO 4: MANTENIMIENTO DE INFRAESTRUCTURA ---
        $solicitudManto = Solicitud::where('codigo', 'SOL-INFRA-2026')->first();
        if (! $solicitudManto) {
            $solicitudManto = Solicitud::create([
                'codigo' => 'SOL-INFRA-2026',
                'colaborador_id' => $colaborador->id,
                'departamento_solicitante_id' => $deptoId,
                'fecha_solicitud' => now()->subDays(5),
                'estado' => EstadoSolicitud::Aprobada,
                'motivo' => 'Materiales para remodelación de fachada y área de alberca.',
            ]);

            $catMantenimiento = $catalogoIds['CAT_PRO_MANT'] ?? 0;
            $prodsManto = Producto::where('categoria_id', $catMantenimiento)->take(4)->get();
            foreach ($prodsManto as $prod) {
                $solicitudManto->items()->create([
                    'producto_id' => $prod->id,
                    'producto_variante_id' => DB::table('producto_variantes')->where('producto_id', $prod->id)->value('id'),
                    'cantidad_solicitada' => 10,
                    'cantidad_aprobada' => 10,
                    'unidad_medida_id' => $prod->unidad_medida_id ?? $unidadMedida?->id,
                ]);
            }
        }

        return [
            'proveedores' => $proveedores,
            'productos' => $productos,
            'unidadMedida' => $unidadMedida,
            'condicionPago' => $condicionPago,
            'catalogoIds' => $catalogoIds,
            'solicitudes' => [
                'SOL-COMP-2026' => $solicitudComp,
                'SOL-STOCK-2026' => $solicitudFull,
                'SOL-INFRA-2026' => $solicitudManto,
            ],
            'cotizaciones' => [
                'SOL-WIN' => null,
            ],
        ];
    }
}
