<?php

declare(strict_types=1);

// app/database/seeders/ActivosFlowSeeder.php

namespace Database\Seeders;

use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoIndividualizacion;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\Activos\ActPlanMantenimiento;
use App\Models\Activos\RegistroIndividualizacion;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Colaboradores\Colaborador;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\Models\Habitaciones\Habitacion;
use App\Models\Monedas\Moneda;
use App\Models\User;
use App\UseCases\Activos\Mutations\Gestion\IndividualizarActivos;
use App\UseCases\Inventario\Recepciones\Mutations\RegistrarEntradaRecepcion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivosFlowSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hotel.com')->first()
            ?? User::first();

        if (! $admin) {
            $this->command->warn('⚠ No se encontró usuario admin. Omitiendo ActivosFlowSeeder.');

            return;
        }

        Auth::login($admin);

        $colaborador = Colaborador::first();
        $proveedor = Proveedor::first();
        $moneda = Moneda::where('codigo', 'USD')->first() ?? Moneda::first();
        $condPago = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'CPAG'))->first();
        $unidadMed = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'UMED'))->first();
        $ubicAlmacen = Ubicacion::where('tipo', 'almacen')->where('estado', 1)->first()
            ?? Ubicacion::where('estado', 1)->first();

        if (! $colaborador || ! $proveedor || ! $moneda || ! $ubicAlmacen) {
            $this->command->warn('⚠ Faltan dependencias (colaborador, proveedor, moneda, ubicación). Omitiendo ActivosFlowSeeder.');

            return;
        }

        // ----------------------------------------------------------------
        // Productos de tipo 3 (Activo Fijo) a comprar
        // ----------------------------------------------------------------
        /** @var Collection<int, Producto> $productosActivo */
        $productosActivo = Producto::where('tipo', 3)->take(3)->get();

        if ($productosActivo->isEmpty()) {
            $this->command->warn('⚠ No hay productos de tipo=3 (Activo Fijo). Omitiendo ActivosFlowSeeder.');

            return;
        }

        DB::transaction(function () use ($admin, $colaborador, $proveedor, $moneda, $condPago, $unidadMed, $productosActivo) {

            // ============================================================
            // PASO 1: SOLICITUD DE COMPRA DE ACTIVOS
            // ============================================================
            $solicitud = Solicitud::create([
                'codigo' => 'SOL-ACT-'.now()->format('Y').'-001',
                'colaborador_id' => $colaborador->id,
                'departamento_solicitante_id' => 1,
                'fecha_solicitud' => now()->subDays(30),
                'estado' => EstadoSolicitud::Aprobada,
                'motivo' => 'Renovación de equipos y mobiliario para habitaciones (TVs, Aires Acondicionados, Camas King).',
            ]);

            $cantidadesPorProducto = [3, 2, 1]; // 3 TVs, 2 ACs, 1 cama (o los 3 primeros activos disponibles)
            $itemsSolicitud = [];

            foreach ($productosActivo as $index => $prod) {
                $cantidad = $cantidadesPorProducto[$index] ?? 1;
                $variante = DB::table('producto_variantes')->where('producto_id', $prod->id)->first();

                $item = $solicitud->items()->create([
                    'producto_id' => $prod->id,
                    'producto_variante_id' => $variante?->id,
                    'cantidad_solicitada' => $cantidad,
                    'cantidad_aprobada' => $cantidad,
                    'unidad_medida_id' => $prod->unidad_medida_id ?? $unidadMed?->id,
                ]);

                $itemsSolicitud[] = ['item' => $item, 'cantidad' => $cantidad, 'variante_id' => $variante?->id];
            }

            // ============================================================
            // PASO 2: COTIZACIÓN (única, ganadora)
            // ============================================================
            $subtotal = 0.0;
            $cotizacion = Cotizacion::create([
                'solicitud_id' => $solicitud->id,
                'proveedor_id' => $proveedor->id,
                'fecha_cotizacion' => now()->subDays(25),
                'dias_entrega' => 5,
                'condicion_pago_id' => $condPago?->id,
                'observaciones' => 'Cotización única. Proveedor seleccionado por precio y disponibilidad.',
                'creada_por' => $admin->id,
                'moneda_id' => $moneda->id,
                'es_elegida' => true,
                'elegida_por' => $admin->id,
                'elegida_en' => now()->subDays(24),
                'subtotal' => 0,
                'total' => 0,
            ]);

            $preciosPorProducto = [450.00, 380.00, 220.00];

            foreach ($solicitud->items as $i => $solItem) {
                $precio = $preciosPorProducto[$i] ?? 300.00;
                $subLinea = $solItem->cantidad_aprobada * $precio;
                $subtotal += $subLinea;

                $cotizacion->items()->create([
                    'producto_id' => $solItem->producto_id,
                    'producto_variante_id' => $solItem->producto_variante_id,
                    'cantidad' => $solItem->cantidad_aprobada,
                    'precio_unitario' => $precio,
                    'subtotal' => $subLinea,
                    'es_elegido' => true,
                ]);
            }

            $total = $subtotal * 1.15;
            $cotizacion->update(['subtotal' => $subtotal, 'total' => $total]);

            // ============================================================
            // PASO 3: ORDEN DE COMPRA
            // ============================================================
            $orden = OrdenCompra::create([
                'codigo' => 'OC-ACT-'.now()->format('Y').'-001',
                'proveedor_id' => $proveedor->id,
                'solicitud_id' => $solicitud->id,
                'cotizacion_id' => $cotizacion->id,
                'fecha_orden' => now()->subDays(20),
                'condicion_pago_id' => $condPago?->id,
                'estado' => EstadoOrdenCompra::Recibida,
                'subtotal' => $subtotal,
                'total' => $total,
            ]);

            foreach ($cotizacion->items as $cotItem) {
                $orden->items()->create([
                    'producto_id' => $cotItem->producto_id,
                    'producto_variante_id' => $cotItem->producto_variante_id,
                    'cantidad' => $cotItem->cantidad,
                    'precio_unitario' => $cotItem->precio_unitario,
                    'subtotal' => $cotItem->subtotal,
                    'unidad_medida_id' => $unidadMed?->id,
                ]);
            }

            // ============================================================
            // PASO 4: RECEPCIÓN DE COMPRA
            // ============================================================
            $recepcion = RecepcionCompra::create([
                'codigo' => 'RC-ACT-'.now()->format('Y').'-001',
                'orden_compra_id' => $orden->id,
                'fecha_recepcion' => now()->subDays(10),
                'recibido_por_id' => $admin->id,
                'estado' => EstadoRecepcion::Completa,
                'notas' => 'Recepción de activos fijos en perfectas condiciones. Sin daños ni faltantes.',
            ]);

            $loteProveedor = 'ACT-LOT-'.Str::upper(Str::random(6));

            $recepcionItemsCreados = [];
            foreach ($orden->items as $ordenItem) {
                $recepcionItemsCreados[] = $recepcion->items()->create([
                    'orden_item_id' => $ordenItem->id,
                    'producto_id' => $ordenItem->producto_id,
                    'producto_variante_id' => $ordenItem->producto_variante_id,
                    'cantidad_recibida' => $ordenItem->cantidad,
                    'cantidad_rechazada' => 0.0,
                    'lote_proveedor' => $loteProveedor,
                    'fecha_vencimiento' => null, // Activos no tienen fecha de vencimiento
                ]);
            }

            // ============================================================
            // PASO 5: DISPARAR UseCase RegistrarEntradaRecepcion
            // (detecta tipo=3 y genera RegistroIndividualizacion por cada ítem)
            // ============================================================
            $itemsParaUseCase = collect($recepcionItemsCreados)->map(fn ($i) => [
                'id' => $i->id,
                'producto_id' => $i->producto_id,
                'producto_variante_id' => $i->producto_variante_id,
                'cantidad_recibida' => (float) $i->cantidad_recibida,
                'cantidad_rechazada' => (float) $i->cantidad_rechazada,
                'lote_proveedor' => $i->lote_proveedor,
                'fecha_vencimiento' => null,
            ])->all();

            app(RegistrarEntradaRecepcion::class)->execute(
                nuevoEstado: 'Completa',
                items: $itemsParaUseCase,
                proveedorId: $proveedor->id,
                creadoPorId: $admin->id,
            );

            // ============================================================
            // PASO 6: INDIVIDUALIZAR ACTIVOS
            // (uno por uno para generar códigos de inventario TV-YYYY-0001, AC-YYYY-0001, etc.)
            // ============================================================
            $registros = RegistroIndividualizacion::where('estado', '!=', EstadoIndividualizacion::Completado)
                ->orderBy('id')
                ->get();

            $nombresDesc = [
                ['TV Suite 101', 'TV Suite 102', 'TV Sala de Estar Lobby'],
                ['AC Habitación 201', 'AC Habitación 202'],
                ['Cama King Suite Principal'],
            ];

            foreach ($registros as $i => $registro) {
                $nombresDelRegistro = $nombresDesc[$i] ?? [];
                $unidades = [];

                for ($u = 0; $u < $registro->cantidad_total; $u++) {
                    $unidades[] = [
                        'numero_serie' => 'SN-'.Str::upper(Str::random(8)),
                        'nombre_descriptivo' => $nombresDelRegistro[$u] ?? (($registro->producto ? $registro->producto->nombre : '').' #'.($u + 1)),
                        'notas' => 'Unidad recibida en buen estado. Sin rasguños ni daños.',
                    ];
                }

                app(IndividualizarActivos::class)->execute(
                    registroId: $registro->id,
                    items: $unidades,
                    userId: $admin->id,
                );
            }

            // ============================================================
            // PASO 7: ASIGNAR ACTIVOS A HABITACIONES
            // ============================================================
            $activos = Activo::latest()->take(6)->get();
            $habitaciones = Habitacion::take(4)->get();

            foreach ($activos as $index => $activo) {
                // Cerrar asignación previa de bodega
                ActivoAsignacion::where('activo_id', $activo->id)
                    ->whereNull('fecha_fin')
                    ->update([
                        'fecha_fin' => now()->subDays(5)->toDateString(),
                        'estado' => EstadoAsignacion::Cerrada,
                    ]);

                $habitacion = $habitaciones->get($index % $habitaciones->count());

                if ($habitacion) {
                    ActivoAsignacion::create([
                        'activo_id' => $activo->id,
                        'asignable_type' => Habitacion::class,
                        'asignable_id' => $habitacion->id,
                        'fecha_inicio' => now()->subDays(5)->toDateString(),
                        'motivo' => "Instalación inicial en {$habitacion->nombre}",
                        'asignado_por_id' => $admin->id,
                        'estado' => EstadoAsignacion::Vigente,
                    ]);
                }
            }

            // ============================================================
            // PASO 8: REGISTRAR UN MANTENIMIENTO DE MUESTRA
            // (TV Suite 101 con daño en pantalla - en proceso)
            // ============================================================
            $activoConManto = $activos->first();

            if ($activoConManto) {
                // Crear un plan de mantenimiento
                $plan = ActPlanMantenimiento::create([
                    'nombre' => 'Reparación Correctiva Externa',
                    'tipo' => TipoMantenimiento::Correctivo,
                    'proveedor_id' => $proveedor->id,
                    'fecha_inicio' => now()->subDays(2)->toDateString(),
                    'costo_estimado' => 85.00,
                    'moneda_id' => $moneda->id,
                    'descripcion' => 'Pantalla con líneas horizontales. Posible golpe en transporte.',
                ]);

                ActivoMantenimiento::create([
                    'plan_id' => $plan->id,
                    'activo_id' => $activoConManto->id,
                    'fecha_programada' => now()->subDays(2)->toDateString(),
                    'fecha_realizada' => null,
                    'costo_real' => null,
                    'realizado_por_id' => $admin->id,
                    'estado' => EstadoMantenimiento::EnProceso,
                    'notas' => 'En espera de repuesto de panel LCD. Tiempo estimado: 7 días hábiles.',
                ]);
            }

            $this->command->info('✅ ActivosFlowSeeder completado:');
            $this->command->info('   • 1 Solicitud → 1 Cotización → 1 Orden de Compra → 1 Recepción');
            $this->command->info("   • {$activos->count()} activos individualizados con códigos de inventario");
            $this->command->info('   • Activos asignados a habitaciones');
            $this->command->info('   • 1 mantenimiento correctivo registrado');
        });
    }
}
