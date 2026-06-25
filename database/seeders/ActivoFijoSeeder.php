<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\TipoMantenimiento;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Colaboradores\Colaborador;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Monedas\Moneda;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivoFijoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hotel.com')->first() ?? User::first();
        if (! $admin) {
            $this->command->warn('No se encontró usuario admin.');

            return;
        }

        Auth::login($admin);

        $proveedores = Proveedor::limit(3)->get();
        $monedaUSD = Moneda::where('codigo', 'USD')->first() ?? Moneda::where('es_predeterminada', true)->first() ?? Moneda::first();
        $condPago = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'CONDICION_PAGO'))->first();
        $unidadMed = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'UNIDAD_MEDIDA'))->first();
        $colaborador = Colaborador::first();

        if ($proveedores->count() < 2 || ! $colaborador || ! $monedaUSD || ! $condPago) {
            $this->command->warn('Faltan dependencias minimas.');

            return;
        }

        $cats = Catalogo::pluck('id', 'codigo');
        $catMob = $cats['CAT_PRO_inv_MOB'];
        $catElectro = $cats['CAT_PRO_inv_ELECTRO'];
        $uniUd = $cats['UNI_UD'];

        // ================================================================
        // 1. CREAR PRODUCTOS
        // ================================================================

        $this->command->info('Creando productos...');

        $productosDef = [
            ['cat' => $catMob, 'nombre' => 'Cama Queen Size', 'desc' => 'Base tapizada Queen', 'variants' => [
                ['codigo' => 'CAM-QUEEN-BCO', 'nombre' => 'Cama Queen blanca 160x200', 'attr' => ['color' => 'blanco', 'tamaño' => '160x200']],
                ['codigo' => 'CAM-QUEEN-NOGAL', 'nombre' => 'Cama Queen nogal 160x200', 'attr' => ['color' => 'nogal', 'tamaño' => '160x200']],
                ['codigo' => 'CAM-QUEEN-GRIS', 'nombre' => 'Cama Queen gris capitoné', 'attr' => ['color' => 'gris', 'tamaño' => '160x200']],
            ]],
            ['cat' => $catMob, 'nombre' => 'Mesa de Centro', 'desc' => 'Mesa decorativa lobby', 'variants' => [
                ['codigo' => 'MESA-CTRO-VIDRIO', 'nombre' => 'Mesa centro vidrio templado', 'attr' => ['material' => 'vidrio templado']],
                ['codigo' => 'MESA-CTRO-MADERA', 'nombre' => 'Mesa centro madera maciza', 'attr' => ['material' => 'madera maciza']],
                ['codigo' => 'MESA-CTRO-MARMOL', 'nombre' => 'Mesa centro mármol', 'attr' => ['material' => 'mármol']],
            ]],
            ['cat' => $catElectro, 'nombre' => 'Televisor 55"', 'desc' => 'Smart TV 4K UHD 55"', 'variants' => [
                ['codigo' => 'TV55-SAMSUNG-24', 'nombre' => 'Samsung 55" 4K 2024', 'attr' => ['marca' => 'Samsung', 'tamaño' => '55"', 'año' => 2024]],
                ['codigo' => 'TV55-LG-24', 'nombre' => 'LG 55" 4K 2024', 'attr' => ['marca' => 'LG', 'tamaño' => '55"', 'año' => 2024]],
                ['codigo' => 'TV55-SONY-24', 'nombre' => 'Sony 55" 4K 2024', 'attr' => ['marca' => 'Sony', 'tamaño' => '55"', 'año' => 2024]],
            ]],
            ['cat' => $catElectro, 'nombre' => 'Aire Acondicionado Split', 'desc' => 'Split Inverter 18000 BTU', 'variants' => [
                ['codigo' => 'AC-SPLIT-18K', 'nombre' => 'Split 18000 BTU frío/calor', 'attr' => ['capacidad' => '18000 BTU', 'tipo' => 'Split Inverter']],
                ['codigo' => 'AC-SPLIT-24K', 'nombre' => 'Split 24000 BTU frío/calor', 'attr' => ['capacidad' => '24000 BTU', 'tipo' => 'Split Inverter']],
                ['codigo' => 'AC-SPLIT-12K', 'nombre' => 'Split 12000 BTU frío/calor', 'attr' => ['capacidad' => '12000 BTU', 'tipo' => 'Split Inverter']],
            ]],
            ['cat' => $catMob, 'nombre' => 'Sillón Lounge', 'desc' => 'Sillón ergonómico', 'variants' => [
                ['codigo' => 'SILLOUNGE-TELA-GRIS', 'nombre' => 'Sillón lounge tela gris', 'attr' => ['material' => 'tela', 'color' => 'gris']],
                ['codigo' => 'SILLOUNGE-CUERO-MARR', 'nombre' => 'Sillón lounge cuero marrón', 'attr' => ['material' => 'cuero', 'color' => 'marrón']],
                ['codigo' => 'SILLOUNGE-CUERO-NEG', 'nombre' => 'Sillón lounge cuero negro', 'attr' => ['material' => 'cuero', 'color' => 'negro']],
            ]],
            ['cat' => $catMob, 'nombre' => 'Escritorio Ejecutivo', 'desc' => 'Escritorio 140x70cm', 'variants' => [
                ['codigo' => 'ESC-EJE-NOGAL', 'nombre' => 'Escritorio ejecutivo nogal', 'attr' => ['color' => 'nogal', 'tamaño' => '140x70cm']],
                ['codigo' => 'ESC-EJE-BLANCO', 'nombre' => 'Escritorio ejecutivo blanco', 'attr' => ['color' => 'blanco', 'tamaño' => '140x70cm']],
                ['codigo' => 'ESC-EJE-WENGUE', 'nombre' => 'Escritorio ejecutivo wengué', 'attr' => ['color' => 'wengué', 'tamaño' => '140x70cm']],
            ]],
            ['cat' => $catElectro, 'nombre' => 'UPS No-Break', 'desc' => 'Respaldo energía', 'variants' => [
                ['codigo' => 'UPS-1000VA', 'nombre' => 'UPS 1000VA 600W', 'attr' => ['capacidad' => '1000VA']],
                ['codigo' => 'UPS-2000VA', 'nombre' => 'UPS 2000VA 1200W', 'attr' => ['capacidad' => '2000VA']],
                ['codigo' => 'UPS-3000VA', 'nombre' => 'UPS 3000VA 1800W', 'attr' => ['capacidad' => '3000VA']],
            ]],
            ['cat' => $catMob, 'nombre' => 'Sombrilla de Jardín', 'desc' => 'Sombrilla exterior', 'variants' => [
                ['codigo' => 'SOMB-2M-ROJA', 'nombre' => 'Sombrilla 2m roja', 'attr' => ['diámetro' => '2m', 'color' => 'roja']],
                ['codigo' => 'SOMB-2M5-BEI', 'nombre' => 'Sombrilla 2.5m beige', 'attr' => ['diámetro' => '2.5m', 'color' => 'beige']],
                ['codigo' => 'SOMB-3M-GRIS', 'nombre' => 'Sombrilla 3m gris', 'attr' => ['diámetro' => '3m', 'color' => 'gris']],
            ]],
        ];

        $prodIds = [];
        foreach ($productosDef as $pd) {
            $existingProduct = DB::table('productos')->where('nombre', $pd['nombre'])->first();
            if ($existingProduct) {
                $pid = $existingProduct->id;
            } else {
                $pid = DB::table('productos')->insertGetId([
                    'categoria_id' => $pd['cat'], 'marca_id' => null, 'nombre' => $pd['nombre'],
                    'descripcion' => $pd['desc'], 'unidad_medida_id' => $uniUd, 'tipo' => 3,
                    'estado' => 1, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $variantePrincipal = null;
            foreach ($pd['variants'] as $v) {
                $existingVariant = DB::table('producto_variantes')->where('codigo', $v['codigo'])->first();
                if ($existingVariant) {
                    $vid = $existingVariant->id;
                } else {
                    $vid = DB::table('producto_variantes')->insertGetId([
                        'producto_id' => $pid, 'codigo' => $v['codigo'],
                        'nombre_variante' => $v['nombre'],
                        'atributos' => json_encode($v['attr']),
                        'unidad_medida_id' => $uniUd,
                        'estado' => 1, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                if ($variantePrincipal === null) {
                    $variantePrincipal = $vid;
                }
            }
            $prodIds[$pd['nombre']] = ['pid' => $pid, 'vid' => $variantePrincipal];
        }

        $this->command->info('✓ '.count($productosDef).' productos tipo=3 creados.');

        // ================================================================
        // 2. FLUJO DE COMPRA
        // ================================================================

        $areas = ['Torre N P1', 'Torre N P2', 'Torre Sur', 'Villas', 'Bungalows'];
        $nums = [101, 102, 103, 104, 105, 106, 201, 202, 203, 204];

        $flujos = [
            [
                'cod' => 'AF-001', 'prov' => $proveedores->get(0), 'dias' => 30, 'label' => 'Habitaciones',
                'items' => [
                    ['nombre' => 'Cama Queen Size', 'cant' => 15, 'precio' => 350.00],
                    ['nombre' => 'Escritorio Ejecutivo', 'cant' => 10, 'precio' => 220.00],
                    ['nombre' => 'Televisor 55"', 'cant' => 10, 'precio' => 480.00],
                    ['nombre' => 'Aire Acondicionado Split', 'cant' => 10, 'precio' => 520.00],
                ],
            ],
            [
                'cod' => 'AF-002', 'prov' => $proveedores->get(1), 'dias' => 25, 'label' => 'Áreas Públicas',
                'items' => [
                    ['nombre' => 'Mesa de Centro', 'cant' => 5, 'precio' => 250.00],
                    ['nombre' => 'Sillón Lounge', 'cant' => 8, 'precio' => 380.00],
                    ['nombre' => 'Sombrilla de Jardín', 'cant' => 4, 'precio' => 90.00],
                ],
            ],
            [
                'cod' => 'AF-003', 'prov' => $proveedores->get(2), 'dias' => 20, 'label' => 'Sistemas',
                'items' => [
                    ['nombre' => 'UPS No-Break', 'cant' => 5, 'precio' => 280.00],
                    ['nombre' => 'Televisor 55"', 'cant' => 3, 'precio' => 480.00],
                ],
            ],
        ];

        $totalActivos = 0;

        foreach ($flujos as $f) {
            $this->command->info("--- Flujo: {$f['label']} ---");

            try {
                // Solicitud
                $sol = Solicitud::create([
                    'codigo' => "SOL-{$f['cod']}",
                    'colaborador_id' => $colaborador->id,
                    'departamento_solicitante_id' => 1,
                    'fecha_solicitud' => now()->subDays($f['dias']),
                    'estado' => EstadoSolicitud::Aprobada,
                    'motivo' => "Equipamiento {$f['label']}",
                ]);
                foreach ($f['items'] as $it) {
                    if (! array_key_exists($it['nombre'], $prodIds)) {
                        $this->command->warn("  Prod no encontrado: {$it['nombre']}");

                        continue;
                    }
                    $sol->items()->create([
                        'producto_id' => $prodIds[$it['nombre']]['pid'],
                        'producto_variante_id' => $prodIds[$it['nombre']]['vid'],
                        'cantidad_solicitada' => $it['cant'], 'cantidad_aprobada' => $it['cant'],
                        'unidad_medida_id' => $uniUd,
                    ]);
                }

                if ($sol->items()->count() === 0) {
                    $this->command->warn('  Sin items, saltando.');

                    continue;
                }

                // Cotización
                $sub = 0;
                $cot = Cotizacion::create([
                    'solicitud_id' => $sol->id, 'proveedor_id' => $f['prov']?->id,
                    'fecha_cotizacion' => now()->subDays($f['dias'] - 3), 'dias_entrega' => rand(5, 10),
                    'condicion_pago_id' => $condPago->id, 'creada_por' => $admin->id,
                    'moneda_id' => $monedaUSD->id, 'es_elegida' => true,
                    'elegida_por' => $admin->id, 'elegida_en' => now()->subDays($f['dias'] - 4),
                    'subtotal' => 0, 'total' => 0,
                ]);
                foreach ($sol->items as $si) {
                    $itemDef = collect($f['items'])->firstWhere('nombre', $si->producto ? $si->producto->nombre : '');
                    $p = $itemDef['precio'] ?? 100;
                    $sl = $si->cantidad_aprobada * $p;
                    $sub += $sl;
                    $cot->items()->create([
                        'producto_id' => $si->producto_id, 'producto_variante_id' => $si->producto_variante_id,
                        'cantidad' => $si->cantidad_aprobada, 'precio_unitario' => $p,
                        'subtotal' => $sl, 'es_elegido' => true,
                    ]);
                }
                $cot->update(['subtotal' => $sub, 'total' => $sub * 1.15]);

                // OC
                $oc = OrdenCompra::create([
                    'codigo' => "OC-{$f['cod']}", 'proveedor_id' => $f['prov']?->id,
                    'solicitud_id' => $sol->id, 'cotizacion_id' => $cot->id,
                    'fecha_orden' => now()->subDays($f['dias'] - 5),
                    'condicion_pago_id' => $condPago->id,
                    'estado' => EstadoOrdenCompra::Recibida,
                    'subtotal' => $sub, 'total' => $sub * 1.15,
                ]);
                foreach ($cot->items as $ci) {
                    $oc->items()->create([
                        'producto_id' => $ci->producto_id, 'producto_variante_id' => $ci->producto_variante_id,
                        'cantidad' => $ci->cantidad, 'precio_unitario' => $ci->precio_unitario,
                        'subtotal' => $ci->subtotal, 'unidad_medida_id' => $uniUd,
                    ]);
                }

                // Recepción
                $rc = RecepcionCompra::create([
                    'codigo' => "RC-{$f['cod']}", 'orden_compra_id' => $oc->id,
                    'fecha_recepcion' => now()->subDays($f['dias'] - 8),
                    'recibido_por_id' => $admin->id,
                    'estado' => EstadoRecepcion::Completa,
                    'notas' => "Recepción {$f['label']}",
                ]);

                // ================================================================
                // CREAR ACTIVOS DIRECTAMENTE EN inv_activos
                // ================================================================

                $contador = 0;
                $areaIdx = 0;

                foreach ($oc->items as $oi) {
                    $rcItem = $rc->items()->create([
                        'orden_item_id' => $oi->id,
                        'producto_id' => $oi->producto_id, 'producto_variante_id' => $oi->producto_variante_id,
                        'cantidad_recibida' => $oi->cantidad, 'cantidad_rechazada' => 0,
                        'lote_proveedor' => 'LOT-'.Str::upper(Str::random(6)),
                        'fecha_vencimiento' => null,
                    ]);

                    $prod = Producto::find($oi->producto_id);
                    if (! $prod) {
                        $this->command->warn("  Prod ID {$oi->producto_id} no encontrado");

                        continue;
                    }

                    for ($u = 0; $u < (int) $oi->cantidad; $u++) {
                        $area = $areas[$areaIdx % count($areas)];
                        $num = $nums[$u % count($nums)];
                        $areaIdx++;

                        $codigo = sprintf('AF-%s-%04d', now()->format('Y'), $totalActivos + 1);

                        $this->command->info("  Creando {$codigo}...");

                        try {
                            $activo = Activo::create([
                                'codigo_inventario' => $codigo,
                                'producto_id' => $oi->producto_id,
                                'producto_variante_id' => $oi->producto_variante_id,
                                'nombre_descriptivo' => "{$prod->nombre} - {$area} #{$num}",
                                'numero_serie' => 'SN-'.Str::upper(Str::random(8)),
                                'fecha_adquisicion' => now()->subDays(rand(1, 20))->toDateString(),
                                'costo_adquisicion' => (float) $oi->precio_unitario,
                                'moneda_id' => $monedaUSD->id,
                                'proveedor_id' => $f['prov']?->id,
                                'recepcion_item_id' => $rcItem->id,
                                'vida_util_meses' => 60,
                                'estado' => EstadoActivo::Activo,
                                'notas' => "Adquirido via OC-{$f['cod']}",
                            ]);
                            $contador++;
                            $totalActivos++;
                            $this->command->info("    ✓ {$codigo}");
                        } catch (\Throwable $e) {
                            $this->command->error("    ✗ Error creando {$codigo}: ".$e->getMessage());
                        }
                    }
                }

                $this->command->info("  ✓ {$contador} activos en {$f['label']}");

            } catch (\Throwable $e) {
                $this->command->error("  ✗ Flujo {$f['label']}: ".$e->getMessage());
                $this->command->error('  Line: '.$e->getLine().' File: '.$e->getFile());
            }
        }

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info(' RESUMEN');
        $this->command->info('========================================');

        try {
            $dbCount = DB::table('inv_activos')->count();
            $eloquentCount = Activo::count();
            $this->command->info("  DB::table('inv_activos')->count() = {$dbCount}");
            $this->command->info("  Activo::count() = {$eloquentCount}");
        } catch (\Throwable $e) {
            $this->command->error('  Error contando activos: '.$e->getMessage());
        }

        $this->command->info("  Total procesados: {$totalActivos}");
        $this->command->info('========================================');

        // ================================================================
        // 3. ASIGNAR A HABITACIONES / ESPACIOS
        // ================================================================

        try {
            $todos = Activo::orderBy('id')->get();
            $habitaciones = Habitacion::take(5)->get();
            $espacios = Espacio::take(3)->get();

            if ($habitaciones->isNotEmpty()) {
                foreach ($todos->take(20) as $i => $a) {
                    $hab = $habitaciones->get($i % $habitaciones->count());
                    if (! $hab) {
                        continue;
                    }
                    ActivoAsignacion::create([
                        'activo_id' => $a->id, 'asignable_type' => Habitacion::class,
                        'asignable_id' => $hab->id, 'fecha_inicio' => now()->subDays(2)->toDateString(),
                        'motivo' => "Asignado a {$hab->nombre}", 'asignado_por_id' => $admin->id,
                        'estado' => EstadoAsignacion::Vigente,
                    ]);
                }
                $this->command->info('✓ Asignados a habitaciones.');
            }

            if ($espacios->isNotEmpty()) {
                foreach ($todos->slice(20, 10) as $i => $a) {
                    $esp = $espacios->get($i % $espacios->count());
                    if (! $esp) {
                        continue;
                    }
                    ActivoAsignacion::create([
                        'activo_id' => $a->id, 'asignable_type' => Espacio::class,
                        'asignable_id' => $esp->id, 'fecha_inicio' => now()->subDays(1)->toDateString(),
                        'motivo' => "Asignado a {$esp->nombre}", 'asignado_por_id' => $admin->id,
                        'estado' => EstadoAsignacion::Vigente,
                    ]);
                }
                $this->command->info('✓ Asignados a espacios.');
            }
        } catch (\Throwable $e) {
            $this->command->error('Error en asignaciones: '.$e->getMessage());
        }

        // ================================================================
        // 4. MANTENIMIENTOS
        // ================================================================

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
            $this->command->info('✓ Mantenimientos registrados.');
        } catch (\Throwable $e) {
            $this->command->error('Error en mantenimientos: '.$e->getMessage());
        }
    }
}
