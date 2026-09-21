<?php

declare(strict_types=1);

namespace Database\Seeders\Activos\Gestion;

use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Enums\Compras\EstadoSolicitud;
use App\Interactors\Activos\Gestion\AsignarActivo;
use App\Interactors\Inventario\Recepciones\RegistrarEntradaRecepcion;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Flujo de adquisición de activos fijos usando los productos tipo=3 ya
 * creados por ProductoSeeder y el interactor RegistrarEntradaRecepcion
 * (que individualiza cada unidad y la asigna inicialmente al almacén,
 * generando su inventario en inv_stock).
 */
class ActivoFijoSeeder extends Seeder
{
    public function run(): void
    {
        $this->ejecutar();
    }

    public function ejecutar(): void
    {
        $admin = User::where('email', 'admin@hotel.com')->first() ?? User::first();
        if (! $admin) {
            $this->command->warn('ActivoFijoSeeder: no se encontró usuario admin.');

            return;
        }

        $proveedores = Proveedor::limit(3)->get();
        $monedaUSD = Moneda::where('codigo', 'USD')->first() ?? Moneda::where('es_predeterminada', true)->first() ?? Moneda::first();
        $condPago = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'CONDICION_PAGO'))->first();
        $unidadMed = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'UNIDAD_MEDIDA'))->first();
        $colaborador = Colaborador::first();

        if ($proveedores->count() < 2 || ! $colaborador || ! $monedaUSD || ! $condPago) {
            $this->command->warn('ActivoFijoSeeder: faltan dependencias mínimas.');

            return;
        }

        $uniUd = Catalogo::where('codigo', 'UNI_UD')->value('id');

        // Resolver productos tipo=3 existentes (sin crear nuevos) por palabra clave.
        $resolver = function (string $keyword): ?array {
            $producto = Producto::where('tipo', 3)
                ->where('nombre', 'like', '%'.$keyword.'%')
                ->orderBy('id')
                ->first();

            if (! $producto) {
                return null;
            }

            $variante = $producto->variantes()->first();

            return [
                'producto' => $producto,
                'variante' => $variante,
            ];
        };

        $flujos = [
            [
                'cod' => 'AF-001', 'prov' => $proveedores->get(0), 'dias' => 30, 'label' => 'Habitaciones',
                'items' => [
                    ['kw' => 'Cama', 'cant' => 15, 'precio' => 350.00],
                    ['kw' => 'Escritorio', 'cant' => 10, 'precio' => 220.00],
                    ['kw' => 'Televisor', 'cant' => 10, 'precio' => 480.00],
                    ['kw' => 'Aire', 'cant' => 10, 'precio' => 520.00],
                ],
            ],
            [
                'cod' => 'AF-002', 'prov' => $proveedores->get(1), 'dias' => 25, 'label' => 'Áreas Públicas',
                'items' => [
                    ['kw' => 'Mesa', 'cant' => 5, 'precio' => 250.00],
                    ['kw' => 'Sillón', 'cant' => 8, 'precio' => 380.00],
                    ['kw' => 'Sombrilla', 'cant' => 4, 'precio' => 90.00],
                ],
            ],
            [
                'cod' => 'AF-003', 'prov' => $proveedores->get(2), 'dias' => 20, 'label' => 'Sistemas',
                'items' => [
                    ['kw' => 'UPS', 'cant' => 5, 'precio' => 280.00],
                    ['kw' => 'Televisor', 'cant' => 3, 'precio' => 480.00],
                ],
            ],
        ];

        $totalActivos = 0;

        foreach ($flujos as $f) {
            $this->command->info("--- Flujo: {$f['label']} ---");

            try {
                $sol = Solicitud::where('codigo', "SOL-{$f['cod']}")->first();
                if ($sol) {
                    $this->command->info("  Solicitud SOL-{$f['cod']} ya existe, saltando flujo.");

                    continue;
                }

                // Resolver ítems con productos existentes; omitir los que no existan.
                $itemsResueltos = [];
                foreach ($f['items'] as $it) {
                    $res = $resolver($it['kw']);
                    if (! $res) {
                        $this->command->warn("  Producto tipo=3 no encontrado para clave: {$it['kw']}");

                        continue;
                    }
                    $itemsResueltos[] = ['kw' => $it['kw'], 'cant' => $it['cant'], 'precio' => $it['precio'], 'res' => $res];
                }

                if ($itemsResueltos === []) {
                    $this->command->warn('  Sin ítems resueltos, saltando flujo.');

                    continue;
                }

                $sol = Solicitud::create([
                    'codigo' => "SOL-{$f['cod']}",
                    'colaborador_id' => $colaborador->id,
                    'departamento_solicitante_id' => 1,
                    'fecha_solicitud' => now()->subDays($f['dias']),
                    'estado' => EstadoSolicitud::Aprobada,
                    'motivo' => "Equipamiento {$f['label']}",
                ]);

                foreach ($itemsResueltos as $it) {
                    $sol->items()->create([
                        'producto_id' => $it['res']['producto']->id,
                        'producto_variante_id' => $it['res']['variante']?->id,
                        'cantidad_solicitada' => $it['cant'],
                        'cantidad_aprobada' => $it['cant'],
                        'unidad_medida_id' => $uniUd,
                    ]);
                }

                // Cotización
                $sub = 0;
                $cot = Cotizacion::create([
                    'solicitud_id' => $sol->id,
                    'proveedor_id' => $f['prov']?->id,
                    'fecha_cotizacion' => now()->subDays($f['dias'] - 3),
                    'dias_entrega' => rand(5, 10),
                    'condicion_pago_id' => $condPago->id,
                    'creada_por' => $admin->id,
                    'moneda_id' => $monedaUSD->id,
                    'es_elegida' => true,
                    'elegida_por' => $admin->id,
                    'elegida_en' => now()->subDays($f['dias'] - 4),
                    'subtotal' => 0,
                    'total' => 0,
                ]);
                foreach ($sol->items as $si) {
                    $itemDef = collect($itemsResueltos)->first(fn ($r) => $r['res']['producto']->id === $si->producto_id);
                    $precio = $itemDef['precio'] ?? 100;
                    $sl = $si->cantidad_aprobada * $precio;
                    $sub += $sl;
                    $cot->items()->create([
                        'producto_id' => $si->producto_id,
                        'producto_variante_id' => $si->producto_variante_id,
                        'cantidad' => $si->cantidad_aprobada,
                        'precio_unitario' => $precio,
                        'subtotal' => $sl,
                        'es_elegido' => true,
                    ]);
                }
                $cot->update(['subtotal' => $sub, 'total' => $sub * 1.15]);

                // Orden de Compra
                $oc = OrdenCompra::create([
                    'codigo' => "OC-{$f['cod']}",
                    'proveedor_id' => $f['prov']?->id,
                    'solicitud_id' => $sol->id,
                    'cotizacion_id' => $cot->id,
                    'fecha_orden' => now()->subDays($f['dias'] - 5),
                    'condicion_pago_id' => $condPago->id,
                    'estado' => EstadoOrdenCompra::Recibida,
                    'subtotal' => $sub,
                    'total' => $sub * 1.15,
                ]);
                foreach ($cot->items as $ci) {
                    $oc->items()->create([
                        'producto_id' => $ci->producto_id,
                        'producto_variante_id' => $ci->producto_variante_id,
                        'cantidad' => $ci->cantidad,
                        'precio_unitario' => $ci->precio_unitario,
                        'subtotal' => $ci->subtotal,
                        'unidad_medida_id' => $uniUd,
                    ]);
                }

                // Recepción e individualización vía interactor (genera inventario).
                $rc = RecepcionCompra::create([
                    'codigo' => "RC-{$f['cod']}",
                    'orden_compra_id' => $oc->id,
                    'fecha_recepcion' => now()->subDays($f['dias'] - 8),
                    'recibido_por_id' => $admin->id,
                    'estado' => EstadoRecepcion::Completa,
                    'notas' => "Recepción {$f['label']}",
                ]);

                $itemsForUseCase = [];

                foreach ($oc->items as $oi) {
                    $rcItem = $rc->items()->create([
                        'orden_item_id' => $oi->id,
                        'producto_id' => $oi->producto_id,
                        'producto_variante_id' => $oi->producto_variante_id,
                        'cantidad_recibida' => $oi->cantidad,
                        'cantidad_rechazada' => 0,
                        'lote_proveedor' => 'LOT-'.Str::upper(Str::random(6)),
                        'fecha_vencimiento' => null,
                    ]);

                    $itemsForUseCase[] = [
                        'id' => $rcItem->id,
                        'producto_id' => (int) $rcItem->producto_id,
                        'producto_variante_id' => $rcItem->producto_variante_id !== null ? (int) $rcItem->producto_variante_id : null,
                        'cantidad_recibida' => (float) $rcItem->cantidad_recibida,
                        'lote_proveedor' => $rcItem->lote_proveedor,
                    ];
                }

                app(RegistrarEntradaRecepcion::class)->ejecutar(
                    nuevoEstado: 'Completa',
                    items: $itemsForUseCase,
                    proveedorId: $oc->proveedor_id,
                    creadoPorId: $admin->id,
                );

                $cantidadIndividualizada = 0;
                foreach ($oc->items as $oi) {
                    $cantidadIndividualizada += (int) $oi->cantidad;
                }
                $totalActivos += $cantidadIndividualizada;
                $this->command->info("  {$totalActivos} activos individualizados en {$f['label']}");

            } catch (\Throwable $e) {
                $this->command->error("  Flujo {$f['label']}: ".$e->getMessage());
                $this->command->error('  Line: '.$e->getLine().' File: '.$e->getFile());
            }
        }

        // ─── ASIGNAR A HABITACIONES / ESPACIOS (los activos en bodega) ───
        try {
            // Solo activos cuya asignación vigente es una Ubicación (aún en bodega):
            // evita redistribuir activos ya asignados a su destino final al re-ejecutar.
            $enBodega = Activo::with('producto')->whereHas('asignaciones', fn ($q) => $q
                ->where('asignable_type', Ubicacion::class)
                ->where('estado', EstadoAsignacion::Vigente->value)
                ->whereNull('fecha_fin')
            )->orderBy('id')->get();

            $habitaciones = Habitacion::all();
            $espaciosComunes = Espacio::whereIn('tipo', ['gym', 'salon', 'spa', 'restaurante', 'bar', 'terraza'])->get();

            if ($enBodega->isEmpty()) {
                $this->command->info('No hay activos con asignación vigente a Ubicación (bodega) para distribuir.');
            }

            $paraHabitaciones = $enBodega->filter(fn (Activo $a) => $this->esActivoDeHabitacion($a))->values();
            $paraEspacios = $enBodega->reject(fn (Activo $a) => $this->esActivoDeHabitacion($a))->values();

            foreach ($paraHabitaciones->values() as $i => $a) {
                $hab = $habitaciones->get($i % max($habitaciones->count(), 1));
                if (! $hab) {
                    continue;
                }
                app(AsignarActivo::class)->ejecutar(
                    activoId: $a->id,
                    asignableType: Habitacion::class,
                    asignableId: $hab->id,
                    userId: $admin->id,
                    motivo: "Asignado a {$hab->nombre}",
                );
            }
            if ($paraHabitaciones->isNotEmpty()) {
                $this->command->info($paraHabitaciones->count().' activos de habitación asignados.');
            }

            foreach ($paraEspacios->values() as $i => $a) {
                $esp = $espaciosComunes->get($i % max($espaciosComunes->count(), 1));
                if (! $esp) {
                    continue;
                }
                app(AsignarActivo::class)->ejecutar(
                    activoId: $a->id,
                    asignableType: Espacio::class,
                    asignableId: $esp->id,
                    userId: $admin->id,
                    motivo: "Equipamiento de {$esp->nombre}",
                );
            }
            if ($paraEspacios->isNotEmpty()) {
                $this->command->info($paraEspacios->count().' activos de áreas comunes asignados.');
            }
        } catch (\Throwable $e) {
            $this->command->error('Error en asignaciones: '.$e->getMessage());
        }

        // ─── MANTENIMIENTOS DE MUESTRA ───
        try {
            $ultimosActivos = Activo::take(3)->get();
            $tpos = [TipoMantenimiento::Correctivo, TipoMantenimiento::Preventivo, TipoMantenimiento::Correctivo];
            foreach ($ultimosActivos as $i => $a) {
                ActivoMantenimiento::create([
                    'activo_id' => $a->id,
                    'tipo' => $tpos[$i],
                    'fecha_programada' => now()->subDays(rand(1, 5))->toDateString(),
                    'fecha_realizada' => $i === 0 ? null : now()->toDateString(),
                    'notas' => 'Mantenimiento de muestra #'.($i + 1),
                    'costo_real' => ($i + 1) * 25,
                    'realizado_por_id' => $admin->id,
                    'estado' => $i === 0 ? EstadoMantenimiento::EnProceso : EstadoMantenimiento::Completado,
                ]);
            }
            $this->command->info('Mantenimientos registrados.');
        } catch (\Throwable $e) {
            $this->command->error('Error en mantenimientos: '.$e->getMessage());
        }
    }

    /**
     * Determina si el activo corresponde al equipamiento de una habitación
     * (se asigna a Habitacion) o a un área común (se asigna a Espacio).
     */
    private function esActivoDeHabitacion(Activo $activo): bool
    {
        $nombre = mb_strtolower((string) ($activo->producto->nombre ?? ''));

        return str_contains($nombre, 'cama')
            || str_contains($nombre, 'colch')
            || str_contains($nombre, 'escritorio')
            || str_contains($nombre, 'televisor')
            || str_contains($nombre, ' tv')
            || str_contains($nombre, 'aire')
            || str_contains($nombre, 'clima');
    }
}
